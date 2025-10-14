<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoSo;
use App\Models\KhuNha;
use App\Models\Phong;
use App\Models\ThietBi;

class QLCSVCSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo cơ sở
        $coSo1 = CoSo::create([
            'ma_co_so' => 'CS001',
            'ten_co_so' => 'Cơ sở chính - Khu 1',
            'dia_chi' => '256 Nguyễn Văn Cừ, An Hòa, Ninh Kiều, Cần Thơ',
            'tong_dien_tich' => 50000,
            'dien_tich_san_xay_dung' => 30000,
            'dien_tich_con_lai' => 20000,
            'mo_ta' => 'Cơ sở chính của trường, bao gồm các khu giảng dạy, hành chính',
            'trang_thai' => 'active',
        ]);

        $coSo2 = CoSo::create([
            'ma_co_so' => 'CS002',
            'ten_co_so' => 'Cơ sở 2 - Khu thực hành',
            'dia_chi' => '1 Lý Tự Trọng, An Phú, Ninh Kiều, Cần Thơ',
            'tong_dien_tich' => 30000,
            'dien_tich_san_xay_dung' => 18000,
            'dien_tich_con_lai' => 12000,
            'mo_ta' => 'Khu thực hành và thí nghiệm',
            'trang_thai' => 'active',
        ]);

        // Tạo khu nhà cho cơ sở 1
        $khuNha1 = KhuNha::create([
            'co_so_id' => $coSo1->id,
            'ma_khu_nha' => 'KN-A',
            'ten_khu_nha' => 'Nhà A - Giảng đường',
            'loai_khu_nha' => 'phong_hoc',
            'so_tang' => 5,
            'dien_tich_san_xay_dung' => 2000,
            'dien_tich_su_dung' => 8000,
            'nam_xay_dung' => 2015,
            'mo_ta' => 'Khu giảng đường chính',
            'trang_thai' => 'active',
        ]);

        $khuNha2 = KhuNha::create([
            'co_so_id' => $coSo1->id,
            'ma_khu_nha' => 'KN-B',
            'ten_khu_nha' => 'Nhà B - Hành chính',
            'loai_khu_nha' => 'phong_lam_viec',
            'so_tang' => 4,
            'dien_tich_san_xay_dung' => 1500,
            'dien_tich_su_dung' => 5000,
            'nam_xay_dung' => 2018,
            'mo_ta' => 'Khu hành chính và văn phòng',
            'trang_thai' => 'active',
        ]);

        $khuNha3 = KhuNha::create([
            'co_so_id' => $coSo1->id,
            'ma_khu_nha' => 'KN-C',
            'ten_khu_nha' => 'Nhà C - Thư viện',
            'loai_khu_nha' => 'phong_chuc_nang',
            'so_tang' => 6,
            'dien_tich_san_xay_dung' => 2500,
            'dien_tich_su_dung' => 12000,
            'nam_xay_dung' => 2020,
            'mo_ta' => 'Thư viện trường',
            'trang_thai' => 'active',
        ]);

        // Tạo khu nhà cho cơ sở 2
        $khuNha4 = KhuNha::create([
            'co_so_id' => $coSo2->id,
            'ma_khu_nha' => 'KN-TH1',
            'ten_khu_nha' => 'Nhà thực hành 1',
            'loai_khu_nha' => 'phong_hoc',
            'so_tang' => 3,
            'dien_tich_san_xay_dung' => 1800,
            'dien_tich_su_dung' => 4500,
            'nam_xay_dung' => 2017,
            'mo_ta' => 'Phòng thực hành kỹ thuật',
            'trang_thai' => 'active',
        ]);

        // Tạo phòng cho nhà A
        $phong1 = Phong::create([
            'khu_nha_id' => $khuNha1->id,
            'ma_phong' => 'A101',
            'ten_phong' => 'Phòng học A101',
            'loai_phong' => 'phong_hoc',
            'tang' => 1,
            'dien_tich' => 80,
            'suc_chua' => 60,
            'trang_thiet_bi' => 'Bàn ghế, Máy chiếu, Điều hòa',
            'mo_ta' => 'Phòng học lý thuyết',
            'trang_thai' => 'active',
        ]);

        $phong2 = Phong::create([
            'khu_nha_id' => $khuNha1->id,
            'ma_phong' => 'A201',
            'ten_phong' => 'Phòng học A201',
            'loai_phong' => 'phong_hoc',
            'tang' => 2,
            'dien_tich' => 100,
            'suc_chua' => 80,
            'trang_thiet_bi' => 'Bàn ghế, Máy chiếu, Điều hòa, Micro',
            'mo_ta' => 'Giảng đường lớn',
            'trang_thai' => 'active',
        ]);

        $phong3 = Phong::create([
            'khu_nha_id' => $khuNha1->id,
            'ma_phong' => 'A301',
            'ten_phong' => 'Phòng thí nghiệm A301',
            'loai_phong' => 'phong_thi_nghiem',
            'tang' => 3,
            'dien_tich' => 120,
            'suc_chua' => 40,
            'trang_thiet_bi' => 'Bàn thí nghiệm, Tủ hóa chất, Máy chiếu',
            'mo_ta' => 'Phòng thí nghiệm hóa học',
            'trang_thai' => 'active',
        ]);

        // Tạo phòng cho nhà B
        $phong4 = Phong::create([
            'khu_nha_id' => $khuNha2->id,
            'ma_phong' => 'B101',
            'ten_phong' => 'Văn phòng Khoa CNTT',
            'loai_phong' => 'phong_lam_viec',
            'tang' => 1,
            'dien_tich' => 50,
            'suc_chua' => 10,
            'trang_thiet_bi' => 'Bàn làm việc, Máy tính, Điều hòa',
            'mo_ta' => 'Văn phòng khoa',
            'trang_thai' => 'active',
        ]);

        $phong5 = Phong::create([
            'khu_nha_id' => $khuNha2->id,
            'ma_phong' => 'B201',
            'ten_phong' => 'Phòng họp',
            'loai_phong' => 'phong_chuc_nang',
            'tang' => 2,
            'dien_tich' => 80,
            'suc_chua' => 30,
            'trang_thiet_bi' => 'Bàn họp, Máy chiếu, Hệ thống âm thanh',
            'mo_ta' => 'Phòng họp hội đồng',
            'trang_thai' => 'active',
        ]);

        // Tạo phòng cho nhà thực hành
        $phong6 = Phong::create([
            'khu_nha_id' => $khuNha4->id,
            'ma_phong' => 'TH101',
            'ten_phong' => 'Phòng thực hành máy tính 1',
            'loai_phong' => 'phong_thuc_hanh',
            'tang' => 1,
            'dien_tich' => 100,
            'suc_chua' => 40,
            'trang_thiet_bi' => 'Máy tính, Bàn ghế, Điều hòa, Máy chiếu',
            'mo_ta' => 'Phòng máy cho sinh viên thực hành',
            'trang_thai' => 'active',
        ]);

        // Tạo thiết bị
        ThietBi::create([
            'phong_id' => $phong1->id,
            'ma_thiet_bi' => 'TB-MC-001',
            'ten_thiet_bi' => 'Máy chiếu Epson EB-X41',
            'loai_thiet_bi' => 'day_hoc',
            'hang_san_xuat' => 'Epson',
            'model' => 'EB-X41',
            'nam_mua' => 2020,
            'gia_tri' => 8000000,
            'so_luong' => 1,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'Độ phân giải XGA (1024x768), 3600 lumens',
            'mo_ta' => 'Máy chiếu cho phòng học',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => $phong1->id,
            'ma_thiet_bi' => 'TB-DH-001',
            'ten_thiet_bi' => 'Điều hòa Daikin 2HP',
            'loai_thiet_bi' => 'day_hoc',
            'hang_san_xuat' => 'Daikin',
            'model' => 'FTKC50TVMV',
            'nam_mua' => 2019,
            'gia_tri' => 12000000,
            'so_luong' => 2,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'Công suất 2HP, tiết kiệm điện',
            'mo_ta' => 'Điều hòa cho phòng học',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => $phong2->id,
            'ma_thiet_bi' => 'TB-AM-001',
            'ten_thiet_bi' => 'Hệ thống âm thanh TOA',
            'loai_thiet_bi' => 'day_hoc',
            'hang_san_xuat' => 'TOA',
            'model' => 'A-2120',
            'nam_mua' => 2021,
            'gia_tri' => 15000000,
            'so_luong' => 1,
            'don_vi_tinh' => 'bộ',
            'thong_so_ky_thuat' => 'Công suất 120W, micro không dây',
            'mo_ta' => 'Hệ thống âm thanh giảng đường',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => $phong3->id,
            'ma_thiet_bi' => 'TB-TN-001',
            'ten_thiet_bi' => 'Kính hiển vi sinh học',
            'loai_thiet_bi' => 'thi_nghiem',
            'hang_san_xuat' => 'Olympus',
            'model' => 'CX23',
            'nam_mua' => 2020,
            'gia_tri' => 25000000,
            'so_luong' => 10,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'Phóng đại 40x-1000x',
            'mo_ta' => 'Kính hiển vi cho thí nghiệm sinh học',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => $phong4->id,
            'ma_thiet_bi' => 'TB-VP-001',
            'ten_thiet_bi' => 'Máy tính Dell OptiPlex',
            'loai_thiet_bi' => 'van_phong',
            'hang_san_xuat' => 'Dell',
            'model' => 'OptiPlex 7090',
            'nam_mua' => 2021,
            'gia_tri' => 18000000,
            'so_luong' => 5,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'Core i5-11500, RAM 8GB, SSD 256GB',
            'mo_ta' => 'Máy tính cho văn phòng',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => $phong6->id,
            'ma_thiet_bi' => 'TB-TH-001',
            'ten_thiet_bi' => 'Máy tính HP ProDesk',
            'loai_thiet_bi' => 'thuc_hanh',
            'hang_san_xuat' => 'HP',
            'model' => 'ProDesk 400 G7',
            'nam_mua' => 2022,
            'gia_tri' => 15000000,
            'so_luong' => 40,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'Core i3-10100, RAM 8GB, SSD 256GB',
            'mo_ta' => 'Máy tính cho phòng thực hành',
            'trang_thai' => 'tot',
        ]);

        ThietBi::create([
            'phong_id' => null,
            'ma_thiet_bi' => 'TB-DU-001',
            'ten_thiet_bi' => 'Máy in HP LaserJet',
            'loai_thiet_bi' => 'van_phong',
            'hang_san_xuat' => 'HP',
            'model' => 'LaserJet Pro M404dn',
            'nam_mua' => 2021,
            'gia_tri' => 6000000,
            'so_luong' => 3,
            'don_vi_tinh' => 'cái',
            'thong_so_ky_thuat' => 'In đen trắng, tốc độ 38 trang/phút',
            'mo_ta' => 'Máy in dự phòng, chưa phân bổ',
            'trang_thai' => 'tot',
        ]);
    }
}

