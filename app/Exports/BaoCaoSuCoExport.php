<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BaoCaoSuCoExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    /** @var Collection */
    private $data;

    const TRANG_THAI_LABELS = [
        'yeu_cau_sua_chua'    => 'Yêu cầu sửa chữa',
        'dang_sua_chua'       => 'Đang sửa chữa',
        'hoan_thanh_sua_chua' => 'Hoàn thành sửa chữa',
    ];

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Báo cáo Sự cố';
    }

    /**
     * Cột: STT | Tên tài sản, thiết bị | Phòng | Khu nhà | Hiện trạng tài sản, thiết bị | Trạng thái | Thời Gian | Đợt kiểm tra
     */
    public function array(): array
    {
        $rows = [
            [
                'STT',
                'Tên tài sản, thiết bị',
                'Phòng',
                'Khu nhà',
                'Hiện trạng tài sản, thiết bị',
                'Trạng thái',
                'Thời Gian',
                'Đợt kiểm tra',
            ],
        ];

        foreach ($this->data as $index => $item) {
            $phong  = $item->phong;
            $khuNha = null;
            if ($phong) {
                // Relationship tên khuNha (camelCase)
                $khuNha = isset($phong->khuNha) ? $phong->khuNha : null;
            }

            // Dùng đúng tên camelCase của relationship trong model
            $thietBi   = $item->thietBi;
            $tenTaiSan = ($thietBi && $thietBi->ten_thiet_bi) ? $thietBi->ten_thiet_bi : 'Cơ sở vật chất khác';

            // Hiện trạng tài sản, thiết bị = mô tả sự cố
            $hienTrang = $item->mo_ta_su_co ? $item->mo_ta_su_co : '—';

            // Trạng thái sự cố
            $trangThai = isset(self::TRANG_THAI_LABELS[$item->trang_thai])
                ? self::TRANG_THAI_LABELS[$item->trang_thai]
                : ($item->trang_thai ? $item->trang_thai : '—');

            // Thời gian báo cáo
            $thoiGian = $item->created_at
                ? \Carbon\Carbon::parse($item->created_at)->format('H:i d/m/Y ')
                : '—';

            // Đợt kiểm tra — dùng đúng tên camelCase
            $dot = $item->dotKiemTraThietBi;
            $dotKiemTra = '—';
            if ($dot) {
                $dotKiemTra = $dot->ten_dot ? $dot->ten_dot : 'Đợt không tên';
                if ($dot->ngay_bat_dau || $dot->ngay_ket_thuc) {
                    $from = $dot->ngay_bat_dau
                        ? \Carbon\Carbon::parse($dot->ngay_bat_dau)->format('d/m/Y')
                        : '—';
                    $to = $dot->ngay_ket_thuc
                        ? \Carbon\Carbon::parse($dot->ngay_ket_thuc)->format('d/m/Y')
                        : '—';
                    $dotKiemTra .= " ({$from} - {$to})";
                }
            }

            $rows[] = [
                $index + 1,
                $tenTaiSan,
                ($phong && $phong->ten_phong) ? $phong->ten_phong : '—',
                ($khuNha && $khuNha->ten_khu_nha) ? $khuNha->ten_khu_nha : '—',
                $hienTrang,
                $trangThai,
                $thoiGian,
                $dotKiemTra,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = count($this->data) + 1; // +1 header
                $range   = "A1:H{$lastRow}";

                // In đậm dòng header
                $sheet->getStyle('A1:H1')->getFont()->setBold(true);

                // Kẻ khung toàn bộ bảng
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Freeze dòng header
                $sheet->freezePane('A2');
            },
        ];
    }
}
