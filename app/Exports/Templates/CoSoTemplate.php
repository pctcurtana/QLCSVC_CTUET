<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * Template Excel mẫu để import Cơ sở.
 * Dòng 1: Header (tên cột)
 * Dòng 2: Ghi chú hướng dẫn (màu vàng)
 * Dòng 3+: Dữ liệu mẫu
 */
class CoSoTemplate implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'CoSo_Import_Template';
    }

    public function array(): array
    {
        return [
            // Dòng 1: Header
            [
                'ma_co_so',
                'ten_co_so',
                'dia_chi',
                'dien_tich_dat',
                'vi_tri_khuon_vien',
                'mo_ta',
                'trang_thai',
            ],
            // Dòng 2: Ghi chú hướng dẫn
            [
                'BẮT BUỘC | Mã cơ sở (duy nhất)',
                'BẮT BUỘC | Tên đầy đủ',
                'BẮT BUỘC | Địa chỉ',
                'BẮT BUỘC | Số (m²)',
                'BẮT BUỘC | Hệ số (VD: 2.5)',
                'Tùy chọn | Mô tả',
                'BẮT BUỘC | active hoặc inactive',
            ],
            // Dòng 3: Dữ liệu mẫu
            [
                'CS001',
                'Cơ sở A - Nguyễn Văn Cừ',
                'Số 1 Võ Văn Ngân, Thủ Đức, TP.HCM',
                12500,
                2.5,
                'Cơ sở chính',
                'active',
            ],
            [
                'CS002',
                'Cơ sở B - Dĩ An',
                'Khu phố 6, Dĩ An, Bình Dương',
                8000,
                2.0,
                '',
                'active',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Style header row (dòng 1)
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1890FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Style ghi chú row (dòng 2)
                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '595959']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFBE6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Style data rows (dòng 3 trở đi)
                $sheet->getStyle('A3:G4')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                // Freeze header
                $sheet->freezePane('A3');
            },
        ];
    }
}
