<?php

namespace App\Contracts\Repositories;

use App\Models\LichSuBaoDuong;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LichSuBaoDuongRepositoryInterface
{
    /**
     * Lấy danh sách lịch sử bảo dưỡng có phân trang và filter
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy tất cả lịch sử bảo dưỡng
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Lấy lịch sử bảo dưỡng theo ID
     *
     * @param int $id
     * @return LichSuBaoDuong|null
     */
    public function find(int $id): ?LichSuBaoDuong;

    /**
     * Tạo lịch sử bảo dưỡng mới
     *
     * @param array $data
     * @return LichSuBaoDuong
     */
    public function create(array $data): LichSuBaoDuong;

    /**
     * Cập nhật lịch sử bảo dưỡng
     *
     * @param int $id
     * @param array $data
     * @return LichSuBaoDuong
     */
    public function update(int $id, array $data): LichSuBaoDuong;

    /**
     * Xóa lịch sử bảo dưỡng
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Lấy lịch sử bảo dưỡng theo thiết bị
     *
     * @param int $thietBiId
     * @return Collection
     */
    public function getByThietBi(int $thietBiId): Collection;

    /**
     * Lấy lịch sử bảo dưỡng mới nhất của thiết bị
     *
     * @param int $thietBiId
     * @return LichSuBaoDuong|null
     */
    public function getLatestByThietBi(int $thietBiId): ?LichSuBaoDuong;
}

