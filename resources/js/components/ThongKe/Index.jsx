import React, { useState, useMemo } from 'react';
import MainLayout from '../Layout/MainLayout';
import {
    Card, Row, Col, Statistic, Typography, Space, Table, Tag, Segmented, Tooltip, Select,
} from 'antd';
import {
    BankOutlined, HomeOutlined, AppstoreOutlined, ToolOutlined,
    DollarOutlined, AreaChartOutlined, WarningOutlined,
} from '@ant-design/icons';
import {
    BarChart, Bar, PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip as RTooltip,
    Legend, ResponsiveContainer, LineChart, Line, Area, AreaChart,
} from 'recharts';
import KpiCard from '../Common/KpiCard';

const { Title, Text } = Typography;

// ─── Palette đồng bộ toàn trang ──────────────────────────────────────────────
const P = {
    blue:   '#4096ff',
    green:  '#52c41a',
    teal:   '#13c2c2',
    purple: '#7c3aed',
    orange: '#fa8c16',
    red:    '#f5222d',
    yellow: '#fadb14',
    pink:   '#eb2f96',
};
const PIE_COLORS = [P.blue, P.green, P.teal, P.purple, P.orange, P.red, P.pink, P.yellow];

// ─── Helpers ──────────────────────────────────────────────────────────────────
const formatCurrency = (v) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);

const formatNumber = (v) => new Intl.NumberFormat('vi-VN').format(v || 0);

const trangThaiTag = (tt) => {
    const map = {
        active:       { color: 'green',   label: 'Hoạt động' },
        maintenance:  { color: 'orange',  label: 'Bảo trì' },
        inactive:     { color: 'default', label: 'Không HĐ' },
        broken:       { color: 'red',     label: 'Hỏng' },
        tot:          { color: 'green',   label: 'Tốt' },
        can_sua_chua: { color: 'orange',  label: 'Cần sửa chữa' },
        hu_hong:      { color: 'red',     label: 'Hư hỏng' },
    };
    const m = map[tt] || { color: 'default', label: tt };
    return <Tag color={m.color}>{m.label}</Tag>;
};

const loaiPhongLabel = (l) => ({
    phong_hoc: 'Phòng học', phong_thi_nghiem: 'Thí nghiệm',
    phong_thuc_hanh: 'Thực hành', phong_lam_viec: 'Làm việc', phong_chuc_nang: 'Chức năng',
}[l] || l);

const loaiKhuNhaLabel = (l) => ({
    phong_hoc: 'Phòng học', phong_lam_viec: 'Làm việc', phong_chuc_nang: 'Chức năng',
}[l] || l);

const loaiThietBiLabel = (l) => ({
    van_phong: 'Văn phòng', day_hoc: 'Dạy học', thi_nghiem: 'Thí nghiệm', thuc_hanh: 'Thực hành',
}[l] || l);

// ─── Shared chart config ──────────────────────────────────────────────────────
const CHART_HEIGHT = 280;

const axisStyle = { fill: '#8c8c8c', fontSize: 12 };

const tooltipStyle = {
    contentStyle: {
        borderRadius: 10,
        border: 'none',
        boxShadow: '0 4px 16px rgba(0,0,0,0.12)',
        padding: '10px 16px',
        fontSize: 13,
    },
    labelStyle: { fontWeight: 600, color: '#222', marginBottom: 4 },
    itemStyle: { color: '#555' },
    cursor: { fill: 'rgba(64,150,255,0.06)' },
};

const gridStyle = {
    strokeDasharray: '0',
    stroke: '#f0f0f0',
    strokeWidth: 1,
    vertical: false,
};

// ─── Donut chart với label ở giữa ────────────────────────────────────────────
const DonutChart = ({ data, height = CHART_HEIGHT }) => {
    const total = data.reduce((s, d) => s + (d.value || 0), 0);
    return (
        <ResponsiveContainer width="100%" height={height}>
            <PieChart>
                <Pie
                    data={data}
                    cx="50%" cy="50%"
                    innerRadius="52%"
                    outerRadius="75%"
                    paddingAngle={3}
                    dataKey="value"
                >
                    {data.map((_, i) => (
                        <Cell key={i} fill={PIE_COLORS[i % PIE_COLORS.length]} stroke="none" />
                    ))}
                </Pie>
                <RTooltip
                    {...tooltipStyle}
                    formatter={(v, name) => [`${formatNumber(v)} (${total ? ((v / total) * 100).toFixed(1) : 0}%)`, name]}
                />
                <Legend
                    iconType="circle"
                    iconSize={8}
                    formatter={(value) => <span style={{ color: '#555', fontSize: 12 }}>{value}</span>}
                />
                {/* Label tổng ở giữa */}
                <text x="50%" y="50%" textAnchor="middle" dominantBaseline="middle">
                    <tspan x="50%" dy="-8" fontSize={22} fontWeight={700} fill="#222">{formatNumber(total)}</tspan>
                    <tspan x="50%" dy={22} fontSize={11} fill="#999">Tổng</tspan>
                </text>
            </PieChart>
        </ResponsiveContainer>
    );
};

// ─── Bar chart chuẩn ─────────────────────────────────────────────────────────
const StyledBarChart = ({ data, bars, height = CHART_HEIGHT, margin, layout, children }) => (
    <ResponsiveContainer width="100%" height={height}>
        <BarChart data={data} margin={margin || { top: 4, right: 8, bottom: 4, left: 0 }} layout={layout}>
            <CartesianGrid {...gridStyle} />
            {children}
            <RTooltip {...tooltipStyle} />
            <Legend
                iconType="circle"
                iconSize={8}
                wrapperStyle={{ paddingTop: 12 }}
                formatter={(value) => <span style={{ color: '#555', fontSize: 12 }}>{value}</span>}
            />
            {bars.map((b, i) => (
                <Bar
                    key={i}
                    yAxisId={b.yAxisId}
                    dataKey={b.dataKey}
                    name={b.name}
                    fill={b.fill}
                    barSize={b.barSize || 28}
                    radius={[4, 4, 0, 0]}
                    background={{ fill: '#fafafa', radius: [4, 4, 0, 0] }}
                />
            ))}
        </BarChart>
    </ResponsiveContainer>
);

// ─────────────────────────────────────────────────────
// TAB: CƠ SỞ
// ─────────────────────────────────────────────────────
const TabCoSo = ({ data }) => {
    const { tong_quan: tq, chi_tiet, bieu_do_dien_tich, bieu_do_so_luong, bieu_do_trang_thai } = data;

    const columns = [
        { title: 'Mã', dataIndex: 'ma_co_so', width: 100, fixed: 'left' },
        { title: 'Tên cơ sở', dataIndex: 'ten_co_so', ellipsis: true },
        { title: 'Địa chỉ', dataIndex: 'dia_chi', ellipsis: true },
        { title: 'DT đất (m²)', dataIndex: 'dien_tich_dat', align: 'right', render: v => formatNumber(v) },
        { title: 'DT quy đổi (m²)', dataIndex: 'dien_tich_quy_doi', align: 'right', render: v => formatNumber(parseFloat(v)) },
        { title: 'Toà nhà', dataIndex: 'so_khu_nha', align: 'center' },
        { title: 'Phòng', dataIndex: 'so_phong', align: 'center' },
        { title: 'Thiết bị', dataIndex: 'so_thiet_bi', align: 'center' },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            <Row gutter={[16, 16]}>
                {[
                    { title: 'Tổng số cơ sở',   value: tq?.tong_co_so,                                           icon: <BankOutlined />,      color: P.blue },
                    { title: 'Tổng DT đất',      value: `${formatNumber(tq?.tong_dien_tich_dat)} m²`,            icon: <AreaChartOutlined />,  color: P.green },
                    { title: 'Tổng DT quy đổi', value: `${formatNumber(Math.round(tq?.tong_dien_tich_quy_doi))} m²`, icon: <AreaChartOutlined />, color: P.teal },
                    { title: 'Đang hoạt động',  value: tq?.co_so_hoat_dong,                                      icon: <BankOutlined />,       color: P.green },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <KpiCard title={item.title} value={item.value} icon={item.icon} color={item.color} />
                    </Col>
                ))}
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={14}>
                    <Card title="Diện tích đất & quy đổi theo cơ sở (m²)" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <StyledBarChart
                            data={bieu_do_dien_tich}
                            margin={{ bottom: 40, left: 8 }}
                            bars={[
                                { dataKey: 'dienTichDat',    name: 'DT đất',      fill: P.blue,  barSize: 24 },
                                { dataKey: 'dienTichQuyDoi', name: 'DT quy đổi', fill: P.teal,  barSize: 24 },
                            ]}
                        >
                            <XAxis dataKey="name" angle={-12} textAnchor="end" interval={0} tick={axisStyle} />
                            <YAxis tickFormatter={v => `${Math.round(v / 1000)}k`} tick={axisStyle} />
                        </StyledBarChart>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái cơ sở" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <DonutChart data={bieu_do_trang_thai} />
                    </Card>
                </Col>
            </Row>

            <Card title="Số toà nhà · Phòng · Thiết bị theo cơ sở" bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <StyledBarChart
                    data={bieu_do_so_luong}
                    margin={{ bottom: 40, left: 0 }}
                    bars={[
                        { dataKey: 'soKhuNha',  name: 'Toà nhà',  fill: P.blue,   barSize: 18 },
                        { dataKey: 'soPhong',   name: 'Phòng',    fill: P.green,  barSize: 18 },
                        { dataKey: 'soThietBi', name: 'Thiết bị', fill: P.orange, barSize: 18 },
                    ]}
                >
                    <XAxis dataKey="name" angle={-12} textAnchor="end" interval={0} tick={axisStyle} />
                    <YAxis tick={axisStyle} />
                </StyledBarChart>
            </Card>

            <Card title={`Chi tiết (${chi_tiet?.length || 0} cơ sở)`} bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Table dataSource={chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 900 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: TOÀ NHÀ
// ─────────────────────────────────────────────────────
const TabKhuNha = ({ data, danhSachCoSo }) => {
    const [filterCoSo, setFilterCoSo] = useState(null);
    const { chi_tiet } = data;

    // Lọc dữ liệu theo cơ sở được chọn
    const filteredData = useMemo(() => {
        const list = filterCoSo
            ? chi_tiet.filter(r => r.co_so_id === filterCoSo)
            : chi_tiet;

        // Tính toán tổng quan từ dữ liệu đã lọc
        const tong_quan = {
            tong_khu_nha: list.length,
            tong_dien_tich_san: list.reduce((s, r) => s + (parseFloat(r.tong_dien_tich_san) || 0), 0),
            tong_dt_dao_tao: list.reduce((s, r) => s + (parseFloat(r.dt_dao_tao) || 0), 0),
            khu_nha_hoat_dong: list.filter(r => r.trang_thai === 'active').length,
        };

        // Biểu đồ theo loại
        const loaiMap = {};
        list.forEach(r => {
            const k = r.loai_khu_nha || 'khac';
            if (!loaiMap[k]) loaiMap[k] = { soLuong: 0, tongDT: 0 };
            loaiMap[k].soLuong++;
            loaiMap[k].tongDT += parseFloat(r.tong_dien_tich_san) || 0;
        });
        const loaiLabels = { phong_hoc: 'Phòng học', phong_lam_viec: 'Phòng làm việc', phong_chuc_nang: 'Phòng chức năng' };
        const bieu_do_loai = Object.entries(loaiMap).map(([k, v]) => ({
            name: loaiLabels[k] || k, soLuong: v.soLuong, tongDT: v.tongDT,
        }));

        // Biểu đồ trạng thái
        const ttMap = {};
        list.forEach(r => {
            const k = r.trang_thai || 'khac';
            ttMap[k] = (ttMap[k] || 0) + 1;
        });
        const ttLabels = { active: 'Hoạt động', maintenance: 'Bảo trì', inactive: 'Không HĐ' };
        const bieu_do_trang_thai = Object.entries(ttMap).map(([k, v]) => ({
            name: ttLabels[k] || k, value: v,
        }));

        // Biểu đồ diện tích
        const bieu_do_dien_tich = list.map(r => ({
            name: r.ten_khu_nha,
            sanXD: parseFloat(r.tong_dien_tich_san) || 0,
            dtDaoTao: parseFloat(r.dt_dao_tao) || 0,
        }));

        return { tong_quan, chi_tiet: list, bieu_do_loai, bieu_do_trang_thai, bieu_do_dien_tich };
    }, [chi_tiet, filterCoSo]);

    const { tong_quan: tq, bieu_do_loai, bieu_do_dien_tich, bieu_do_trang_thai } = filteredData;

    const columns = [
        { title: 'Mã', dataIndex: 'ma_khu_nha', width: 100, fixed: 'left' },
        { title: 'Tên toà nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
        { title: 'Cơ sở', dataIndex: 'ten_co_so', ellipsis: true },
        { title: 'Loại', dataIndex: 'loai_khu_nha', render: loaiKhuNhaLabel },
        { title: 'Số tầng', dataIndex: 'so_tang', align: 'center' },
        { title: 'DT sàn XD (m²)', dataIndex: 'tong_dien_tich_san', align: 'right', render: v => formatNumber(v) },
        { title: 'DT đào tạo (m²)', dataIndex: 'dt_dao_tao', align: 'right', render: v => formatNumber(parseFloat(v)) },
        { title: 'Số phòng', dataIndex: 'so_phong', align: 'center' },
        { title: 'Thiết bị', dataIndex: 'so_thiet_bi', align: 'center' },
        { title: 'Năm XD', dataIndex: 'nam_xay_dung', align: 'center' },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            {/* Filter */}
            <Card bordered={false} style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Space wrap>
                    <span style={{ fontWeight: 600 }}>Lọc theo:</span>
                    <Select
                        placeholder="Tất cả cơ sở"
                        allowClear
                        style={{ width: 220 }}
                        value={filterCoSo}
                        onChange={setFilterCoSo}
                        options={danhSachCoSo?.map(cs => ({ value: cs.id, label: cs.ten_co_so })) || []}
                    />
                </Space>
            </Card>

            <Row gutter={[16, 16]}>
                {[
                    { title: 'Tổng toà nhà',    value: tq?.tong_khu_nha,                                          icon: <HomeOutlined />,      color: P.blue },
                    { title: 'Tổng DT sàn XD', value: `${formatNumber(tq?.tong_dien_tich_san)} m²`,              icon: <AreaChartOutlined />,  color: P.green },
                    { title: 'Tổng DT đào tạo', value: `${formatNumber(Math.round(tq?.tong_dt_dao_tao || 0))} m²`,    icon: <AreaChartOutlined />,  color: P.teal },
                    { title: 'Đang hoạt động', value: tq?.khu_nha_hoat_dong,                                       icon: <HomeOutlined />,       color: P.green },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <KpiCard title={item.title} value={item.value} icon={item.icon} color={item.color} />
                    </Col>
                ))}
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={12}>
                    <Card title="Phân bố theo loại toà nhà" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <StyledBarChart
                            data={bieu_do_loai}
                            bars={[{ dataKey: 'soLuong', name: 'Số lượng', fill: P.blue, barSize: 40 }]}
                        >
                            <XAxis dataKey="name" tick={axisStyle} />
                            <YAxis tick={axisStyle} />
                        </StyledBarChart>
                    </Card>
                </Col>
                <Col xs={24} lg={12}>
                    <Card title="Trạng thái toà nhà" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <DonutChart data={bieu_do_trang_thai} />
                    </Card>
                </Col>
            </Row>

            <Card title="Diện tích sàn & đào tạo theo toà nhà (m²)" bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <StyledBarChart
                    data={bieu_do_dien_tich}
                    height={300}
                    margin={{ bottom: 50, left: 8 }}
                    bars={[
                        { dataKey: 'sanXD',    name: 'DT sàn XD',    fill: P.blue,  barSize: 16 },
                        { dataKey: 'dtDaoTao', name: 'DT đào tạo',   fill: P.green, barSize: 16 },
                    ]}
                >
                    <XAxis dataKey="name" angle={-12} textAnchor="end" interval={0} tick={{ ...axisStyle, fontSize: 11 }} />
                    <YAxis tickFormatter={v => `${Math.round(v / 1000)}k`} tick={axisStyle} />
                </StyledBarChart>
            </Card>

            <Card title={`Chi tiết (${filteredData.chi_tiet?.length || 0} toà nhà)`} bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Table dataSource={filteredData.chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 1000 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: PHÒNG
// ─────────────────────────────────────────────────────
const TabPhong = ({ data, danhSachCoSo, danhSachKhuNha }) => {
    const [filterCoSo, setFilterCoSo] = useState(null);
    const [filterKhuNha, setFilterKhuNha] = useState(null);
    const { chi_tiet } = data;

    // Danh sách khu nhà theo cơ sở đã chọn
    const khuNhaOptions = useMemo(() => {
        if (!filterCoSo) return danhSachKhuNha || [];
        return (danhSachKhuNha || []).filter(kn => kn.co_so_id === filterCoSo);
    }, [filterCoSo, danhSachKhuNha]);

    // Reset khu nhà khi đổi cơ sở
    const handleCoSoChange = (val) => {
        setFilterCoSo(val);
        setFilterKhuNha(null);
    };

    // Lọc dữ liệu
    const filteredData = useMemo(() => {
        let list = chi_tiet;
        if (filterCoSo) {
            list = list.filter(r => r.co_so_id === filterCoSo);
        }
        if (filterKhuNha) {
            list = list.filter(r => r.khu_nha_id === filterKhuNha);
        }

        // Tính toán tổng quan
        const tong_quan = {
            tong_phong: list.length,
            tong_dien_tich: list.reduce((s, r) => s + (parseFloat(r.dien_tich) || 0), 0),
            tong_suc_chua: list.reduce((s, r) => s + (parseInt(r.suc_chua) || 0), 0),
            phong_bao_tri: list.filter(r => r.trang_thai === 'maintenance').length,
        };

        // Biểu đồ theo loại
        const loaiMap = {};
        list.forEach(r => {
            const k = r.loai_phong || 'khac';
            if (!loaiMap[k]) loaiMap[k] = { soLuong: 0, tongDT: 0, sucChua: 0 };
            loaiMap[k].soLuong++;
            loaiMap[k].tongDT += parseFloat(r.dien_tich) || 0;
            loaiMap[k].sucChua += parseInt(r.suc_chua) || 0;
        });
        const loaiLabels = {
            phong_hoc: 'Phòng học', phong_thi_nghiem: 'Phòng thí nghiệm', phong_thuc_hanh: 'Phòng thực hành',
            phong_lam_viec: 'Phòng làm việc', phong_chuc_nang: 'Phòng chức năng',
        };
        const bieu_do_loai = Object.entries(loaiMap).map(([k, v]) => ({
            name: loaiLabels[k] || k, soLuong: v.soLuong, tongDT: v.tongDT, sucChua: v.sucChua,
        }));

        // Biểu đồ trạng thái
        const ttMap = {};
        list.forEach(r => {
            const k = r.trang_thai || 'khac';
            ttMap[k] = (ttMap[k] || 0) + 1;
        });
        const ttLabels = { active: 'Hoạt động', maintenance: 'Bảo trì', inactive: 'Không HĐ' };
        const bieu_do_trang_thai = Object.entries(ttMap).map(([k, v]) => ({
            name: ttLabels[k] || k, value: v,
        }));

        // Biểu đồ theo tầng
        const tangMap = {};
        list.forEach(r => {
            const t = r.tang ?? 0;
            tangMap[t] = (tangMap[t] || 0) + 1;
        });
        const bieu_do_tang = Object.entries(tangMap)
            .sort((a, b) => parseInt(a[0]) - parseInt(b[0]))
            .map(([k, v]) => ({ name: `Tầng ${k}`, soPhong: v }));

        return { tong_quan, chi_tiet: list, bieu_do_loai, bieu_do_trang_thai, bieu_do_tang };
    }, [chi_tiet, filterCoSo, filterKhuNha]);

    const { tong_quan: tq, bieu_do_loai, bieu_do_trang_thai, bieu_do_tang } = filteredData;

    const columns = [
        { title: 'Mã', dataIndex: 'ma_phong', width: 90, fixed: 'left' },
        { title: 'Tên phòng', dataIndex: 'ten_phong', ellipsis: true },
        { title: 'Toà nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
        { title: 'Cơ sở', dataIndex: 'ten_co_so', ellipsis: true },
        { title: 'Loại', dataIndex: 'loai_phong', render: loaiPhongLabel },
        { title: 'Tầng', dataIndex: 'tang', align: 'center' },
        { title: 'DT (m²)', dataIndex: 'dien_tich', align: 'right', render: v => formatNumber(v) },
        { title: 'Sức chứa', dataIndex: 'suc_chua', align: 'center' },
        { title: 'Thiết bị', dataIndex: 'so_thiet_bi', align: 'center' },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            {/* Filter cascading */}
            <Card bordered={false} style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Space wrap>
                    <span style={{ fontWeight: 600 }}>Lọc theo:</span>
                    <Select
                        placeholder="Tất cả cơ sở"
                        allowClear
                        style={{ width: 220 }}
                        value={filterCoSo}
                        onChange={handleCoSoChange}
                        options={danhSachCoSo?.map(cs => ({ value: cs.id, label: cs.ten_co_so })) || []}
                    />
                    <Select
                        placeholder="Tất cả toà nhà"
                        allowClear
                        style={{ width: 220 }}
                        value={filterKhuNha}
                        onChange={setFilterKhuNha}
                        options={khuNhaOptions.map(kn => ({ value: kn.id, label: kn.ten_khu_nha }))}
                        disabled={!filterCoSo && khuNhaOptions.length === 0}
                    />
                </Space>
            </Card>

            <Row gutter={[16, 16]}>
                {[
                    { title: 'Tổng số phòng',  value: tq?.tong_phong,                          icon: <AppstoreOutlined />, color: P.blue },
                    { title: 'Tổng diện tích', value: `${formatNumber(tq?.tong_dien_tich)} m²`, icon: <AreaChartOutlined />, color: P.green },
                    { title: 'Tổng sức chứa', value: formatNumber(tq?.tong_suc_chua),           icon: <AppstoreOutlined />, color: P.teal },
                    { title: 'Đang bảo trì',  value: tq?.phong_bao_tri,                         icon: <WarningOutlined />,  color: P.orange },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <KpiCard title={item.title} value={item.value} icon={item.icon} color={item.color} />
                    </Col>
                ))}
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={14}>
                    <Card title="Phân bố theo loại phòng" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <StyledBarChart
                            data={bieu_do_loai}
                            bars={[
                                { dataKey: 'soLuong', name: 'Số phòng',  fill: P.blue,   barSize: 28 },
                                { dataKey: 'sucChua', name: 'Sức chứa', fill: P.purple, barSize: 28 },
                            ]}
                        >
                            <XAxis dataKey="name" tick={axisStyle} />
                            <YAxis tick={axisStyle} />
                        </StyledBarChart>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái phòng" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <DonutChart data={bieu_do_trang_thai} />
                    </Card>
                </Col>
            </Row>

            <Card title="Phân bố phòng theo tầng" bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <StyledBarChart
                    data={bieu_do_tang}
                    height={240}
                    bars={[{ dataKey: 'soPhong', name: 'Số phòng', fill: P.teal, barSize: 32 }]}
                >
                    <XAxis dataKey="name" tick={axisStyle} />
                    <YAxis tick={axisStyle} />
                </StyledBarChart>
            </Card>

            <Card title={`Chi tiết (${filteredData.chi_tiet?.length || 0} phòng)`} bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Table dataSource={filteredData.chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 1000 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: THIẾT BỊ
// ─────────────────────────────────────────────────────
const TabThietBi = ({ data, danhSachCoSo, danhSachKhuNha, danhSachPhong }) => {
    const [filterCoSo, setFilterCoSo] = useState(null);
    const [filterKhuNha, setFilterKhuNha] = useState(null);
    const [filterPhong, setFilterPhong] = useState(null);
    const { chi_tiet } = data;

    // Danh sách khu nhà theo cơ sở
    const khuNhaOptions = useMemo(() => {
        if (!filterCoSo) return danhSachKhuNha || [];
        return (danhSachKhuNha || []).filter(kn => kn.co_so_id === filterCoSo);
    }, [filterCoSo, danhSachKhuNha]);

    // Danh sách phòng theo khu nhà
    const phongOptions = useMemo(() => {
        if (!filterKhuNha) return danhSachPhong || [];
        return (danhSachPhong || []).filter(p => p.khu_nha_id === filterKhuNha);
    }, [filterKhuNha, danhSachPhong]);

    // Reset cascading
    const handleCoSoChange = (val) => {
        setFilterCoSo(val);
        setFilterKhuNha(null);
        setFilterPhong(null);
    };
    const handleKhuNhaChange = (val) => {
        setFilterKhuNha(val);
        setFilterPhong(null);
    };

    // Lọc dữ liệu
    const filteredData = useMemo(() => {
        let list = chi_tiet;
        if (filterCoSo) list = list.filter(r => r.co_so_id === filterCoSo);
        if (filterKhuNha) list = list.filter(r => r.khu_nha_id === filterKhuNha);
        if (filterPhong) list = list.filter(r => r.phong_id === filterPhong);

        // Tổng quan
        const tong_quan = {
            tong_thiet_bi: list.length,
            tong_gia_tri: list.reduce((s, r) => s + (parseFloat(r.gia_tri) || 0), 0),
            can_bao_duong: list.filter(r => r.qua_han_bao_duong).length,
            dang_hoat_dong: list.filter(r => r.trang_thai === 'tot').length,
        };

        // Biểu đồ theo loại
        const loaiMap = {};
        list.forEach(r => {
            const k = r.loai_thiet_bi || 'khac';
            if (!loaiMap[k]) loaiMap[k] = { soLuong: 0, tongGiaTri: 0 };
            loaiMap[k].soLuong++;
            loaiMap[k].tongGiaTri += parseFloat(r.gia_tri) || 0;
        });
        const loaiLabels = { van_phong: 'Văn phòng', day_hoc: 'Dạy học', thi_nghiem: 'Thí nghiệm', thuc_hanh: 'Thực hành' };
        const bieu_do_loai = Object.entries(loaiMap).map(([k, v]) => ({
            name: loaiLabels[k] || k, soLuong: v.soLuong, tongGiaTri: v.tongGiaTri,
        }));

        // Biểu đồ trạng thái
        const ttMap = {};
        list.forEach(r => {
            const k = r.trang_thai || 'khac';
            ttMap[k] = (ttMap[k] || 0) + 1;
        });
        const ttLabels = { tot: 'Tốt', can_sua_chua: 'Cần sửa chữa', hu_hong: 'Hư hỏng' };
        const bieu_do_trang_thai = Object.entries(ttMap).map(([k, v]) => ({
            name: ttLabels[k] || k, value: v,
        }));

        // Biểu đồ theo năm mua
        const namMap = {};
        list.forEach(r => {
            if (!r.nam_mua) return;
            if (!namMap[r.nam_mua]) namMap[r.nam_mua] = { soLuong: 0, tongGiaTri: 0 };
            namMap[r.nam_mua].soLuong++;
            namMap[r.nam_mua].tongGiaTri += parseFloat(r.gia_tri) || 0;
        });
        const bieu_do_nam_mua = Object.entries(namMap)
            .sort((a, b) => parseInt(a[0]) - parseInt(b[0]))
            .map(([k, v]) => ({ name: k, soLuong: v.soLuong, tongGiaTri: v.tongGiaTri }));

        // Biểu đồ theo hãng
        const hangMap = {};
        list.forEach(r => {
            if (!r.hang_san_xuat) return;
            hangMap[r.hang_san_xuat] = (hangMap[r.hang_san_xuat] || 0) + 1;
        });
        const bieu_do_hang = Object.entries(hangMap)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 10)
            .map(([k, v]) => ({ name: k, soLuong: v }));

        return { tong_quan, chi_tiet: list, bieu_do_loai, bieu_do_trang_thai, bieu_do_nam_mua, bieu_do_hang };
    }, [chi_tiet, filterCoSo, filterKhuNha, filterPhong]);

    const { tong_quan: tq, bieu_do_loai, bieu_do_trang_thai, bieu_do_nam_mua, bieu_do_hang } = filteredData;

    const columns = [
        { title: 'Mã TB', dataIndex: 'ma_thiet_bi', width: 100, fixed: 'left' },
        { title: 'Tên thiết bị', dataIndex: 'ten_thiet_bi', ellipsis: true },
        { title: 'Loại', dataIndex: 'loai_thiet_bi', render: loaiThietBiLabel },
        { title: 'Hãng', dataIndex: 'hang_san_xuat', ellipsis: true },
        { title: 'Model', dataIndex: 'model', ellipsis: true },
        { title: 'Phòng', dataIndex: 'ten_phong', ellipsis: true },
        { title: 'Toà nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
        { title: 'Năm SX', dataIndex: 'nam_san_xuat', align: 'center' },
        { title: 'Năm mua', dataIndex: 'nam_mua', align: 'center' },
        { title: 'Giá trị', dataIndex: 'gia_tri', align: 'right', render: v => formatCurrency(v) },
        {
            title: 'Bảo dưỡng', dataIndex: 'qua_han_bao_duong', align: 'center',
            render: (v) => v ? <Tooltip title="Quá hạn bảo dưỡng"><Tag color="red">Quá hạn</Tag></Tooltip> : null,
        },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            {/* Filter cascading */}
            <Card bordered={false} style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Space wrap>
                    <span style={{ fontWeight: 600 }}>Lọc theo:</span>
                    <Select
                        placeholder="Tất cả cơ sở"
                        allowClear
                        style={{ width: 200 }}
                        value={filterCoSo}
                        onChange={handleCoSoChange}
                        options={danhSachCoSo?.map(cs => ({ value: cs.id, label: cs.ten_co_so })) || []}
                    />
                    <Select
                        placeholder="Tất cả toà nhà"
                        allowClear
                        style={{ width: 200 }}
                        value={filterKhuNha}
                        onChange={handleKhuNhaChange}
                        options={khuNhaOptions.map(kn => ({ value: kn.id, label: kn.ten_khu_nha }))}
                    />
                    <Select
                        placeholder="Tất cả phòng"
                        allowClear
                        style={{ width: 200 }}
                        value={filterPhong}
                        onChange={setFilterPhong}
                        options={phongOptions.map(p => ({ value: p.id, label: p.ten_phong }))}
                    />
                </Space>
            </Card>

            <Row gutter={[16, 16]}>
                {[
                    { title: 'Tổng thiết bị', value: tq?.tong_thiet_bi,              icon: <ToolOutlined />,    color: P.blue },
                    { title: 'Tổng giá trị',  value: formatCurrency(tq?.tong_gia_tri), icon: <DollarOutlined />, color: P.purple },
                    { title: 'Cần bảo dưỡng', value: tq?.can_bao_duong,              icon: <WarningOutlined />, color: P.red },
                    { title: 'Đang hoạt động', value: tq?.dang_hoat_dong,             icon: <ToolOutlined />,    color: P.green },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <KpiCard title={item.title} value={item.value} icon={item.icon} color={item.color} />
                    </Col>
                ))}
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={14}>
                    <Card title="Số lượng & giá trị theo loại thiết bị" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <StyledBarChart
                            data={bieu_do_loai}
                            bars={[
                                { dataKey: 'soLuong',    name: 'Số lượng', fill: P.blue,   barSize: 28, yAxisId: 'left' },
                                { dataKey: 'tongGiaTri', name: 'Giá trị',  fill: P.purple, barSize: 28, yAxisId: 'right' },
                            ]}
                        >
                            <XAxis dataKey="name" tick={axisStyle} />
                            <YAxis yAxisId="left" tick={axisStyle} />
                            <YAxis yAxisId="right" orientation="right" tickFormatter={v => `${Math.round(v / 1e6)}M`} tick={axisStyle} />
                        </StyledBarChart>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái thiết bị" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <DonutChart data={bieu_do_trang_thai} />
                    </Card>
                </Col>
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={14}>
                    <Card title="Xu hướng mua sắm theo năm" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <ResponsiveContainer width="100%" height={260}>
                            <AreaChart data={bieu_do_nam_mua} margin={{ top: 8, right: 8, left: 0, bottom: 0 }}>
                                <defs>
                                    <linearGradient id="gradBlue" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%"  stopColor={P.blue}   stopOpacity={0.2} />
                                        <stop offset="95%" stopColor={P.blue}   stopOpacity={0} />
                                    </linearGradient>
                                    <linearGradient id="gradPurple" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%"  stopColor={P.purple} stopOpacity={0.2} />
                                        <stop offset="95%" stopColor={P.purple} stopOpacity={0} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid {...gridStyle} />
                                <XAxis dataKey="name" tick={axisStyle} />
                                <YAxis yAxisId="left"  tick={axisStyle} />
                                <YAxis yAxisId="right" orientation="right" tickFormatter={v => `${Math.round(v / 1e6)}M`} tick={axisStyle} />
                                <RTooltip {...tooltipStyle} formatter={(v, name) => name === 'Giá trị' ? formatCurrency(v) : v} />
                                <Legend iconType="circle" iconSize={8}
                                    formatter={(value) => <span style={{ color: '#555', fontSize: 12 }}>{value}</span>} />
                                <Area yAxisId="left"  type="monotone" dataKey="soLuong"    name="Số lượng"
                                    stroke={P.blue}   strokeWidth={2.5} fill="url(#gradBlue)"
                                    dot={{ r: 4, fill: P.blue,   strokeWidth: 0 }}
                                    activeDot={{ r: 6 }} />
                                <Area yAxisId="right" type="monotone" dataKey="tongGiaTri" name="Giá trị"
                                    stroke={P.purple} strokeWidth={2.5} fill="url(#gradPurple)"
                                    dot={{ r: 4, fill: P.purple, strokeWidth: 0 }}
                                    activeDot={{ r: 6 }} />
                            </AreaChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Top 10 hãng sản xuất" bordered={false}
                        style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                        <ResponsiveContainer width="100%" height={260}>
                            <BarChart data={bieu_do_hang} layout="vertical"
                                margin={{ top: 4, right: 16, left: 8, bottom: 4 }}>
                                <CartesianGrid strokeDasharray="0" stroke="#f0f0f0" strokeWidth={1} horizontal={false} />
                                <XAxis type="number" tick={axisStyle} />
                                <YAxis type="category" dataKey="name" width={85} tick={{ ...axisStyle, fontSize: 11 }} />
                                <RTooltip {...tooltipStyle} />
                                <Bar dataKey="soLuong" name="Số lượng" fill={P.teal} barSize={14} radius={[0, 4, 4, 0]}
                                    background={{ fill: '#f5f5f5', radius: [0, 4, 4, 0] }} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            <Card title={`Chi tiết (${filteredData.chi_tiet?.length || 0} thiết bị)`} bordered={false}
                style={{ borderRadius: 10, boxShadow: '0 1px 6px rgba(0,0,0,.06)' }}>
                <Table dataSource={filteredData.chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 1200 }} size="small"
                    rowClassName={(r) => r.qua_han_bao_duong ? 'ant-table-row-warning' : ''}
                />
            </Card>

            <style>{`.ant-table-row-warning > td { background-color: #fff7e6 !important; }`}</style>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// MAIN PAGE
// ─────────────────────────────────────────────────────
const ThongKeIndex = ({ thongKeCoSo, thongKeKhuNha, thongKePhong, thongKeThietBi, danhSachCoSo, danhSachKhuNha, danhSachPhong }) => {
    const [tab, setTab] = useState('co-so');

    const options = [
        { label: <Space><BankOutlined />Cơ sở</Space>,      value: 'co-so'    },
        { label: <Space><HomeOutlined />Toà nhà</Space>,    value: 'khu-nha'  },
        { label: <Space><AppstoreOutlined />Phòng</Space>,  value: 'phong'    },
        { label: <Space><ToolOutlined />Thiết bị</Space>,   value: 'thiet-bi' },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                    <Title level={2} style={{ margin: 0 }}>
                        <AreaChartOutlined style={{ marginRight: 8, color: P.blue }} />
                        Thống kê chi tiết
                    </Title>
                    <Segmented options={options} value={tab} onChange={setTab} size="large" />
                </div>

                {tab === 'co-so'    && <TabCoSo    data={thongKeCoSo}    />}
                {tab === 'khu-nha'  && <TabKhuNha  data={thongKeKhuNha} danhSachCoSo={danhSachCoSo} />}
                {tab === 'phong'    && <TabPhong   data={thongKePhong}  danhSachCoSo={danhSachCoSo} danhSachKhuNha={danhSachKhuNha} />}
                {tab === 'thiet-bi' && <TabThietBi data={thongKeThietBi} danhSachCoSo={danhSachCoSo} danhSachKhuNha={danhSachKhuNha} danhSachPhong={danhSachPhong} />}
            </Space>
        </MainLayout>
    );
};

export default ThongKeIndex;
