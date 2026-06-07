<?php

namespace App\Services;

use App\Models\DotBaoCao;

class BaoCaoDataService
{
    /**
     * Danh sách relations cần load cho báo cáo BGD.
     * Dùng chung cho cả preview (Inertia) và export (Excel).
     */
    private const REPORT_RELATIONS = [
        'bcLoaiPhongs',
        'bcTieuChuanCsvcs',
        'bcKhuonViens',
        'bcCongTrinhDaoTaos',
        'bcHaTangCntts',
    ];

    /**
     * Load tất cả relations báo cáo vào DotBaoCao (nếu chưa load).
     * Trả lại chính $dotBaoCao để tiện chaining.
     */
    public function getReportData(DotBaoCao $dotBaoCao): DotBaoCao
    {
        $dotBaoCao->loadMissing(self::REPORT_RELATIONS);
        return $dotBaoCao;
    }

    /**
     * Trả về array đã format sẵn cho Inertia preview.
     */
    public function getPreviewData(DotBaoCao $dotBaoCao): array
    {
        $this->getReportData($dotBaoCao);

        return [
            'id'                  => $dotBaoCao->id,
            'ten_dot'             => $dotBaoCao->ten_dot,
            'bcLoaiPhongs'        => $dotBaoCao->bcLoaiPhongs,
            'bcTieuChuanCsvcs'    => $dotBaoCao->bcTieuChuanCsvcs,
            'bcKhuonViens'        => $dotBaoCao->bcKhuonViens,
            'bcCongTrinhDaoTaos'  => $dotBaoCao->bcCongTrinhDaoTaos,
            'bcHaTangCntts'       => $dotBaoCao->bcHaTangCntts,
        ];
    }
}
