<?php

namespace App\Exports;

use App\Models\DotBaoCao;
use App\Exports\Sheets\LoaiPhongSheet;
use App\Exports\Sheets\TieuChuanSheet;
use App\Exports\Sheets\KhuonVienSheet;
use App\Exports\Sheets\CongTrinhSheet;
use App\Exports\Sheets\HaTangSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BaoCaoBgdExport implements WithMultipleSheets
{
    /** @var DotBaoCao */
    protected $dotBaoCao;

    /** @var string */
    protected $loaiBaoCao;

    public function __construct(DotBaoCao $dotBaoCao, string $loaiBaoCao = 'all')
    {
        $this->dotBaoCao  = $dotBaoCao;
        $this->loaiBaoCao = $loaiBaoCao;
    }

    /**
     * Trả về danh sách sheet sẽ được tạo trong file Excel.
     * Mỗi sheet là một class riêng biệt, nhận Collection từ relation tương ứng.
     *
     * @return array<\Maatwebsite\Excel\Concerns\WithTitle>
     */
    public function sheets(): array
    {
        $sheets = [];

        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'loai_phong') {
            $sheets[] = new LoaiPhongSheet($this->dotBaoCao->bcLoaiPhongs);
        }

        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'tieu_chuan') {
            $sheets[] = new TieuChuanSheet($this->dotBaoCao->bcTieuChuanCsvcs);
        }

        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'khuon_vien') {
            $sheets[] = new KhuonVienSheet($this->dotBaoCao->bcKhuonViens);
        }

        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'cong_trinh') {
            $sheets[] = new CongTrinhSheet($this->dotBaoCao->bcCongTrinhDaoTaos);
        }

        if ($this->loaiBaoCao === 'all' || $this->loaiBaoCao === 'ha_tang') {
            $sheets[] = new HaTangSheet($this->dotBaoCao->bcHaTangCntts);
        }

        return $sheets;
    }
}
