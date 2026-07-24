<?php

namespace App\Services;

use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Services\PhongServiceInterface;
use App\Models\Phong;
use App\Models\ThietBi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\ThongKeSnapshotService;

class PhongService
{
    /**
     * @var PhongRepositoryInterface
     */
    protected $phongRepository;

    /**
     * PhongService constructor.
     *
     * @param PhongRepositoryInterface $phongRepository
     */
    public function __construct(PhongRepositoryInterface $phongRepository)
    {
        $this->phongRepository = $phongRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->phongRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivePhongs(): Collection
    {
        return $this->phongRepository->getActive(['id', 'ten_phong', 'khu_nha_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): Phong
    {
        $phong = $this->phongRepository->find($id);
        
        if (!$phong) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Không tìm thấy phòng');
        }

        // Load relationships
        $phong->load('khuNha.coSo');

        return $phong;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Phong
    {
        $result = $this->phongRepository->create($data);
        app(ThongKeSnapshotService::class)->onEntityChanged('phong');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): Phong
    {
        $result = $this->phongRepository->update($id, $data);
        app(ThongKeSnapshotService::class)->onEntityChanged('phong');
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $result = $this->phongRepository->delete($id);
        app(ThongKeSnapshotService::class)->onEntityChanged('phong');
        return $result;
    }

    /**
     * Tạo phiên bản mới: lưu trữ bản ghi hiện tại vào lịch sử,
     * tạo bản ghi mới với dữ liệu đã cập nhật.
     *
     * Cascade: cập nhật tất cả ThietBi hien_hanh trỏ từ old_id sang new_id.
     */
    public function createNewVersion(int $id, array $data): Phong
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

            $newRecord = $this->phongRepository->create(array_merge($data, [
                'ma_phong'           => $current->ma_phong,
                'trang_thai_du_lieu' => 'hien_hanh',
                'hieu_luc_tu'        => $now,
                'hieu_luc_den'       => null,
                'phien_ban'          => $phienBanMoi,
                'ban_ghi_goc_id'     => $gocId,
            ]));

            // Cascade: chuyển các ThietBi hien_hanh sang phong_id mới
            ThietBi::where('phong_id', $current->id)
                ->where('trang_thai_du_lieu', 'hien_hanh')
                ->update(['phong_id' => $newRecord->id]);

            app(ThongKeSnapshotService::class)->onEntityChanged('phong');

            return $newRecord;
        });
    }
}

