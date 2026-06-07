<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class KhuonVienSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** @var Collection */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Bang 3A';
    }

    /**
     * Dòng 1: tiêu đề (merge A1:G1)
     * Dòng 2: header cột
     * Dòng 3+: dữ liệu
     */
    public function array(): array
    {
        $rows = [
            ['Bảng 3A: Khuôn viên trụ sở chính và các phân hiệu', '', '', '', '', '', ''],
            ['KHUÔN VIÊN', 'Ký hiệu', 'Hình thức sử dụng', 'Diện tích đất (m²)', 'Vị trí khuôn viên', 'Diện tích quy đổi (m²)', 'Địa chỉ'],
        ];

        foreach ($this->data as $item) {
            $rows[] = [
                $item->ten_khuon_vien,
                $item->ky_hieu,
                $item->hinh_thuc_su_dung,
                $item->dien_tich_dat,
                $item->vi_tri_khuon_vien,
                $item->dien_tich_quy_doi,
                $item->dia_chi,
            ];
        }

        return $rows;
    }

        public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $lastRow = count($this->data) + 2;

                $event->sheet
                    ->getStyle("A2:G{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
            },
        ];
    }
}
