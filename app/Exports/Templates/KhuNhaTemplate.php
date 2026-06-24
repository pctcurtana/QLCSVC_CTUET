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
 * Template Excel mẫu để import Khu nhà.
 * Cột ma_co_so phải khớp với mã cơ sở đã tồn tại trong hệ thống.
 * Cột dien_tich_xay_dung: diện tích xây dựng 1 tầng (m²).
 * Backend tự tính: tong_dien_tich_san = dien_tich_xay_dung × so_tang
 */
class KhuNhaTemplate implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'KhuNha_Import_Template';
    }

    public function array(): array
    {
        return [
            // Header
            [
                'ma_khu_nha',
                'ma_co_so',
                'ten_khu_nha',
                'loai_khu_nha',
                'so_tang',
                'dien_tich_xay_dung',
                'he_so_su_dung_dao_tao',
                'nam_xay_dung',
                'mo_ta',
                'trang_thai',
            ],
            // Ghi chú
            [
                'BẮT BUỘC | Mã khu nhà (duy nhất)',
                'BẮT BUỘC | Mã cơ sở đã tồn tại',
                'BẮT BUỘC | Tên khu nhà',
                'BẮT BUỘC | Bắt buộc phải là (phong_hoc, phong_lam_viec, phong_chuc_nang)',
                'BẮT BUỘC | Số tầng (>= 1)',
                'BẮT BUỘC | DT xây dựng 1 tầng (m²)',
                'BẮT BUỘC | Hệ số 0-1 (VD: 0.7)',
                'Tùy chọn | Năm xây dựng (VD: 2010)',
                'Tùy chọn | Mô tả',
                'BẮT BUỘC | active hoặc inactive',
            ],
            // Dữ liệu mẫu
            [
                'KN001',
                'CS001',
                'Nhà A',
                'phong_hoc',
                6,
                750,
                0.8,
                2005,
                'Tòa nhà học chính (DT sàn XD = 750 × 6 = 4500 m²)',
                'active',
            ],
            [
                'KN002',
                'CS001',
                'Nhà B - Thực hành',
                'phong_chuc_nang',
                4,
                800,
                0.9,
                2012,
                'DT sàn XD = 800 × 4 = 3200 m²',
                'active',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $cols  = 'A1:J1';

                $sheet->getStyle($cols)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '52C41A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getStyle('A2:J2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '595959']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBE6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->getStyle('A3:J4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $sheet->freezePane('A3');
            },
        ];
    }
}
