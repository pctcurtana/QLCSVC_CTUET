import React from 'react';
import MainLayout from './Layout/MainLayout';
import { Card, Row, Col, Typography, Space } from 'antd';
import {
    BankOutlined, HomeOutlined, AppstoreOutlined, ToolOutlined,
    DollarOutlined, AreaChartOutlined,
} from '@ant-design/icons';
import {
    PieChart, Pie, Cell,
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, LabelList,
} from 'recharts';

const { Title, Text } = Typography;

// ─── Màu sắc ──────────────────────────────────────────────────────────────
const C = ['#3b82f6', '#0f766e', '#f59e0b', '#8b5cf6', '#ef4444', '#22c55e'];

const tooltipStyle = {
    contentStyle: {
        borderRadius: 10,
        border: '1px solid #e5e7eb',
        boxShadow: '0 6px 18px rgba(15, 23, 42, .08)',
        padding: '8px 12px',
        fontSize: 12,
        background: '#fff',
    },
    cursor: { fill: 'rgba(59, 130, 246, .06)' },
};

// ─── Helpers ──────────────────────────────────────────────────────────────
const fmt   = (v) => new Intl.NumberFormat('vi-VN').format(v || 0);
const fmtCr = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(v || 0);
const loaiPhongLabel   = (l) => ({ phong_hoc: 'Phòng học', phong_thi_nghiem: 'Thí nghiệm', phong_thuc_hanh: 'Thực hành', phong_lam_viec: 'Làm việc', phong_chuc_nang: 'Chức năng' }[l] || l);
const loaiThietBiLabel = (l) => ({ van_phong: 'Văn phòng', day_hoc: 'Dạy học', thi_nghiem: 'Thí nghiệm', thuc_hanh: 'Thực hành' }[l] || l);
const trangThaiLabel   = (t) => ({ active: 'Hoạt động', maintenance: 'Bảo trì', inactive: 'Không HĐ' }[t] || t);

// ─── KPI Card ─────────────────────────────────────────────────────────────
const KpiCard = ({ title, value, icon, color }) => (
    <Card bordered={false} style={{ borderRadius: 14, boxShadow: '0 2px 12px rgba(0,0,0,.08)', overflow: 'hidden' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
            <div style={{
                width: 52, height: 52, borderRadius: 14, flexShrink: 0,
                background: `${color}18`,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: 22, color,
            }}>
                {icon}
            </div>
            <div>
                <div style={{ fontSize: 12, color: '#999', marginBottom: 2 }}>{title}</div>
                <div style={{ fontSize: 22, fontWeight: 800, color: '#111', lineHeight: 1.2 }}>{value}</div>
            </div>
        </div>
    </Card>
);

// ─── Chart Card ───────────────────────────────────────────────────────────
const ChartCard = ({ title, children }) => (
    <Card
        title={<span style={{ fontWeight: 700, fontSize: 14 }}>{title}</span>}
        bordered={false}
        style={{ borderRadius: 14, boxShadow: '0 2px 12px rgba(0,0,0,.08)', height: '100%' }}
        bodyStyle={{ paddingTop: 8 }}
    >
        {children}
    </Card>
);

// ─── Donut Chart ──────────────────────────────────────────────────────────
const DonutChart = ({ data }) => {
    const total = data.reduce((s, d) => s + (d.value || 0), 0);
    return (
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
            <div style={{ position: 'relative', width: '100%' }}>
                <ResponsiveContainer width="100%" height={200}>
                    <PieChart>
                        <Pie data={data} cx="50%" cy="50%"
                            innerRadius={56} outerRadius={84}
                            paddingAngle={4} dataKey="value" startAngle={90} endAngle={-270}>
                            {data.map((_, i) => <Cell key={i} fill={C[i % C.length]} stroke="none" />)}
                        </Pie>
                        <Tooltip {...tooltipStyle}
                            formatter={(v) => [`${fmt(v)} phòng (${total ? ((v / total) * 100).toFixed(1) : 0}%)`]} />
                        <text x="50%" y="46%" textAnchor="middle" dominantBaseline="middle"
                            fontSize={28} fontWeight={800} fill="#111">{fmt(total)}</text>
                        <text x="50%" y="57%" textAnchor="middle" dominantBaseline="middle"
                            fontSize={11} fill="#bbb">Tổng phòng</text>
                    </PieChart>
                </ResponsiveContainer>
            </div>
            <div style={{ width: '100%', display: 'flex', flexDirection: 'column', gap: 8, marginTop: 4 }}>
                {data.map((item, i) => {
                    const pct = total ? ((item.value / total) * 100).toFixed(0) : 0;
                    return (
                        <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                            <div style={{ width: 10, height: 10, borderRadius: '50%', background: C[i % C.length], flexShrink: 0 }} />
                            <div style={{ flex: 1, fontSize: 13, color: '#555' }}>{item.name}</div>
                            <Text strong style={{ fontSize: 14 }}>{fmt(item.value)}</Text>
                            <Text type="secondary" style={{ fontSize: 12, width: 36, textAlign: 'right' }}>{pct}%</Text>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

// ─── Main ─────────────────────────────────────────────────────────────────
const Dashboard = ({ statistics, thongKeLoaiPhong, thongKeLoaiThietBi, thongKeCoSo, thongKeTrangThaiPhong }) => {
    const loaiPhongData = thongKeLoaiPhong.map((d, i) => ({
        name: loaiPhongLabel(d.loai_phong), soLuong: d.so_luong, fill: C[i % C.length],
    })).sort((a, b) => (b.soLuong || 0) - (a.soLuong || 0));
    const loaiThietBiData    = thongKeLoaiThietBi
        .map(d => ({ name: loaiThietBiLabel(d.loai_thiet_bi), soLuong: d.so_luong }))
        .sort((a, b) => (b.soLuong || 0) - (a.soLuong || 0));
    const coSoData           = thongKeCoSo.map(d        => ({ name: d.ten_co_so, soKhuNha: d.so_khu_nha }));
    const trangThaiPhongData = thongKeTrangThaiPhong.map(d => ({ name: trangThaiLabel(d.trang_thai), value: d.so_luong }));

    const kpis = [
        { title: 'Tổng số cơ sở',          value: fmt(statistics.tong_co_so),               icon: <BankOutlined />,      color: '#4096ff' },
        { title: 'Tổng số khu nhà',         value: fmt(statistics.tong_khu_nha),              icon: <HomeOutlined />,      color: '#52c41a' },
        { title: 'Tổng số phòng',           value: fmt(statistics.tong_phong),                icon: <AppstoreOutlined />,  color: '#13c2c2' },
        { title: 'Tổng số thiết bị',        value: fmt(statistics.tong_thiet_bi),              icon: <ToolOutlined />,      color: '#fa8c16' },
        { title: 'Tổng giá trị thiết bị',  value: fmtCr(statistics.tong_gia_tri_thiet_bi),    icon: <DollarOutlined />,    color: '#7c3aed' },
        { title: 'Diện tích đất (m²)',      value: fmt(statistics.dien_tich_dat),              icon: <AreaChartOutlined />, color: '#13c2c2' },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>

                <Title level={2} style={{ margin: 0 }}>
                    <AreaChartOutlined style={{ marginRight: 10, color: '#4096ff' }} />
                    Tổng quan QLCSVC
                </Title>

                {/* ── KPI ── */}
                <Row gutter={[16, 16]}>
                    {kpis.map((k, i) => (
                        <Col xs={24} sm={12} lg={8} key={i}>
                            <KpiCard {...k} />
                        </Col>
                    ))}
                </Row>

                {/* ── Hàng 1 ── */}
                <Row gutter={[16, 16]} align="stretch">

                    {/* BarChart: loại phòng — so sánh theo nhóm */}
                    <Col xs={24} lg={14}>
                        <ChartCard title="Phân bố theo loại phòng">
                            <ResponsiveContainer width="100%" height={280}>
                                <BarChart data={loaiPhongData} margin={{ top: 16, right: 12, left: -10, bottom: 6 }}>
                                    <CartesianGrid strokeDasharray="2 4" stroke="#eef2f7" vertical={false} />
                                    <XAxis dataKey="name" tick={{ fill: '#64748b', fontSize: 12 }} axisLine={false} tickLine={false} />
                                    <YAxis allowDecimals={false} tick={{ fill: '#94a3b8', fontSize: 11 }} axisLine={false} tickLine={false} />
                                    <Tooltip {...tooltipStyle} formatter={(v) => [`${fmt(v)} phòng`, 'Số lượng']} />
                                    <Bar
                                        dataKey="soLuong"
                                        name="Số phòng"
                                        fill="#3b82f6"
                                        radius={[10, 10, 0, 0]}
                                        maxBarSize={54}
                                        background={{ fill: '#eef2ff', radius: 10 }}
                                        isAnimationActive
                                        animationDuration={950}
                                        animationBegin={120}
                                        animationEasing="ease-out"
                                        activeBar={{ fill: '#2563eb' }}
                                    >
                                        <LabelList dataKey="soLuong" position="top" style={{ fontWeight: 700, fill: '#334155', fontSize: 12 }} />
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </ChartCard>
                    </Col>

                    {/* Donut: trạng thái */}
                    <Col xs={24} lg={10}>
                        <ChartCard title="Trạng thái phòng">
                            <DonutChart data={trangThaiPhongData} />
                        </ChartCard>
                    </Col>

                </Row>

                {/* ── Hàng 2 ── */}
                <Row gutter={[16, 16]} align="stretch">

                    {/* Horizontal Bar: thiết bị theo loại */}
                    <Col xs={24} lg={12}>
                        <ChartCard title="Thiết bị theo loại">
                            <ResponsiveContainer width="100%" height={280}>
                                <BarChart
                                    data={loaiThietBiData}
                                    layout="vertical"
                                    margin={{ top: 8, right: 30, left: 24, bottom: 8 }}
                                >
                                    <CartesianGrid strokeDasharray="2 4" stroke="#eef2f7" horizontal={false} />
                                    <XAxis type="number" allowDecimals={false} tick={{ fill: '#94a3b8', fontSize: 11 }} axisLine={false} tickLine={false} />
                                    <YAxis type="category" dataKey="name" tick={{ fill: '#64748b', fontSize: 12 }} axisLine={false} tickLine={false} width={90} />
                                    <Tooltip {...tooltipStyle} formatter={(v) => [`${fmt(v)} thiết bị`, 'Số lượng']} />
                                    <Bar
                                        dataKey="soLuong"
                                        name="Số thiết bị"
                                        fill="#0f766e"
                                        radius={[0, 10, 10, 0]}
                                        maxBarSize={24}
                                        background={{ fill: '#ecfeff', radius: 10 }}
                                        isAnimationActive
                                        animationDuration={1000}
                                        animationBegin={220}
                                        animationEasing="ease-out"
                                        activeBar={{ fill: '#0d9488' }}
                                    >
                                        <LabelList dataKey="soLuong" position="right" style={{ fontWeight: 700, fill: '#334155', fontSize: 12 }} />
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </ChartCard>
                    </Col>

                    {/* BarChart: khu nhà theo cơ sở — bar dọc gradient */}
                    <Col xs={24} lg={12}>
                        <ChartCard title="Khu nhà theo cơ sở">
                            <ResponsiveContainer width="100%" height={280}>
                                <BarChart data={coSoData}
                                    margin={{ top: 32, right: 12, left: -16, bottom: 4 }}>
                                    <defs>
                                        {coSoData.map((_, i) => (
                                            <linearGradient key={i} id={`g${i}`} x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%"   stopColor={C[i % C.length]} stopOpacity={1}   />
                                                <stop offset="100%" stopColor={C[i % C.length]} stopOpacity={0.25} />
                                            </linearGradient>
                                        ))}
                                    </defs>
                                    <CartesianGrid strokeDasharray="0" stroke="#f0f0f0" vertical={false} />
                                    <XAxis dataKey="name"
                                        tick={{ fill: '#888', fontSize: 12 }} axisLine={false} tickLine={false} />
                                    <YAxis hide />
                                    <Tooltip {...tooltipStyle} formatter={(v) => [`${fmt(v)} khu nhà`]} />
                                    <Bar dataKey="soKhuNha" name="Số khu nhà" barSize={52} radius={[10, 10, 0, 0]}>
                                        {coSoData.map((_, i) => (
                                            <Cell key={i} fill={`url(#g${i})`} />
                                        ))}
                                        <LabelList dataKey="soKhuNha" position="top"
                                            style={{ fontWeight: 800, fontSize: 18, fill: '#333' }} />
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </ChartCard>
                    </Col>

                </Row>

                {/* ── Diện tích ── */}
                <Card bordered={false} style={{ borderRadius: 14, boxShadow: '0 2px 12px rgba(0,0,0,.08)' }}>
                    <Row>
                        {[
                            { label: 'Tổng diện tích đất', value: fmt(statistics.dien_tich_dat),                                                             unit: 'm²',  color: '#4096ff' },
                            { label: 'Vị trí khuôn viên (TB)', value: statistics.vi_tri_khuon_vien_tb ? Number(statistics.vi_tri_khuon_vien_tb).toFixed(2) : '—', unit: '',     color: '#13c2c2' },
                            { label: 'Diện tích quy đổi',   value: fmt(statistics.dien_tich_quy_doi),                                                        unit: 'm²',  color: '#52c41a' },
                        ].map((item, i) => (
                            <Col xs={24} sm={8} key={i}
                                style={{ padding: '16px 24px', borderRight: i < 2 ? '1px solid #f0f0f0' : 'none', textAlign: 'center' }}>
                                <div style={{ fontSize: 13, color: '#999', marginBottom: 6 }}>{item.label}</div>
                                <div style={{ fontSize: 30, fontWeight: 800, color: item.color, lineHeight: 1 }}>
                                    {item.value}
                                    {item.unit && <span style={{ fontSize: 14, fontWeight: 400, marginLeft: 6, color: '#bbb' }}>{item.unit}</span>}
                                </div>
                            </Col>
                        ))}
                    </Row>
                </Card>

            </Space>
        </MainLayout>
    );
};

export default Dashboard;
