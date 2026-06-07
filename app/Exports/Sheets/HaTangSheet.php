<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;


class HaTangSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** @var Collection */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Bang 3D';
    }

    /**
     * Dòng 1: tiêu đề (merge A1:D1)
     * Dòng 2: header cột
     * Dòng 3+: dữ liệu
     */
    public function array(): array
    {
        $rows = [
            ['Bảng 3D: Hạ tầng công nghệ thông tin', '', '', ''],
            ['STT', 'CHỈ SỐ THỐNG KÊ', 'Giá trị', 'Ghi chú'],
        ];

        foreach ($this->data as $item) {
            $rows[] = [
                $item->stt,
                $item->chi_so_thong_ke,
                $item->gia_tri,
                $item->ghi_chu,
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
                    ->getStyle("A2:D{$lastRow}")
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
