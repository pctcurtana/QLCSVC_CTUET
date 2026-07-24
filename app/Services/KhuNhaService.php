<?php

namespace App\Services;

use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Services\KhuNhaServiceInterface;
use App\Models\KhuNha;
use App\Models\Phong;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\ThongKeSnapshotService;

class KhuNhaService
{
    /**
     * @var KhuNhaRepositoryInterface
     */
    protected $khuNhaRepository;

    /**
     * KhuNhaService constructor.
     *
     * @param KhuNhaRepositoryInterface $khuNhaRepository
     */
    public function __construct(KhuNhaRepositoryInterface $khuNhaRepository)
    {
        $this->khuNhaRepository = $khuNhaRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->khuNhaRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveKhuNhas(): Collection
    {
        return $this->khuNhaRepository->getActive(['id', 'ten_khu_nha', 'co_so_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): KhuNha
    {
        $khuNha = $this->khuNhaRepository->find($id);
        
        if (!$khuNha) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Không tìm thấy khu nhà');
        }

        // Load relationship
        $khuNha->load('coSo');

        return $khuNha;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): KhuNha
    {
        // Tự động tính các diện tích
        $data = $this->calculateDienTich($data);
        $result = $this->khuNhaRepository->create($data);
        app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): KhuNha
    {
        // Tự động tính các diện tích
        $data = $this->calculateDienTich($data);
        $result = $this->khuNhaRepository->update($id, $data);
        app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $result = $this->khuNhaRepository->delete($id);
        app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');
        return $result;
    }

    /**
     * Tạo phiên bản mới: lưu trữ bản ghi hiện tại vào lịch sử,
     * tạo bản ghi mới với dữ liệu đã cập nhật.
     *
     * Cascade: cập nhật tất cả Phong hien_hanh trỏ từ old_id sang new_id.
     */
    public function createNewVersion(int $id, array $data): KhuNha
    {
        return DB::transaction(function () use ($id, $data) {
            $current = $this->getById($id);

            $gocId = $current->ban_ghi_goc_id ?? $current->id;
            $phienBanMoi = ($current->phien_ban ?? 1) + 1;
            $now = now();

            $current->update([
                'trang_thai_du_lieu' => 'lich_su',
                'hieu_luc_den' => $now,
            ]);

            // Tự động tính các diện tích
            $data = $this->calculateDienTich($data);

            $newRecord = $this->khuNhaRepository->create(array_merge($data, [
                'ma_khu_nha'         => $current->ma_khu_nha,
                'trang_thai_du_lieu' => 'hien_hanh',
                'hieu_luc_tu'        => $now,
                'hieu_luc_den'       => null,
                'phien_ban'          => $phienBanMoi,
                'ban_ghi_goc_id'     => $gocId,
            ]));

            // Cascade: chuyển các Phong hien_hanh sang khu_nha_id mới
            Phong::where('khu_nha_id', $current->id)
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->update(['khu_nha_id' => $newRecord->id]);

            app(ThongKeSnapshotService::class)->onEntityChanged('khu_nha');

            return $newRecord;
        });
    }

    /**
     * Tính toán tất cả diện tích tự động cho khu nhà.
     * 
     * Công thức:
     *   - Tổng DT sàn XD = Diện tích xây dựng × Số tầng
     *   - DT sàn đào tạo = Tổng DT sàn XD × Hệ số sử dụng cho đào tạo
     *
     * @param array $data Dữ liệu chứa dien_tich_xay_dung, so_tang, he_so_su_dung_dao_tao
     * @return array Dữ liệu đã bổ sung tong_dien_tich_san và dien_tich_san_dao_tao
     */
    public function calculateDienTich(array $data): array
    {
        $dienTichXayDung = (float) ($data['dien_tich_xay_dung'] ?? 0);
        $soTang = (int) ($data['so_tang'] ?? 1);
        $heSoSuDung = (float) ($data['he_so_su_dung_dao_tao'] ?? 0.7);

        $data['tong_dien_tich_san'] = $this->calculateTongDienTichSan($dienTichXayDung, $soTang);
        $data['dien_tich_san_dao_tao'] = $this->calculateDienTichSanDaoTao($data['tong_dien_tich_san'], $heSoSuDung);

        return $data;
    }

    /**
     * Tính tổng diện tích sàn xây dựng
     * Tổng DT sàn XD = Diện tích xây dựng × Số tầng
     * 
     * @param float $dienTichXayDung Diện tích xây dựng (m²)
     * @param int $soTang Số tầng
     * @return float Tổng diện tích sàn xây dựng
     */
    public function calculateTongDienTichSan(float $dienTichXayDung, int $soTang): float
    {
        return $dienTichXayDung * $soTang;
    }

    /**
     * Tính diện tích sàn sử dụng cho đào tạo
     * DT sàn đào tạo = Tổng DT sàn xây dựng × Hệ số sử dụng cho đào tạo
     * 
     * @param float $tongDienTichSan Tổng diện tích sàn xây dựng (m²)
     * @param float $heSoSuDung Hệ số DT sử dụng cho đào tạo (mặc định 0.7)
     * @return float Diện tích sàn sử dụng cho đào tạo
     */
    public function calculateDienTichSanDaoTao(float $tongDienTichSan, float $heSoSuDung): float
    {
        return $tongDienTichSan * $heSoSuDung;
    }
}
