import React from 'react';
import { useState, useEffect } from 'react';
import MainLayout from './Layout/MainLayout';
import { Card, Row, Col, Typography, Space } from 'antd';
import {
    BankOutlined, HomeOutlined, AppstoreOutlined, ToolOutlined,
    DollarOutlined, AreaChartOutlined,
} from '@ant-design/icons';
import {
    PieChart, Pie, Cell, RadialBar, RadialBarChart, Legend,
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, LabelList,
} from 'recharts';

const { Title, Text } = Typography;


// ─── Màu sắc ──────────────────────────────────────────────────────────────
const C = ['#3b82f6', '#0f766e', '#f59e0b', '#8b5cf6', '#ef4444', '#22c55e'];
const DONUT_COLORS = [
    {
        solid: "#10b981",
        grad: ["#34d399", "#10b981"],
    },
    {
        solid: "#f59e0b",
        grad: ["#fbbf24", "#f59e0b"],
    },
    {
        solid: "#ef4444",
        grad: ["#fb7185", "#ef4444"],
    },
];

const tooltipStyle = {
    contentStyle: {
        background: 'rgba(255,255,255,0.75)',
        backdropFilter: 'blur(20px)',
        WebkitBackdropFilter: 'blur(20px)',
        border: '1px solid rgba(255,255,255,0.35)',
        borderRadius: 16,
        boxShadow: `
            0 10px 30px rgba(15,23,42,.08),
            0 2px 8px rgba(15,23,42,.04)
        `,
        padding: '10px 14px',
        fontSize: 12,
        fontWeight: 600,
        color: '#0f172a',
        fontFamily: "'Plus Jakarta Sans', sans-serif",
    },

    labelStyle: {
        color: '#64748b',
        fontSize: 11,
        fontWeight: 700,
        marginBottom: 4,
        textTransform: 'uppercase',
        letterSpacing: '.06em',
    },

    itemStyle: {
        color: '#0f172a',
        fontSize: 12,
        fontWeight: 700,
    },

    cursor: false,
};

const useCountUp = (target, duration = 1200) => {
    const [value, setValue] = useState(0);

    useEffect(() => {
        let raf;
        let start;

        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 4);
            setValue(Math.round(target * eased));
            if (progress < 1) raf = requestAnimationFrame(animate);
        };

        raf = requestAnimationFrame(animate);
        return () =>
            cancelAnimationFrame(raf);
    }, [target, duration]);

    return value;
};

// ─── Helpers ──────────────────────────────────────────────────────────────
const fmt = (v) => new Intl.NumberFormat('vi-VN').format(v || 0);
const fmtCr = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(v || 0);
const loaiPhongLabel = (l) => ({ phong_hoc: 'Phòng học', phong_thi_nghiem: 'Thí nghiệm', phong_thuc_hanh: 'Thực hành', phong_lam_viec: 'Làm việc', phong_chuc_nang: 'Chức năng' }[l] || l);
const loaiThietBiLabel = (l) => ({ van_phong: 'Văn phòng', day_hoc: 'Dạy học', thi_nghiem: 'Thí nghiệm', thuc_hanh: 'Thực hành' }[l] || l);
const trangThaiLabel = (t) => ({ active: 'Hoạt động', maintenance: 'Bảo trì', inactive: 'Không HĐ' }[t] || t);

// ─── KPI Card ─────────────────────────────────────────────────────────────
const KpiCard = ({ title, value, icon, color }) => {
    return (
        <Card
            bordered={false}
            className="
                relative
                overflow-hidden
                h-full
                rounded-[18px]
                border
                border-white/35
                bg-white/55
                backdrop-blur-xl
                shadow-[0_6px_20px_rgba(15,23,42,.04)]
            "
            styles={{
                body: {
                    padding: 16,
                },
            }}
        >
            {/* Glow */}
            <div
                className=" absolute -top-6 -right-6 h-20 w-20 rounded-full blur-3xl"
                style={{
                    background: `${color}15`,
                }}
            />

            {/* Header */}
            <div className="mb-3 flex items-center justify-between">
                <div className="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-500">
                    {title}
                </div>

                <div
                    className="flex h-9 w-9 items-center justify-center rounded-xl"
                    style={{
                        background: `${color}15`,
                        color,
                    }}
                >
                    {icon}
                </div>
            </div>

            {/* Value */}
            <div
                className="mb-2 text-[24px] font-extrabold leading-none tracking-[-0.05em] text-[#0f172a]"
                style={{
                    fontFamily: "'Plus Jakarta Sans', sans-serif",
                }}
            >
                {value}
            </div>

            {/* Footer */}
            <div className="flex items-center gap-1.5">
                <div
                    className="h-1.5 w-1.5 rounded-full"
                    style={{
                        background: color,
                    }}
                />

                <span className="text-[10px] font-semibold uppercase tracking-[0.05em] text-slate-400">
                    Dữ liệu mới nhất
                </span>
            </div>
        </Card>
    );
};

// ─── Chart Card ───────────────────────────────────────────────────────────
const ChartCard = ({ title, children }) => (
    <Card
        bordered={false}
        className="
            overflow-hidden
            rounded-3xl
            border
            border-white/25
            bg-white/55
            backdrop-blur-xl
            shadow-[0_8px_30px_rgba(15,23,42,.05)]"
        styles={{
            body: {
                padding: 20,
            },
        }}
    >
        <div className="mb-5 flex items-center justify-between">
            <div>
                <h3 className="text-[15px] font-semibold tracking-[-0.03em] text-slate-900">
                    {title}
                </h3>
            </div>
        </div>

        {children}
    </Card>
);

// ─── Donut Chart ──────────────────────────────────────────────────────────
const DonutChart = ({ data }) => {
    const total = data.reduce(
        (s, d) => s + (d.value || 0),
        0
    );

    const displayTotal = useCountUp(total);

    const [activeIndex, setActiveIndex] =
        useState(null);

    return (
        <div className="flex items-center gap-1">
            <div className="relative w-[220px] shrink-0">
                <ResponsiveContainer width="100%" height={300}>
                    <PieChart>
                        <defs>
                            {DONUT_COLORS.map(
                                (c, i) => (
                                    <linearGradient key={i} id={`donutGradient${i}`} x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stopColor={c.grad[0]} />
                                        <stop offset="100%" stopColor={c.grad[1]} />
                                    </linearGradient>
                                )
                            )}
                        </defs>
                        <Pie
                            data={data}
                            cx="50%"
                            cy="50%"
                            innerRadius={60}
                            outerRadius={82}
                            paddingAngle={5}
                            dataKey="value"
                            startAngle={90}
                            endAngle={-270}
                            animationDuration={1800}
                            animationBegin={200}
                            onMouseEnter={(_, index) => setActiveIndex(index)}
                            onMouseLeave={() => setActiveIndex(null)}
                        >
                            {data.map((item, i) => {
                                const active = activeIndex === i;
                                return (
                                    <Cell
                                        key={i}
                                        fill={`url(#donutGradient${i})`}
                                        stroke="rgba(255,255,255,.8)"
                                        strokeWidth={2}
                                        style={{
                                            filter: active ? `drop-shadow(0 0 10px ${DONUT_COLORS[i].solid}55)` : "none",
                                            transition: "all .25s ease",
                                        }}
                                    />
                                );
                            })}
                        </Pie>

                        <Tooltip
                            {...tooltipStyle}
                            offset={100}
                            formatter={(v) => [
                                `${fmt(v)} phòng (${total ? ((v / total) * 100).toFixed(1) : 0}%)`
                            ]}
                        />
                    </PieChart>
                </ResponsiveContainer>

                {/* Center */}
                <div
                    className="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div className="flex flex-col items-center justify-center w-[88px] h-[88px] rounded-full border border-white/30 bg-white/40 backdrop-blur-xl">
                        <div
                            className="text-[24px] font-black text-slate-900 leading-none"
                            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                            {displayTotal}
                        </div>
                        <div
                            className="mt-1 text-[10px] uppercase tracking-[0.12em] text-slate-400 font-bold">
                            Tổng phòng
                        </div>
                    </div>
                </div>
            </div>

            {/* Legend */}
            <div className="flex-1 flex flex-col gap-2">
                {data.map((item, i) => {
                    const pct = total ? Math.round((item.value / total) * 100) : 0;
                    return (
                        <div
                            key={i}
                            className="rounded-xl px-3 py-2 transition-all duration-300 hover:bg-[#244380]/[0.06] hover:backdrop-blur-xl hover:shadow-[0_8px_20px_rgba(36,67,128,.08)] hover:border hover:border-[#244380]/10 hover:-translate-y-[1px]"
                            onMouseEnter={() => setActiveIndex(i)}
                            onMouseLeave={() => setActiveIndex(null)}>
                            <div className="flex items-center justify-between mb-1.5">
                                <div className="flex items-center gap-2">
                                    <div className="w-2.5 h-2.5 rounded-full" style={{
                                        background: DONUT_COLORS[i].solid,
                                    }} />
                                    <span className="text-[12px] font-semibold text-slate-700">
                                        {item.name}
                                    </span>
                                </div>
                                <div className="text-[13px] font-bold text-slate-900">
                                    {fmt(item.value)}
                                </div>
                            </div>
                            <div className="h-1 rounded-full bg-slate-100 overflow-hidden">
                                <div
                                    className="h-full rounded-full transition-all duration-700"
                                    style={{
                                        width: `${pct}%`,
                                        background: `linear-gradient(90deg, ${DONUT_COLORS[i].grad[0]}, ${DONUT_COLORS[i].grad[1]})`,
                                    }}
                                />
                            </div>
                            <div className="mt-1 text-right text-[11px] font-semibold text-slate-400">
                                {pct}%
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

// ─── Main ─────────────────────────────────────────────────────────────────
const Dashboard = ({ statistics, thongKeLoaiPhong, thongKeLoaiThietBi, thongKeCoSo, thongKeTrangThaiPhong }) => {
    const loaiPhongData = thongKeLoaiPhong.map((d) => ({
        name: loaiPhongLabel(d.loai_phong),
        soLuong: d.so_luong,
    }));
    const loaiThietBiData = thongKeLoaiThietBi
        .map(d => ({ name: loaiThietBiLabel(d.loai_thiet_bi), soLuong: d.so_luong }))
        .sort((a, b) => (b.soLuong || 0) - (a.soLuong || 0));
    const coSoData = thongKeCoSo.map(d => ({ name: d.ten_co_so, soKhuNha: d.so_khu_nha }));
    const trangThaiPhongData = thongKeTrangThaiPhong.map(d => ({ name: trangThaiLabel(d.trang_thai), value: d.so_luong }));
    const [activeBar, setActiveBar] = useState(null);
    const [activeDeviceBar, setActiveDeviceBar] = useState(null);

    const kpis = [
        { title: 'Tổng số cơ sở', value: fmt(statistics.tong_co_so), icon: <BankOutlined />, color: '#4096ff' },
        { title: 'Tổng số toà nhà', value: fmt(statistics.tong_khu_nha), icon: <HomeOutlined />, color: '#52c41a' },
        { title: 'Tổng số phòng', value: fmt(statistics.tong_phong), icon: <AppstoreOutlined />, color: '#13c2c2' },
        { title: 'Tổng số thiết bị', value: fmt(statistics.tong_thiet_bi), icon: <ToolOutlined />, color: '#fa8c16' },
        { title: 'Tổng giá trị thiết bị', value: fmtCr(statistics.tong_gia_tri_thiet_bi), icon: <DollarOutlined />, color: '#7c3aed' },
        { title: 'Diện tích đất (m²)', value: fmt(statistics.dien_tich_dat), icon: <AreaChartOutlined />, color: '#13c2c2' },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>

                <Title level={2} style={{ margin: 0 }}>
                    <AreaChartOutlined style={{ marginRight: 10, color: '#4096ff' }} />
                    Tổng quan cơ sở vật chất
                </Title>
                {/* ── KPI ── */}
                <Row gutter={[16, 16]}>
                    {kpis.map((k, i) => (
                        <Col xs={24} sm={12} lg={4} key={i}>
                            <KpiCard {...k} />
                        </Col>
                    ))}
                </Row>
                {/* ── Hàng 1 ── */}
                <Row gutter={[16, 16]} align="stretch">
                    {/* BarChart: loại phòng — so sánh theo nhóm */}
                    <Col xs={24} lg={14}>
                        <ChartCard title="Phân bố theo loại phòng">
                            <ResponsiveContainer width="100%" height={260}>
                                <BarChart data={loaiPhongData} margin={{ top: 16, right: 12, left: -10, bottom: 6 }}>
                                    <defs>
                                        <linearGradient id="roomGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stopColor="#6ea8ff" />
                                            <stop offset="55%" stopColor="#4f8cff" />
                                            <stop offset="100%" stopColor="#244380" />
                                        </linearGradient>
                                        <filter id="roomGlow">
                                            <feDropShadow dx="0" dy="6" stdDeviation="8" floodColor="#4f8cff" floodOpacity="0.15" />
                                        </filter>
                                    </defs>
                                    <CartesianGrid vertical={false} stroke="rgba(148,163,184,.20)" />
                                    <XAxis
                                        dataKey="name"
                                        axisLine={false}
                                        tickLine={false}
                                        tick={{ fill: "#64748b", fontSize: 12, fontWeight: 600 }}

                                    />
                                    <YAxis
                                        width={35}
                                        axisLine={false}
                                        tickLine={false}
                                        tick={{ fill: "#94a3b8", fontSize: 11, fontWeight: 500 }}

                                    />
                                    <Tooltip {...tooltipStyle} formatter={(v) => [`${fmt(v)} phòng`, "Số lượng"]} />
                                    <Bar
                                        dataKey="soLuong"
                                        radius={[8, 8, 2, 2]}
                                        maxBarSize={38}
                                        filter="url(#roomGlow)"
                                        animationDuration={1200}
                                        animationBegin={100}
                                        animationEasing="ease-out"
                                    >
                                        {loaiPhongData.map((entry, index) => (
                                            <Cell
                                                key={index}
                                                fill={activeBar === null ? "#7EA6FF" : activeBar === index ? "#4F8CFF" : "#DCE7FF"}
                                                style={{
                                                    filter: activeBar === index ? "drop-shadow(0 8px 18px rgba(79,140,255,.22))" : "none",
                                                    opacity: activeBar === null ? 1 : activeBar === index ? 1 : 0.55,
                                                    transition: "fill .25s, fill-opacity .25s",
                                                }}
                                                onMouseEnter={() => setActiveBar(index)}
                                                onMouseLeave={() => setActiveBar(null)}
                                            />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>

                            {/* Stats Row */}
                            <div className="pt-2 border-t border-slate-200/60 grid grid-cols-5">
                                {loaiPhongData.map((item, index) => {
                                    const total = loaiPhongData.reduce((s, i) => s + i.soLuong, 0);
                                    return (
                                        <div
                                            key={item.name}
                                            onMouseEnter={() => setActiveBar(index)}
                                            onMouseLeave={() => setActiveBar(null)}
                                            className={`relative px-4 py-1 cursor-pointer transition-all duration-500 ease-in-out
                                                ${activeBar === index ? "bg-slate-300" : ""}
                                                ${index !== loaiPhongData.length - 1 ? " border-r border-slate-200" : ""}`}
                                            style={{ opacity: activeBar === null ? 1 : activeBar === index ? 1 : 0.35, }}>
                                            <div className="flex items-center justify-center gap-2">
                                                <span
                                                    className={`text-base font-black ${activeBar === index ? "text-[#244380]" : "text-slate-800"}`}>
                                                    {item.soLuong}
                                                </span>
                                                <span className="h-1 w-1 rounded-full bg-slate-300" />
                                                <span
                                                    className={`text-[11px] uppercase tracking-[0.1em] font-semibold ${activeBar === index ? "text-[#244380]" : "text-slate-400"}`}>
                                                    {item.name}
                                                </span>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
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
                        <ChartCard title="Toà nhà theo cơ sở">
                            <ResponsiveContainer width="100%" height={240}>
                                <RadialBarChart
                                    cx="50%"
                                    cy="50%"
                                    innerRadius="25%"
                                    outerRadius="85%"
                                    barSize={16}
                                    data={coSoData.map((item, index) => ({
                                        ...item,
                                        fill: [
                                            "#244380",
                                            "#10B981",
                                            "#F59E0B",
                                            "#8B5CF6",
                                            "#EF4444",
                                        ][index % 5],
                                    }))}
                                >
                                    <RadialBar background clockWise dataKey="soKhuNha" cornerRadius={12} label={false} />
                                    <Tooltip
                                        {...tooltipStyle}
                                        formatter={(value) => [`${fmt(value)} toà nhà`, "Số lượng"]}
                                        labelFormatter={(_, payload) => payload?.[0]?.payload?.name || ""}
                                    />
                                </RadialBarChart>
                            </ResponsiveContainer>
                            <div className="space-y-2">
                                {coSoData.map((item, index) => (
                                    <div
                                        key={item.name}
                                        className="flex items-center justify-between text-sm"
                                    >
                                        <div className="flex items-center gap-2">
                                            <div
                                                className="w-3 h-3 rounded-full"
                                                style={{
                                                    background: [
                                                        "#244380",
                                                        "#10B981",
                                                        "#F59E0B",
                                                        "#8B5CF6",
                                                        "#EF4444",
                                                    ][index % 5],
                                                }}
                                            />

                                            <span className="font-medium text-slate-700">
                                                {item.name}
                                            </span>
                                        </div>

                                        <span className="font-bold text-slate-900">
                                            {String(item.soKhuNha).padStart(2, "0")}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </ChartCard>
                    </Col>
                    {/* BarChart: khu nhà theo cơ sở — bar dọc gradient */}
                    <Col xs={24} lg={12}>
                        <ChartCard title="Thiết bị theo loại">
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart
                                    data={loaiThietBiData}
                                    layout="vertical"
                                    margin={{ top: 12, right: 20, left: 12, bottom: 12, }}
                                >
                                    <defs>
                                        <filter id="deviceGlow">
                                            <feDropShadow dx="0" dy="4" stdDeviation="6" floodColor="#4f8cff" floodOpacity="0.18" />
                                        </filter>
                                    </defs>
                                    <CartesianGrid horizontal={false} stroke="rgba(148,163,184,.15)" />
                                    <XAxis type="number" allowDecimals={false} axisLine={false} tickLine={false}
                                        tick={{ fill: "#94a3b8", fontSize: 11, fontWeight: 500, }}
                                    />
                                    <YAxis type="category" dataKey="name" width={70} axisLine={false} tickLine={false}
                                        tick={({ x, y, payload, index }) => (
                                            <text
                                                x={x} y={y} dy={4} textAnchor="end"
                                                fill={activeDeviceBar === index ? "#10B981" : "#64748b"}
                                                fontSize="12"
                                                fontWeight={activeDeviceBar === index ? 800 : 600}
                                            >
                                                {payload.value}
                                            </text>
                                        )}
                                    />
                                    <Tooltip {...tooltipStyle} cursor={false} formatter={(v) => [`${fmt(v)} thiết bị`, "Số lượng"]} />
                                    <Bar
                                        dataKey="soLuong"
                                        radius={[2, 8, 8, 2]}
                                        maxBarSize={26}
                                        isAnimationActive={false}
                                    >
                                        <LabelList
                                            content={({ x, y, width, height, value }) => (
                                                <text
                                                    x={Number(x) + Number(width) + 12}
                                                    y={Number(y) + Number(height) / 2}
                                                    dominantBaseline="middle"
                                                    fill="#64748b"
                                                    fontSize="11"
                                                    fontWeight="700"
                                                >
                                                    {value}
                                                </text>
                                            )}
                                        />
                                        {loaiThietBiData.map((item, index) => (
                                            <Cell
                                                key={index}
                                                fill={activeDeviceBar === null ? "#7DD3C7" : activeDeviceBar === index ? "#34D399" : "#D1FAE5"}
                                                style={{
                                                    opacity: activeDeviceBar === null ? 1 : activeDeviceBar === index ? 1 : 0.35,
                                                    transition: "all .3s ease",
                                                }}
                                                onMouseEnter={() => setActiveDeviceBar(index)}
                                                onMouseLeave={() => setActiveDeviceBar(null)}
                                            />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                            <div className="h-4 text-center">
                                {activeDeviceBar !== null && (
                                    <span className="text-xs font-semibold uppercase tracking-[0.12em] text-[#244380]">
                                        {loaiThietBiData[activeDeviceBar].name}
                                    </span>
                                )}
                            </div>
                        </ChartCard>
                    </Col>
                </Row>
            </Space>
        </MainLayout>
    );
};

export default Dashboard;
