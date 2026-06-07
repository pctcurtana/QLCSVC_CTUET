<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TieuChuanSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    /** @var Collection */
    private $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Tieu Chuan 3';
    }

    /**
     * Dòng 1: tiêu đề (merge A1:F1)
     * Dòng 2: header cột
     * Dòng 3+: dữ liệu
     */
    public function array(): array
    {
        $rows = [
            ['TIÊU CHUẨN 3: CƠ SỞ VẬT CHẤT', '', '', '', '', ''],
            ['Mã', 'CHỈ SỐ ĐÁNH GIÁ', 'NGƯỠNG', 'THỰC TẾ', 'KẾT QUẢ', 'GIẢI TRÌNH'],
        ];

        foreach ($this->data as $item) {
            $ketQua = '';
            if ($item->ket_qua === 'dat') {
                $ketQua = 'Đạt';
            } elseif ($item->ket_qua === 'khong_dat') {
                $ketQua = 'Không đạt';
            }

            $rows[] = [
                $item->ma_chi_so,
                $item->chi_so_danh_gia,
                $item->nguong,
                $item->thuc_te,
                $ketQua,
                $item->giai_trinh,
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
                    ->getStyle("A2:F{$lastRow}")
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
