<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CongTrinhSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** @var Collection */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Bang 3B';
    }

    /**
     * Dòng 1: tiêu đề (merge A1:G1)
     * Dòng 2: header cột
     * Dòng 3+: dữ liệu
     */
    public function array(): array
    {
        $rows = [
            ['Bảng 3B: Công trình phục vụ đào tạo', '', '', '', '', '', ''],
            [
                'STT',
                'CÔNG TRÌNH',
                'Ký hiệu',
                'Tổng diện tích sàn xây dựng',
                'Hệ số diện tích sử dụng cho đào tạo (Ksd)',
                'Diện tích sàn sử dụng cho đào tạo (m²)',
                'Địa chỉ',
            ],
        ];

        foreach ($this->data as $item) {
            $rows[] = [
                $item->is_tong ? '' : $item->stt,
                $item->ten_cong_trinh,
                $item->ky_hieu,
                $item->tong_dien_tich_san,
                $item->is_tong ? '' : $item->he_so_dien_tich,
                $item->dien_tich_san_dao_tao,
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
