<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Services\CoSoServiceInterface;
use App\Models\CoSo;
use App\Models\KhuNha;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\ThongKeSnapshotService;

class CoSoService
{
    /**
     * @var CoSoRepositoryInterface
     */
    protected $coSoRepository;

    private const ACTIVE_CO_SO_CACHE_KEY = 'active.co_so';
    private const CACHE_TIME = 300;

    /**
     * CoSoService constructor.
     *
     * @param CoSoRepositoryInterface $coSoRepository
     */
    public function __construct(CoSoRepositoryInterface $coSoRepository)
    {
        $this->coSoRepository = $coSoRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->coSoRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveCoSos(): Collection
    {
        return Cache::remember(
            self::ACTIVE_CO_SO_CACHE_KEY,
            self::CACHE_TIME,
            function() {
                return $this->coSoRepository->getActive(['id', 'ten_co_so']);
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): CoSo
    {
        $coSo = $this->coSoRepository->find($id);
        if (!$coSo) {
            throw new \App\Exceptions\ResourceNotFoundException('Không tìm thấy cơ sở với ID: ' . $id);
        }
        return $coSo;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): CoSo
    {
        // Tự động tính diện tích quy đổi = diện tích đất * vị trí khuôn viên
        $data['dien_tich_quy_doi'] = $this->calculateDienTichQuyDoi(
            $data['dien_tich_dat'],
            $data['vi_tri_khuon_vien']
        );
        $result = $this->coSoRepository->create($data);
        app(ThongKeSnapshotService::class)->onEntityChanged('co_so');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): CoSo
    {
        $this->getById($id);
        // Tự động tính diện tích quy đổi = diện tích đất * vị trí khuôn viên
        $data['dien_tich_quy_doi'] = $this->calculateDienTichQuyDoi(
            $data['dien_tich_dat'],
            $data['vi_tri_khuon_vien']
        );
        $result = $this->coSoRepository->update($id, $data);
        app(ThongKeSnapshotService::class)->onEntityChanged('co_so');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $this->getById($id);
        $result = $this->coSoRepository->delete($id);
        app(ThongKeSnapshotService::class)->onEntityChanged('co_so');
        return $result;
    }

    /**
     * Tạo phiên bản mới: lưu trữ bản ghi hiện tại vào lịch sử,
     * tạo bản ghi mới với dữ liệu đã cập nhật.
     *
     * Cascade: cập nhật tất cả KhuNha hien_hanh trỏ từ old_id sang new_id.
     */
    public function createNewVersion(int $id, array $data): CoSo
    {
        return DB::transaction(function () use ($id, $data) {
            $current = $this->getById($id);

            // Xác định ban_ghi_goc_id và phien_ban mới
            $gocId = $current->ban_ghi_goc_id ?? $current->id;
            $phienBanMoi = ($current->phien_ban ?? 1) + 1;
            $now = now();

            // Đánh dấu bản ghi hiện tại là lịch sử
            $current->update([
                'trang_thai_du_lieu' => 'lich_su',
                'hieu_luc_den' => $now,
            ]);

            // Tính diện tích quy đổi
            $data['dien_tich_quy_doi'] = $this->calculateDienTichQuyDoi(
                $data['dien_tich_dat'],
                $data['vi_tri_khuon_vien']
            );

            // Tạo bản ghi phiên bản mới
            $newRecord = $this->coSoRepository->create(array_merge($data, [
                'ma_co_so'          => $current->ma_co_so,
                'trang_thai_du_lieu' => 'hien_hanh',
                'hieu_luc_tu'       => $now,
                'hieu_luc_den'      => null,
                'phien_ban'         => $phienBanMoi,
                'ban_ghi_goc_id'    => $gocId,
            ]));

            // Cascade: chuyển các KhuNha hien_hanh sang co_so_id mới
            KhuNha::where('co_so_id', $current->id)
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->update(['co_so_id' => $newRecord->id]);

            // Hook sau khi transaction commit thành công
            app(ThongKeSnapshotService::class)->onEntityChanged('co_so');

            return $newRecord;
        });
    }

    /**
     * Tính diện tích quy đổi theo công thức BGD
     * Diện tích quy đổi = Diện tích đất × Vị trí khuôn viên
     * 
     * @param float $dienTichDat Diện tích đất (m²)
     * @param float $viTriKhuonVien Hệ số vị trí khuôn viên (mặc định 2.5 theo BGD)
     * @return float Diện tích quy đổi
     */
    public function calculateDienTichQuyDoi(float $dienTichDat, float $viTriKhuonVien): float
    {
        return $dienTichDat * $viTriKhuonVien;
    }
}

