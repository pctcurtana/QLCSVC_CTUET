import React, { useState, useEffect } from 'react';
import MainLayout from './Layout/MainLayout';
import { Card, Row, Col, Statistic, Typography, Space, Table, Skeleton } from 'antd';
import {
    BankOutlined,
    HomeOutlined,
    AppstoreOutlined,
    ToolOutlined,
    DollarOutlined,
    AreaChartOutlined,
} from '@ant-design/icons';
import {
    BarChart,
    Bar,
    PieChart,
    Pie,
    Cell,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';

const { Title } = Typography;

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d'];

const Dashboard = ({ statistics, thongKeLoaiPhong, thongKeLoaiThietBi, thongKeCoSo, thongKeTrangThaiPhong }) => {
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [statistics]);
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value);
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    };

    // Chuẩn bị dữ liệu cho biểu đồ loại phòng
    const loaiPhongData = thongKeLoaiPhong.map(item => ({
        name: getLoaiPhongLabel(item.loai_phong),
        soLuong: item.so_luong,
        dienTich: parseFloat(item.dien_tich || 0),
    }));

    // Chuẩn bị dữ liệu cho biểu đồ loại thiết bị
    const loaiThietBiData = thongKeLoaiThietBi.map(item => ({
        name: getLoaiThietBiLabel(item.loai_thiet_bi),
        soLuong: item.so_luong,
        giaTri: parseFloat(item.gia_tri || 0),
    }));

    // Chuẩn bị dữ liệu cho biểu đồ cơ sở
    const coSoData = thongKeCoSo.map(item => ({
        name: item.ten_co_so,
        soKhuNha: item.so_khu_nha,
        dienTich: parseFloat(item.dien_tich || 0),
    }));

    // Dữ liệu trạng thái phòng cho pie chart
    const trangThaiPhongData = thongKeTrangThaiPhong.map(item => ({
        name: getTrangThaiLabel(item.trang_thai),
        value: item.so_luong,
    }));

    function getLoaiPhongLabel(loai) {
        const labels = {
            'phong_hoc': 'Phòng học',
            'phong_thi_nghiem': 'Phòng thí nghiệm',
            'phong_thuc_hanh': 'Phòng thực hành',
            'phong_lam_viec': 'Phòng làm việc',
            'phong_chuc_nang': 'Phòng chức năng',
        };
        return labels[loai] || loai;
    }

    function getLoaiThietBiLabel(loai) {
        const labels = {
            'van_phong': 'Văn phòng',
            'day_hoc': 'Dạy học',
            'thi_nghiem': 'Thí nghiệm',
            'thuc_hanh': 'Thực hành',
        };
        return labels[loai] || loai;
    }

    function getTrangThaiLabel(trangThai) {
        const labels = {
            'active': 'Hoạt động',
            'maintenance': 'Bảo trì',
            'inactive': 'Không hoạt động',
        };
        return labels[trangThai] || trangThai;
    }

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <Title level={2}>Tổng quan QLCSVC</Title>

                {loading ? (
                    <>
                        {/* Skeleton for statistics */}
                        <Row gutter={16}>
                            {[1, 2, 3, 4, 5, 6].map((item) => (
                                <Col xs={24} sm={12} lg={8} key={item}>
                                    <Card>
                                        <Skeleton active paragraph={{ rows: 1 }} />
                                    </Card>
                                </Col>
                            ))}
                        </Row>
                        {/* Skeleton for charts */}
                        <Row gutter={16}>
                            <Col xs={24} lg={14}>
                                <Card>
                                    <Skeleton active paragraph={{ rows: 8 }} />
                                </Card>
                            </Col>
                            <Col xs={24} lg={10}>
                                <Card>
                                    <Skeleton active paragraph={{ rows: 8 }} />
                                </Card>
                            </Col>
                        </Row>
                        <Row gutter={16}>
                            <Col xs={24} lg={12}>
                                <Card>
                                    <Skeleton active paragraph={{ rows: 8 }} />
                                </Card>
                            </Col>
                            <Col xs={24} lg={12}>
                                <Card>
                                    <Skeleton active paragraph={{ rows: 8 }} />
                                </Card>
                            </Col>
                        </Row>
                    </>
                ) : (
                    <>
                        {/* Thống kê tổng quan */}
                        <Row gutter={16}>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng số cơ sở"
                                value={statistics.tong_co_so}
                                prefix={<BankOutlined />}
                                valueStyle={{ color: '#3f8600' }}
                            />
                        </Card>
                    </Col>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng số khu nhà"
                                value={statistics.tong_khu_nha}
                                prefix={<HomeOutlined />}
                                valueStyle={{ color: '#1890ff' }}
                            />
                        </Card>
                    </Col>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng số phòng"
                                value={statistics.tong_phong}
                                prefix={<AppstoreOutlined />}
                                valueStyle={{ color: '#cf1322' }}
                            />
                        </Card>
                    </Col>
                </Row>

                <Row gutter={16}>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng số thiết bị"
                                value={statistics.tong_thiet_bi}
                                prefix={<ToolOutlined />}
                                valueStyle={{ color: '#faad14' }}
                            />
                        </Card>
                    </Col>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng giá trị thiết bị"
                                value={formatCurrency(statistics.tong_gia_tri_thiet_bi)}
                                prefix={<DollarOutlined />}
                                valueStyle={{ color: '#722ed1' }}
                            />
                        </Card>
                    </Col>
                    <Col xs={24} sm={12} lg={8}>
                        <Card>
                            <Statistic
                                title="Tổng diện tích (m²)"
                                value={formatNumber(statistics.tong_dien_tich)}
                                prefix={<AreaChartOutlined />}
                                valueStyle={{ color: '#13c2c2' }}
                            />
                        </Card>
                    </Col>
                </Row>

                {/* Biểu đồ thống kê loại phòng */}
                <Row gutter={16}>
                    <Col xs={24} lg={14}>
                        <Card title="Thống kê theo loại phòng">
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={loaiPhongData}>
                                    <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip cursor={{ fill: "rgba(0, 0, 0, 0.1)" }} 
                                        contentStyle={{
                                            borderRadius: "8px",
                                            border: "1px solid #ccc",
                                          }}
                                          itemStyle={{ color: "#333", fontWeight: "bold" }}
                                          labelStyle={{ color: "#8884d8", fontSize: 14 }}   />
                                    <Legend />
                                    <Bar dataKey="soLuong" fill="#8884d8" name="Số lượng"
                                     barSize={40}  />
                                </BarChart>
                            </ResponsiveContainer>
                        </Card>
                    </Col>

                    {/* Biểu đồ trạng thái phòng */}
                    <Col xs={24} lg={10}>
                        <Card title="Trạng thái phòng">
                            <ResponsiveContainer width="100%" height={300}>
                                <PieChart>
                                    <Pie
                                        data={trangThaiPhongData}
                                        cx="50%"
                                        cy="50%"
                                        labelLine={false}
                                        label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                                        outerRadius={80}
                                        fill="#8884d8"
                                        dataKey="value"
                                    >
                                        {trangThaiPhongData.map((entry, index) => (
                                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        contentStyle={{
                                            borderRadius: "8px",
                                            border: "1px solid #ccc",
                                          }}
                                          itemStyle={{ color: "#333", fontWeight: "bold" }}
                                          labelStyle={{ color: "#8884d8", fontSize: 14 }} />
                                </PieChart>
                            </ResponsiveContainer>
                        </Card>
                    </Col>
                </Row>

                {/* Biểu đồ thiết bị */}
                <Row gutter={16}>
                    <Col xs={24} lg={12}>
                        <Card title="Thống kê thiết bị theo loại">
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={loaiThietBiData}>
                                    <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip cursor={{ fill: "rgba(0, 0, 0, 0.1)" }} 
                                        contentStyle={{
                                            borderRadius: "8px",
                                            border: "1px solid #ccc",
                                          }}
                                          itemStyle={{ color: "#333", fontWeight: "bold" }}
                                          labelStyle={{ color: "#82ca9d", fontSize: 14 }} />
                                    <Legend />
                                    <Bar dataKey="soLuong" fill="#82ca9d" name="Số lượng" />
                                </BarChart>
                            </ResponsiveContainer>
                        </Card>
                    </Col>

                    {/* Biểu đồ cơ sở */}
                    <Col xs={24} lg={12}>
                        <Card title="Thống kê theo cơ sở">
                            <ResponsiveContainer width="100%" height={300}>
                                <BarChart data={coSoData}>
                                    <CartesianGrid strokeDasharray="0" strokeWidth={0.5} vertical={false} />
                                    <XAxis dataKey="name" />
                                    <YAxis />
                                    <Tooltip cursor={{ fill: "rgba(0, 0, 0, 0.1)" }} 
                                        contentStyle={{
                                            borderRadius: "8px",
                                            border: "1px solid #ccc",
                                          }}
                                          itemStyle={{ color: "#333", fontWeight: "bold" }}
                                          labelStyle={{ color: "#ffc658", fontSize: 14 }} />
                                    <Legend />
                                    <Bar dataKey="soKhuNha" fill="#ffc658" name="Số khu nhà" />
                                </BarChart>
                            </ResponsiveContainer>
                        </Card>
                    </Col>
                </Row>

                {/* Thống kê diện tích */}
                <Row gutter={16}>
                    <Col xs={24}>
                        <Card title="Thống kê diện tích">
                            <Row gutter={16}>
                                <Col span={8}>
                                    <Statistic
                                        title="Tổng diện tích"
                                        value={formatNumber(statistics.tong_dien_tich)}
                                        suffix="m²"
                                    />
                                </Col>
                                <Col span={8}>
                                    <Statistic
                                        title="Diện tích sân xây dựng"
                                        value={formatNumber(statistics.dien_tich_san_xay_dung)}
                                        suffix="m²"
                                        valueStyle={{ color: '#1890ff' }}
                                    />
                                </Col>
                                <Col span={8}>
                                    <Statistic
                                        title="Diện tích còn lại"
                                        value={formatNumber(statistics.dien_tich_con_lai)}
                                        suffix="m²"
                                        valueStyle={{ color: '#3f8600' }}
                                    />
                                </Col>
                            </Row>
                        </Card>
                    </Col>
                </Row>
                    </>
                )}
            </Space>
        </MainLayout>
    );
};

export default Dashboard;
