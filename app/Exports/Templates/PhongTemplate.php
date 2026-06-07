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
 * Template Excel mẫu để import Phòng.
 * Cột ma_khu_nha phải khớp với mã khu nhà đã tồn tại.
 * loai_phong chỉ chấp nhận các giá trị enum được liệt kê.
 */
class PhongTemplate implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function title(): string
    {
        return 'Phong_Import_Template';
    }

    public function array(): array
    {
        return [
            // Header
            [
                'ma_phong',
                'ma_khu_nha',
                'ten_phong',
                'loai_phong',
                'tang',
                'dien_tich',
                'suc_chua',
                'trang_thiet_bi',
                'mo_ta',
                'trang_thai',
            ],
            // Ghi chú
            [
                'BẮT BUỘC | Mã phòng (duy nhất)',
                'BẮT BUỘC | Mã khu nhà đã tồn tại',
                'BẮT BUỘC | Tên phòng',
                'BẮT BUỘC | phong_hoc | phong_thi_nghiem | phong_thuc_hanh | phong_lam_viec | phong_chuc_nang',
                'BẮT BUỘC | Số tầng (0=trệt)',
                'BẮT BUỘC | Diện tích (m²)',
                'Tùy chọn | Sức chứa (người)',
                'Tùy chọn | Trang thiết bị có sẵn',
                'Tùy chọn | Mô tả',
                'BẮT BUỘC | active | maintenance | inactive',
            ],
            // Dữ liệu mẫu
            [
                'P101',
                'KN001',
                'Phòng học 101',
                'phong_hoc',
                1,
                55.5,
                50,
                'Máy chiếu, bảng trắng',
                '',
                'active',
            ],
            [
                'PTN201',
                'KN001',
                'Phòng thí nghiệm 201',
                'phong_thi_nghiem',
                2,
                80,
                30,
                'Bàn thí nghiệm, tủ hóa chất',
                'Phòng thí nghiệm hóa học',
                'active',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:J1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '722ED1']],
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
