<?php

namespace App\Services;

use App\Contracts\Repositories\LichSuBaoDuongRepositoryInterface;
use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Contracts\Services\LichSuBaoDuongServiceInterface;
use App\Models\LichSuBaoDuong;
use App\Models\ThietBi;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LichSuBaoDuongService
{
    /**
     * @var LichSuBaoDuongRepositoryInterface
     */
    protected $lichSuBaoDuongRepository;

    /**
     * @var ThietBiRepositoryInterface
     */
    protected $thietBiRepository;

    /**
     * LichSuBaoDuongService constructor.
     *
     * @param LichSuBaoDuongRepositoryInterface $lichSuBaoDuongRepository
     * @param ThietBiRepositoryInterface $thietBiRepository
     */
    public function __construct(
        LichSuBaoDuongRepositoryInterface $lichSuBaoDuongRepository,
        ThietBiRepositoryInterface $thietBiRepository
    ) {
        $this->lichSuBaoDuongRepository = $lichSuBaoDuongRepository;
        $this->thietBiRepository = $thietBiRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->lichSuBaoDuongRepository->paginate($filters, $perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): LichSuBaoDuong
    {
        $lichSu = $this->lichSuBaoDuongRepository->find($id);
        
        if (!$lichSu) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Không tìm thấy lịch sử bảo dưỡng');
        }

        // Load relationship
        $lichSu->load('thietBi');

        return $lichSu;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): LichSuBaoDuong
    {
        $lichSu = $this->lichSuBaoDuongRepository->create($data);

        // Cập nhật thông tin bảo dưỡng cho thiết bị nếu hoàn thành
        if ($data['trang_thai'] === 'hoan_thanh') {
            $this->updateThietBiMaintenanceInfo($data['thiet_bi_id'], $data['ngay_bao_duong']);
        }

        return $lichSu;
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): LichSuBaoDuong
    {
        $lichSu = $this->getById($id);
        $oldStatus = $lichSu->trang_thai;

        $updatedLichSu = $this->lichSuBaoDuongRepository->update($id, $data);

        // Cập nhật thông tin bảo dưỡng cho thiết bị nếu trạng thái thay đổi thành hoàn thành
        if ($data['trang_thai'] === 'hoan_thanh' && $oldStatus !== 'hoan_thanh') {
            $this->updateThietBiMaintenanceInfo($data['thiet_bi_id'], $data['ngay_bao_duong']);
        }

        return $updatedLichSu;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        return $this->lichSuBaoDuongRepository->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getByThietBi(int $thietBiId): Collection
    {
        return $this->lichSuBaoDuongRepository->getByThietBi($thietBiId);
    }

    /**
     * {@inheritDoc}
     */
    public function getByThietBiObject(ThietBi $thietBi): Collection
    {
        return $thietBi->lichSuBaoDuongs()
            ->latest('ngay_bao_duong')
            ->get();
    }

    /**
     * {@inheritDoc}
     * 
     * Cập nhật ngày bảo dưỡng cuối và tính ngày bảo dưỡng tiếp theo cho thiết bị
     */
    public function updateThietBiMaintenanceInfo(int $thietBiId, string $ngayBaoDuong): void
    {
        $thietBi = $this->thietBiRepository->find($thietBiId);

        if (!$thietBi) {
            return;
        }

        $updateData = [
            'ngay_bao_duong_cuoi' => $ngayBaoDuong,
        ];

        // Tính ngày bảo dưỡng tiếp theo nếu có chu kỳ
        if ($thietBi->chu_ky_bao_duong) {
            $updateData['ngay_bao_duong_tiep_theo'] = Carbon::parse($ngayBaoDuong)
                ->addMonths($thietBi->chu_ky_bao_duong)
                ->format('Y-m-d');
        }

        $this->thietBiRepository->update($thietBiId, $updateData);
    }
}

