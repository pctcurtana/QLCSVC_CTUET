<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Template Excel mẫu để import Thiết bị.
 * Cột ma_phong phải khớp với mã phòng đã tồn tại.
 * Không có cột: id, phong_id, so_luong, don_vi_tinh, qr_token.
 * Ngày tháng: định dạng YYYY-MM-DD.
 */
class ThietBiTemplate implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'ThietBi_Import_Template';
    }

    public function array(): array
    {
        return [
            // Header
            [
                'ma_thiet_bi',
                'ma_phong',
                'serial_number',
                'ten_thiet_bi',
                'loai_thiet_bi',
                'hang_san_xuat',
                'model',
                'nam_san_xuat',
                'nam_mua',
                'ngay_mua',
                'ngay_bao_duong_cuoi',
                'chu_ky_bao_duong',
                'gia_tri',
                'thong_so_ky_thuat',
                'mo_ta',
                'trang_thai',
            ],
            // Ghi chú
            [
                'BẮT BUỘC | Mã thiết bị (duy nhất)',
                'BẮT BUỘC | Mã phòng đã tồn tại',
                'BẮT BUỘC | Số serial',
                'BẮT BUỘC | Tên thiết bị',
                'BẮT BUỘC | 1 trong các giá trị (thuc_hanh, day_hoc, van_phong, thi_nghiem)',
                'Tùy chọn | Hãng sản xuất',
                'Tùy chọn | Model thiết bị',
                'Tùy chọn | Năm sản xuất (VD: 2020)',
                'Tùy chọn | Năm mua (VD: 2020)',
                'BẮT BUỘC | Ngày mua (YYYY-MM-DD) phải là text format',
                'Tùy chọn | Ngày bảo dưỡng cuối (YYYY-MM-DD) phải là text format',
                'Tùy chọn | Chu kỳ bảo dưỡng (tháng, VD: 6)',
                'BẮT BUỘC | Giá trị (VNĐ)',
                'Tùy chọn | Thông số kỹ thuật',
                'Tùy chọn | Mô tả',
                'BẮT BUỘC | tot | can_sua_chua | hu_hong',
            ],
            // Dữ liệu mẫu
            [
                'TB00001',
                'P101',
                'SN-DELL-2024-001',
                'Máy tính để bàn Dell',
                'Máy tính',
                'Dell',
                'OptiPlex 5000',
                2023,
                2024,
                '2024-03-15',
                '2024-09-15',
                6,
                12500000,
                'CPU: i5-12500, RAM: 8GB, SSD: 256GB',
                'Dùng cho giảng dạy',
                'tot',
            ],
            [
                'TB00002',
                'P101',
                'SN-EPSON-2023-001',
                'Máy chiếu Epson EB-X51',
                'Máy chiếu',
                'Epson',
                'EB-X51',
                2022,
                2023,
                '2023-06-01',
                '',
                12,
                8900000,
                'Độ phân giải: XGA (1024x768), Độ sáng: 3600 lumens',
                '',
                'tot',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $range = 'A1:P1';

                $sheet->getStyle($range)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FA8C16']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getStyle('A2:P2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '595959']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBE6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getStyle('A3:P4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->freezePane('A3');
            },
        ];
    }
}
