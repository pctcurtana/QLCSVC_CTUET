<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LoaiPhongSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    /** @var Collection */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Loai Phong';
    }

    /**
     * Header ở dòng 1, dữ liệu từ dòng 2.
     */
    public function array(): array
    {
        $rows = [
            ['STT', 'Loại Phòng', 'Số lượng', 'Diện tích (m²)'],
        ];

        foreach ($this->data as $item) {
            $rows[] = [
                $item->stt,
                $item->loai_phong,
                $item->so_luong,
                $item->dien_tich,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $lastRow = count($this->data) + 1;

                $event->sheet
                    ->getStyle("A1:D{$lastRow}")
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