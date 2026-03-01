import React, { useState } from 'react';
import MainLayout from '../Layout/MainLayout';
import {
    Card, Row, Col, Statistic, Typography, Space, Table, Tag, Segmented, Tooltip,
} from 'antd';
import {
    BankOutlined, HomeOutlined, AppstoreOutlined, ToolOutlined,
    DollarOutlined, AreaChartOutlined, WarningOutlined,
} from '@ant-design/icons';
import {
    BarChart, Bar, PieChart, Pie, Cell,
    XAxis, YAxis, CartesianGrid, Tooltip as RTooltip,
    Legend, ResponsiveContainer, LineChart, Line,
} from 'recharts';

const { Title, Text } = Typography;

const COLORS = ['#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1', '#13c2c2', '#eb2f96', '#fa8c16'];

const formatCurrency = (v) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);

const formatNumber = (v) => new Intl.NumberFormat('vi-VN').format(v || 0);

const trangThaiTag = (tt) => {
    const map = {
        active:       { color: 'green',  label: 'Hoạt động' },
        maintenance:  { color: 'orange', label: 'Bảo trì' },
        inactive:     { color: 'default',label: 'Không HĐ' },
        broken:       { color: 'red',    label: 'Hỏng' },
        tot:          { color: 'green',  label: 'Tốt' },
        can_sua_chua: { color: 'orange', label: 'Cần sửa chữa' },
        hu_hong:      { color: 'red',    label: 'Hư hỏng' },
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

// ─── Shared chart style ───────────────────────────────
const tooltipStyle = {
    contentStyle: { borderRadius: 8, border: '1px solid #e8e8e8', boxShadow: '0 2px 8px rgba(0,0,0,.1)' },
    itemStyle: { color: '#333', fontWeight: 600 },
    labelStyle: { color: '#666', fontSize: 13 },
    cursor: { fill: 'rgba(0,0,0,.04)' },
};

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
        { title: 'Khu nhà', dataIndex: 'so_khu_nha', align: 'center' },
        { title: 'Phòng', dataIndex: 'so_phong', align: 'center' },
        { title: 'Thiết bị', dataIndex: 'so_thiet_bi', align: 'center' },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            {/* KPIs */}
            <Row gutter={16}>
                {[
                    { title: 'Tổng số cơ sở', value: tq?.tong_co_so, icon: <BankOutlined />, color: '#1890ff' },
                    { title: 'Tổng DT đất', value: `${formatNumber(tq?.tong_dien_tich_dat)} m²`, icon: <AreaChartOutlined />, color: '#52c41a' },
                    { title: 'Tổng DT quy đổi', value: `${formatNumber(Math.round(tq?.tong_dien_tich_quy_doi))} m²`, icon: <AreaChartOutlined />, color: '#13c2c2' },
                    { title: 'Đang hoạt động', value: tq?.co_so_hoat_dong, icon: <BankOutlined />, color: '#52c41a' },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <Card>
                            <Statistic title={item.title} value={item.value} prefix={item.icon}
                                valueStyle={{ color: item.color }} />
                        </Card>
                    </Col>
                ))}
            </Row>

            {/* Charts row 1 */}
            <Row gutter={16}>
                <Col xs={24} lg={14}>
                    <Card title="Diện tích đất & quy đổi theo cơ sở (m²)">
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={bieu_do_dien_tich} margin={{ bottom: 40 }}>
                                <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                <XAxis dataKey="name" angle={-20} textAnchor="end" interval={0} tick={{ fontSize: 12 }} />
                                <YAxis tickFormatter={v => formatNumber(v)} />
                                <RTooltip {...tooltipStyle} formatter={v => formatNumber(v)} />
                                <Legend />
                                <Bar dataKey="dienTichDat" name="DT đất" fill="#1890ff" barSize={32} />
                                <Bar dataKey="dienTichQuyDoi" name="DT quy đổi" fill="#13c2c2" barSize={32} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái cơ sở">
                        <ResponsiveContainer width="100%" height={280}>
                            <PieChart>
                                <Pie data={bieu_do_trang_thai} cx="50%" cy="45%" outerRadius={90}
                                    dataKey="value" label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                    labelLine={false}>
                                    {bieu_do_trang_thai.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                                </Pie>
                                <RTooltip {...tooltipStyle} />
                            </PieChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            {/* Chart: số khu nhà / phòng / thiết bị */}
            <Card title="Số khu nhà · Phòng · Thiết bị theo cơ sở">
                <ResponsiveContainer width="100%" height={280}>
                    <BarChart data={bieu_do_so_luong} margin={{ bottom: 40 }}>
                        <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                        <XAxis dataKey="name" angle={-20} textAnchor="end" interval={0} tick={{ fontSize: 12 }} />
                        <YAxis />
                        <RTooltip {...tooltipStyle} />
                        <Legend />
                        <Bar dataKey="soKhuNha"  name="Khu nhà"  fill="#1890ff" barSize={20} />
                        <Bar dataKey="soPhong"   name="Phòng"    fill="#52c41a" barSize={20} />
                        <Bar dataKey="soThietBi" name="Thiết bị" fill="#faad14" barSize={20} />
                    </BarChart>
                </ResponsiveContainer>
            </Card>

            {/* Table */}
            <Card title={`Danh sách chi tiết (${chi_tiet?.length || 0} cơ sở)`}>
                <Table dataSource={chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 900 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: KHU NHÀ
// ─────────────────────────────────────────────────────
const TabKhuNha = ({ data }) => {
    const { tong_quan: tq, chi_tiet, bieu_do_loai, bieu_do_dien_tich, bieu_do_trang_thai } = data;

    const columns = [
        { title: 'Mã', dataIndex: 'ma_khu_nha', width: 100, fixed: 'left' },
        { title: 'Tên khu nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
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
            <Row gutter={16}>
                {[
                    { title: 'Tổng khu nhà', value: tq?.tong_khu_nha, icon: <HomeOutlined />, color: '#1890ff' },
                    { title: 'Tổng DT sàn XD', value: `${formatNumber(tq?.tong_dien_tich_san)} m²`, icon: <AreaChartOutlined />, color: '#52c41a' },
                    { title: 'Tổng DT đào tạo', value: `${formatNumber(Math.round(tq?.tong_dt_dao_tao))} m²`, icon: <AreaChartOutlined />, color: '#13c2c2' },
                    { title: 'Đang hoạt động', value: tq?.khu_nha_hoat_dong, icon: <HomeOutlined />, color: '#52c41a' },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <Card><Statistic title={item.title} value={item.value} prefix={item.icon}
                            valueStyle={{ color: item.color }} /></Card>
                    </Col>
                ))}
            </Row>

            <Row gutter={16}>
                <Col xs={24} lg={12}>
                    <Card title="Phân bố theo loại khu nhà">
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={bieu_do_loai}>
                                <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                <XAxis dataKey="name" />
                                <YAxis />
                                <RTooltip {...tooltipStyle} />
                                <Legend />
                                <Bar dataKey="soLuong" name="Số lượng" fill="#1890ff" barSize={40} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={12}>
                    <Card title="Trạng thái khu nhà">
                        <ResponsiveContainer width="100%" height={280}>
                            <PieChart>
                                <Pie data={bieu_do_trang_thai} cx="50%" cy="45%" outerRadius={90}
                                    dataKey="value" label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                    labelLine={false}>
                                    {bieu_do_trang_thai.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                                </Pie>
                                <RTooltip {...tooltipStyle} />
                            </PieChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            <Card title="Diện tích sàn & đào tạo theo khu nhà (m²)">
                <ResponsiveContainer width="100%" height={300}>
                    <BarChart data={bieu_do_dien_tich} margin={{ bottom: 50 }}>
                        <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                        <XAxis dataKey="name" angle={-25} textAnchor="end" interval={0} tick={{ fontSize: 11 }} />
                        <YAxis tickFormatter={v => formatNumber(v)} />
                        <RTooltip {...tooltipStyle} formatter={v => formatNumber(v)} />
                        <Legend />
                        <Bar dataKey="sanXD"    name="DT sàn XD"    fill="#1890ff" barSize={18} />
                        <Bar dataKey="dtDaoTao" name="DT đào tạo"   fill="#52c41a" barSize={18} />
                    </BarChart>
                </ResponsiveContainer>
            </Card>

            <Card title={`Danh sách chi tiết (${chi_tiet?.length || 0} khu nhà)`}>
                <Table dataSource={chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 1000 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: PHÒNG
// ─────────────────────────────────────────────────────
const TabPhong = ({ data }) => {
    const { tong_quan: tq, chi_tiet, bieu_do_loai, bieu_do_trang_thai, bieu_do_tang } = data;

    const columns = [
        { title: 'Mã', dataIndex: 'ma_phong', width: 90, fixed: 'left' },
        { title: 'Tên phòng', dataIndex: 'ten_phong', ellipsis: true },
        { title: 'Khu nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
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
            <Row gutter={16}>
                {[
                    { title: 'Tổng số phòng', value: tq?.tong_phong, icon: <AppstoreOutlined />, color: '#1890ff' },
                    { title: 'Tổng diện tích', value: `${formatNumber(tq?.tong_dien_tich)} m²`, icon: <AreaChartOutlined />, color: '#52c41a' },
                    { title: 'Tổng sức chứa', value: formatNumber(tq?.tong_suc_chua), icon: <AppstoreOutlined />, color: '#13c2c2' },
                    { title: 'Đang bảo trì', value: tq?.phong_bao_tri, icon: <WarningOutlined />, color: '#faad14' },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <Card><Statistic title={item.title} value={item.value} prefix={item.icon}
                            valueStyle={{ color: item.color }} /></Card>
                    </Col>
                ))}
            </Row>

            <Row gutter={16}>
                <Col xs={24} lg={14}>
                    <Card title="Phân bố theo loại phòng">
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={bieu_do_loai}>
                                <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                <XAxis dataKey="name" tick={{ fontSize: 12 }} />
                                <YAxis />
                                <RTooltip {...tooltipStyle} />
                                <Legend />
                                <Bar dataKey="soLuong" name="Số phòng"   fill="#8884d8" barSize={36} />
                                <Bar dataKey="sucChua" name="Sức chứa"   fill="#82ca9d" barSize={36} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái phòng">
                        <ResponsiveContainer width="100%" height={280}>
                            <PieChart>
                                <Pie data={bieu_do_trang_thai} cx="50%" cy="45%" outerRadius={90}
                                    dataKey="value" label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                    labelLine={false}>
                                    {bieu_do_trang_thai.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                                </Pie>
                                <RTooltip {...tooltipStyle} />
                            </PieChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            <Card title="Phân bố phòng theo tầng">
                <ResponsiveContainer width="100%" height={240}>
                    <BarChart data={bieu_do_tang}>
                        <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                        <XAxis dataKey="name" />
                        <YAxis />
                        <RTooltip {...tooltipStyle} />
                        <Legend />
                        <Bar dataKey="soPhong" name="Số phòng" fill="#ffc658" barSize={32} />
                    </BarChart>
                </ResponsiveContainer>
            </Card>

            <Card title={`Danh sách chi tiết (${chi_tiet?.length || 0} phòng)`}>
                <Table dataSource={chi_tiet} columns={columns} rowKey="id"
                    pagination={{ pageSize: 10 }} scroll={{ x: 1000 }} size="small" />
            </Card>
        </Space>
    );
};

// ─────────────────────────────────────────────────────
// TAB: THIẾT BỊ
// ─────────────────────────────────────────────────────
const TabThietBi = ({ data }) => {
    const { tong_quan: tq, chi_tiet, bieu_do_loai, bieu_do_trang_thai, bieu_do_nam_mua, bieu_do_hang } = data;

    const columns = [
        { title: 'Mã TB', dataIndex: 'ma_thiet_bi', width: 100, fixed: 'left' },
        { title: 'Tên thiết bị', dataIndex: 'ten_thiet_bi', ellipsis: true },
        { title: 'Loại', dataIndex: 'loai_thiet_bi', render: loaiThietBiLabel },
        { title: 'Hãng', dataIndex: 'hang_san_xuat', ellipsis: true },
        { title: 'Model', dataIndex: 'model', ellipsis: true },
        { title: 'Phòng', dataIndex: 'ten_phong', ellipsis: true },
        { title: 'Khu nhà', dataIndex: 'ten_khu_nha', ellipsis: true },
        { title: 'Năm mua', dataIndex: 'nam_mua', align: 'center' },
        { title: 'Giá trị', dataIndex: 'gia_tri', align: 'right', render: v => formatCurrency(v) },
        {
            title: 'BDưỡng', dataIndex: 'qua_han_bao_duong', align: 'center',
            render: (v) => v ? <Tooltip title="Quá hạn bảo dưỡng"><Tag color="red">Quá hạn</Tag></Tooltip> : null,
        },
        { title: 'Trạng thái', dataIndex: 'trang_thai', render: trangThaiTag },
    ];

    return (
        <Space direction="vertical" size="large" style={{ width: '100%' }}>
            <Row gutter={16}>
                {[
                    { title: 'Tổng thiết bị', value: tq?.tong_thiet_bi, icon: <ToolOutlined />, color: '#1890ff' },
                    { title: 'Tổng giá trị', value: formatCurrency(tq?.tong_gia_tri), icon: <DollarOutlined />, color: '#722ed1' },
                    { title: 'Cần bảo dưỡng', value: tq?.can_bao_duong, icon: <WarningOutlined />, color: '#f5222d' },
                    { title: 'Thiết bị tốt', value: tq?.dang_hoat_dong, icon: <ToolOutlined />, color: '#52c41a' },
                ].map((item, i) => (
                    <Col xs={24} sm={12} lg={6} key={i}>
                        <Card><Statistic title={item.title} value={item.value} prefix={item.icon}
                            valueStyle={{ color: item.color }} /></Card>
                    </Col>
                ))}
            </Row>

            <Row gutter={16}>
                <Col xs={24} lg={14}>
                    <Card title="Số lượng & giá trị theo loại thiết bị">
                        <ResponsiveContainer width="100%" height={280}>
                            <BarChart data={bieu_do_loai}>
                                <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                <XAxis dataKey="name" />
                                <YAxis yAxisId="left" />
                                <YAxis yAxisId="right" orientation="right" tickFormatter={v => `${Math.round(v / 1e6)}M`} />
                                <RTooltip {...tooltipStyle} formatter={(v, name) => name === 'Giá trị' ? formatCurrency(v) : v} />
                                <Legend />
                                <Bar yAxisId="left"  dataKey="soLuong"    name="Số lượng" fill="#1890ff" barSize={32} />
                                <Bar yAxisId="right" dataKey="tongGiaTri" name="Giá trị"  fill="#722ed1" barSize={32} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Trạng thái thiết bị">
                        <ResponsiveContainer width="100%" height={280}>
                            <PieChart>
                                <Pie data={bieu_do_trang_thai} cx="50%" cy="45%" outerRadius={90}
                                    dataKey="value" label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                    labelLine={false}>
                                    {bieu_do_trang_thai.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                                </Pie>
                                <RTooltip {...tooltipStyle} />
                            </PieChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            <Row gutter={16}>
                <Col xs={24} lg={14}>
                    <Card title="Xu hướng mua sắm theo năm">
                        <ResponsiveContainer width="100%" height={260}>
                            <LineChart data={bieu_do_nam_mua}>
                                <CartesianGrid strokeDasharray="3 3" strokeWidth={0.5} />
                                <XAxis dataKey="name" />
                                <YAxis yAxisId="left" />
                                <YAxis yAxisId="right" orientation="right" tickFormatter={v => `${Math.round(v / 1e6)}M`} />
                                <RTooltip {...tooltipStyle} formatter={(v, name) => name === 'Giá trị' ? formatCurrency(v) : v} />
                                <Legend />
                                <Line yAxisId="left"  type="monotone" dataKey="soLuong"    name="Số lượng" stroke="#1890ff" strokeWidth={2} dot={{ r: 4 }} />
                                <Line yAxisId="right" type="monotone" dataKey="tongGiaTri" name="Giá trị"  stroke="#722ed1" strokeWidth={2} dot={{ r: 4 }} />
                            </LineChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
                <Col xs={24} lg={10}>
                    <Card title="Top 10 hãng sản xuất">
                        <ResponsiveContainer width="100%" height={260}>
                            <BarChart data={bieu_do_hang} layout="vertical" margin={{ left: 20 }}>
                                <CartesianGrid strokeDasharray="0" strokeWidth={0.5} horizontal={false} />
                                <XAxis type="number" />
                                <YAxis type="category" dataKey="name" width={90} tick={{ fontSize: 12 }} />
                                <RTooltip {...tooltipStyle} />
                                <Bar dataKey="soLuong" name="Số lượng" fill="#13c2c2" barSize={16} />
                            </BarChart>
                        </ResponsiveContainer>
                    </Card>
                </Col>
            </Row>

            <Card title={`Danh sách chi tiết (${chi_tiet?.length || 0} thiết bị)`}>
                <Table dataSource={chi_tiet} columns={columns} rowKey="id"
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
const ThongKeIndex = ({ thongKeCoSo, thongKeKhuNha, thongKePhong, thongKeThietBi }) => {
    const [tab, setTab] = useState('co-so');

    const options = [
        { label: <Space><BankOutlined />Cơ sở</Space>,      value: 'co-so'    },
        { label: <Space><HomeOutlined />Khu nhà</Space>,    value: 'khu-nha'  },
        { label: <Space><AppstoreOutlined />Phòng</Space>,  value: 'phong'    },
        { label: <Space><ToolOutlined />Thiết bị</Space>,   value: 'thiet-bi' },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                    <Title level={2} style={{ margin: 0 }}>
                        <AreaChartOutlined style={{ marginRight: 8, color: '#1890ff' }} />
                        Thống kê chi tiết
                    </Title>
                    <Segmented
                        options={options}
                        value={tab}
                        onChange={setTab}
                        size="large"
                    />
                </div>

                {tab === 'co-so'    && <TabCoSo    data={thongKeCoSo}    />}
                {tab === 'khu-nha'  && <TabKhuNha  data={thongKeKhuNha}  />}
                {tab === 'phong'    && <TabPhong   data={thongKePhong}   />}
                {tab === 'thiet-bi' && <TabThietBi data={thongKeThietBi} />}
            </Space>
        </MainLayout>
    );
};

export default ThongKeIndex;
