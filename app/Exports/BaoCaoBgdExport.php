<?php

namespace App\Exports;

use App\Models\DotBaoCao;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaoCaoBgdExport
{
    protected $dotBaoCao;
    protected $loaiBaoCao;

    public function __construct(DotBaoCao $dotBaoCao, string $loaiBaoCao = 'all')
    {
        $this->dotBaoCao = $dotBaoCao;
        $this->loaiBaoCao = $loaiBaoCao;
    }

    public function download(string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // Tạo các sheet theo loại báo cáo
        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'loai_phong') {
            $this->createLoaiPhongSheet($spreadsheet);
        }
        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'tieu_chuan') {
            $this->createTieuChuanSheet($spreadsheet);
        }
        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'khuon_vien') {
            $this->createKhuonVienSheet($spreadsheet);
        }
        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'cong_trinh') {
            $this->createCongTrinhSheet($spreadsheet);
        }
        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'ha_tang') {
            $this->createHaTangSheet($spreadsheet);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Sheet 1: Loại phòng phục vụ tuyển sinh
     */
    private function createLoaiPhongSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Loai Phong');

        // Header
        $sheet->setCellValue('A1', 'STT');
        $sheet->setCellValue('B1', 'Loại Phòng');
        $sheet->setCellValue('C1', 'Số lượng');
        $sheet->setCellValue('D1', 'Diện tích');

        $this->styleHeader($sheet, 'A1:D1');
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // Data
        $row = 2;
        foreach ($this->dotBaoCao->bcLoaiPhongs as $item) {
            $sheet->setCellValue('A' . $row, $item->stt);
            $sheet->setCellValue('B' . $row, $item->loai_phong);
            $sheet->setCellValue('C' . $row, $item->so_luong);
            $sheet->setCellValue('D' . $row, $item->dien_tich);

            if ($item->is_tong) {
                $this->styleTotalRow($sheet, 'A' . $row . ':D' . $row);
            }
            $row++;
        }

        $this->styleBorder($sheet, 'A1:D' . ($row - 1));
    }

    /**
     * Sheet 2: Tiêu chuẩn 3 - Cơ sở vật chất
     */
    private function createTieuChuanSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Tieu Chuan 3');

        // Title
        $sheet->setCellValue('A1', 'TIÊU CHUẨN 3: CƠ SỞ VẬT CHẤT');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header
        $sheet->setCellValue('A2', 'Mã');
        $sheet->setCellValue('B2', 'CHỈ SỐ ĐÁNH GIÁ');
        $sheet->setCellValue('C2', 'NGƯỠNG');
        $sheet->setCellValue('D2', 'THỰC TẾ');
        $sheet->setCellValue('E2', 'KẾT QUẢ');
        $sheet->setCellValue('F2', 'GIẢI TRÌNH');

        $this->styleHeader($sheet, 'A2:F2');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(25);

        // Data
        $row = 3;
        foreach ($this->dotBaoCao->bcTieuChuanCsvcs as $item) {
            $sheet->setCellValue('A' . $row, $item->ma_chi_so);
            $sheet->setCellValue('B' . $row, $item->chi_so_danh_gia);
            $sheet->setCellValue('C' . $row, $item->nguong);
            $sheet->setCellValue('D' . $row, $item->thuc_te);
            $sheet->setCellValue('E' . $row, $item->ket_qua === 'dat' ? 'Đạt' : ($item->ket_qua === 'khong_dat' ? 'Không đạt' : ''));
            $sheet->setCellValue('F' . $row, $item->giai_trinh);

            if ($item->ket_qua === 'dat') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('90EE90');
            } elseif ($item->ket_qua === 'khong_dat') {
                $sheet->getStyle('E' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFB6C1');
            }

            $row++;
        }

        $this->styleBorder($sheet, 'A2:F' . ($row - 1));
    }

    /**
     * Sheet 3: Bảng 3A - Khuôn viên
     */
    private function createKhuonVienSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Bang 3A');

        // Title
        $sheet->setCellValue('A1', 'Bảng 3A: Khuôn viên trụ sở chính và các phân hiệu');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // Header
        $headers = ['KHUÔN VIÊN', 'Ký hiệu', 'Hình thức sử dụng', 'Diện tích đất (m2)', 'Vị trí khuôn viên', 'Diện tích quy đổi (m2)', 'Địa chỉ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '2', $header);
            $col++;
        }

        $this->styleHeader($sheet, 'A2:G2');
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(40);

        // Data
        $row = 3;
        foreach ($this->dotBaoCao->bcKhuonViens as $item) {
            $sheet->setCellValue('A' . $row, $item->ten_khuon_vien);
            $sheet->setCellValue('B' . $row, $item->ky_hieu);
            $sheet->setCellValue('C' . $row, $item->hinh_thuc_su_dung);
            $sheet->setCellValue('D' . $row, $item->dien_tich_dat);
            $sheet->setCellValue('E' . $row, $item->vi_tri_khuon_vien);
            $sheet->setCellValue('F' . $row, $item->dien_tich_quy_doi);
            $sheet->setCellValue('G' . $row, $item->dia_chi);

            if ($item->is_tong) {
                $this->styleTotalRow($sheet, 'A' . $row . ':G' . $row);
            }
            $row++;
        }

        $this->styleBorder($sheet, 'A2:G' . ($row - 1));
    }

    /**
     * Sheet 4: Bảng 3B - Công trình phục vụ đào tạo
     */
    private function createCongTrinhSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Bang 3B');

        // Title
        $sheet->setCellValue('A1', 'Bảng 3B: Công trình phục vụ đào tạo');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // Header
        $headers = ['STT', 'CÔNG TRÌNH', 'Ký hiệu', 'Tổng diện tích sàn xây dựng', 'Hệ số diện tích sử dụng cho đào tạo (Ksd)', 'Diện tích sàn sử dụng cho đào tạo (m2)', 'Địa chỉ'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '2', $header);
            $col++;
        }

        $this->styleHeader($sheet, 'A2:G2');
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(28);
        $sheet->getColumnDimension('G')->setWidth(30);

        // Data
        $row = 3;
        foreach ($this->dotBaoCao->bcCongTrinhDaoTaos as $item) {
            if (!$item->is_tong) {
                $sheet->setCellValue('A' . $row, $item->stt);
            }
            $sheet->setCellValue('B' . $row, $item->ten_cong_trinh);
            $sheet->setCellValue('C' . $row, $item->ky_hieu);
            $sheet->setCellValue('D' . $row, $item->tong_dien_tich_san);
            $sheet->setCellValue('E' . $row, $item->is_tong ? '' : $item->he_so_dien_tich);
            $sheet->setCellValue('F' . $row, $item->dien_tich_san_dao_tao);
            $sheet->setCellValue('G' . $row, $item->dia_chi);

            if ($item->is_tong) {
                $this->styleTotalRow($sheet, 'A' . $row . ':G' . $row);
            }
            $row++;
        }

        $this->styleBorder($sheet, 'A2:G' . ($row - 1));
    }

    /**
     * Sheet 5: Bảng 3D - Hạ tầng CNTT
     */
    private function createHaTangSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Bang 3D');

        // Title
        $sheet->setCellValue('A1', 'Bảng 3D: Hạ tầng công nghệ thông tin');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        // Header
        $sheet->setCellValue('A2', 'STT');
        $sheet->setCellValue('B2', 'CHỈ SỐ THỐNG KÊ');
        $sheet->setCellValue('C2', 'Giá trị');
        $sheet->setCellValue('D2', 'Ghi chú');

        $this->styleHeader($sheet, 'A2:D2');
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(35);

        // Data
        $row = 3;
        foreach ($this->dotBaoCao->bcHaTangCntts as $item) {
            $sheet->setCellValue('A' . $row, $item->stt);
            $sheet->setCellValue('B' . $row, $item->chi_so_thong_ke);
            $sheet->setCellValue('C' . $row, $item->gia_tri);
            $sheet->setCellValue('D' . $row, $item->ghi_chu);
            $row++;
        }

        $this->styleBorder($sheet, 'A2:D' . ($row - 1));
    }

    /**
     * Style header row
     */
    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF99'],
            ],
        ]);
    }

    /**
     * Style total row
     */
    private function styleTotalRow($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0'],
            ],
        ]);
    }

    /**
     * Style border
     */
    private function styleBorder($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
}
