import React, { useState, useEffect } from 'react';
import MainLayout from '../Layout/MainLayout';
import {
    Table, Space, Input, Tag, Card, Row, Col, Select, Skeleton, Statistic, Empty, Tooltip, Button, DatePicker,
} from 'antd';
import {
    SearchOutlined,
    ReloadOutlined,
    InboxOutlined,
    ToolOutlined,
    ArrowRightOutlined,
} from '@ant-design/icons';
import { router } from '@inertiajs/react';
import dayjs from 'dayjs';

const { RangePicker } = DatePicker;

const { Search } = Input;

const Kho = ({ thietBis, stats, phongs, filters }) => {
    const [searchText, setSearchText] = useState(filters.search || '');
    const [phongFilter, setPhongFilter] = useState(filters.phong_id || '');
    const [dateRange, setDateRange] = useState(
        filters.ngay_vao_kho_tu && filters.ngay_vao_kho_den
            ? [dayjs(filters.ngay_vao_kho_tu), dayjs(filters.ngay_vao_kho_den)]
            : null
    );
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [thietBis]);

    const doFilter = (overrides = {}) => {
        router.get('/kho', {
            search: searchText,
            phong_id: phongFilter,
            ngay_vao_kho_tu: dateRange?.[0]?.format('YYYY-MM-DD') || '',
            ngay_vao_kho_den: dateRange?.[1]?.format('YYYY-MM-DD') || '',
            ...overrides,
        }, { preserveState: true, replace: true });
    };

    const handleSearch = (value) => {
        doFilter({ search: value });
    };

    const handlePhongFilter = (value) => {
        setPhongFilter(value ?? '');
        doFilter({ phong_id: value ?? '' });
    };

    const handleDateRange = (dates) => {
        setDateRange(dates);
        doFilter({
            ngay_vao_kho_tu: dates?.[0]?.format('YYYY-MM-DD') || '',
            ngay_vao_kho_den: dates?.[1]?.format('YYYY-MM-DD') || '',
        });
    };

    const handleReset = () => {
        setSearchText('');
        setPhongFilter('');
        setDateRange(null);
        router.get('/kho');
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return '—';
        return new Date(dateStr).toLocaleDateString('vi-VN');
    };

    const getLoaiLabel = (loai) => {
        const labels = { van_phong: 'Văn phòng', day_hoc: 'Dạy học', thi_nghiem: 'Thí nghiệm', thuc_hanh: 'Thực hành' };
        return labels[loai] || loai;
    };

    const getLoaiColor = (loai) => {
        const colors = { van_phong: 'blue', day_hoc: 'green', thi_nghiem: 'purple', thuc_hanh: 'orange' };
        return colors[loai] || 'default';
    };

    const getKhoTrangThaiLabel = (trangThai) => {
        const labels = { tot: 'Đang lưu kho', can_sua_chua: 'Chờ sửa chữa', hu_hong: 'Chờ thanh lý' };
        return labels[trangThai] || trangThai;
    };

    const getKhoTrangThaiColor = (trangThai) => {
        const colors = { tot: 'blue', can_sua_chua: 'orange', hu_hong: 'red' };
        return colors[trangThai] || 'default';
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 60,
            fixed: 'left',
            align: 'center',
            render: (_, __, index) =>
                (thietBis.current_page - 1) * thietBis.per_page + index + 1,
        },
        // {
        //     title: 'Mã TB',
        //     dataIndex: 'ma_thiet_bi',
        //     key: 'ma_thiet_bi',
        //     width: 110,
        //     fixed: 'left',
        // },
        {
            title: 'Tên thiết bị',
            dataIndex: 'ten_thiet_bi',
            key: 'ten_thiet_bi',
            width: 200,
            fixed: 'left',
            ellipsis: true,
            render: (text) => <strong>{text}</strong>,
        },
        {
            title: 'Phiên bản',
            dataIndex: 'phien_ban',
            key: 'phien_ban',
            width: 90,
            align: 'center',
            render: (v) => <Tag color="default">v{v ?? 1}</Tag>,
        },
        {
            title: 'Phòng (cũ)',
            key: 'phong',
            width: 140,
            ellipsis: true,
            render: (_, record) => record.phong?.ten_phong || <Tag>Chưa phân bổ</Tag>,
        },
        {
            title: 'Khu nhà (cũ)',
            key: 'khu_nha',
            width: 150,
            ellipsis: true,
            render: (_, record) =>
                record.phong?.khu_nha?.ten_khu_nha
                ?? record.phong?.khuNha?.ten_khu_nha
                ?? '—',
        },
        {
            title: 'Cơ sở (cũ)',
            key: 'co_so',
            width: 150,
            ellipsis: true,
            render: (_, record) =>
                record.phong?.khu_nha?.co_so?.ten_co_so
                ?? record.phong?.khuNha?.coSo?.ten_co_so
                ?? '—',
        },
        {
            title: 'Loại TB',
            dataIndex: 'loai_thiet_bi',
            key: 'loai_thiet_bi',
            width: 110,
            render: (loai) => <Tag color={getLoaiColor(loai)}>{getLoaiLabel(loai)}</Tag>,
        },
        {
            title: 'Hãng SX',
            dataIndex: 'hang_san_xuat',
            key: 'hang_san_xuat',
            width: 120,
            ellipsis: true,
        },
        {
            title: 'Model',
            dataIndex: 'model',
            key: 'model',
            width: 110,
            ellipsis: true,
        },
        {
            title: 'Serial Number',
            dataIndex: 'serial_number',
            key: 'serial_number',
            width: 140,
            ellipsis: true,
            render: (text) => text ? <Tag color="blue">{text}</Tag> : '—',
        },
        {
            title: 'Năm mua',
            dataIndex: 'nam_mua',
            key: 'nam_mua',
            width: 85,
            align: 'center',
        },
        {
            title: 'Giá trị',
            dataIndex: 'gia_tri',
            key: 'gia_tri',
            width: 130,
            align: 'right',
            render: (value) => formatCurrency(value),
        },
        {
            title: 'Trạng thái kho',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            width: 130,
            align: 'center',
            render: (trangThai) => (
                <Tag color={getKhoTrangThaiColor(trangThai)}>
                    {getKhoTrangThaiLabel(trangThai)}
                </Tag>
            ),
        },
        {
            title: 'Đưa vào kho',
            dataIndex: 'hieu_luc_den',
            key: 'hieu_luc_den',
            width: 120,
            align: 'center',
            render: (date) => formatDate(date),
        },
        {
            title: 'Thay thế bởi',
            key: 'thiet_bi_thay_the',
            width: 220,
            render: (_, record) => {
                const tb = record.thiet_bi_thay_the;
                if (!tb) return <Tag color="default">—</Tag>;
                const phong = tb.phong?.ten_phong;
                return (
                    <Tooltip
                        title={
                            <div>
                                <div><strong>{tb.ten_thiet_bi}</strong> (v{tb.phien_ban ?? 1})</div>
                                <div>Cập nhật: {formatDate(tb.hieu_luc_tu)}</div>
                                {phong && <div>Phòng: {phong}</div>}
                            </div>
                        }
                    >
                        <span style={{ cursor: 'default' }}>
                            <ArrowRightOutlined style={{ color: '#52c41a', marginRight: 6 }} />
                            <Tag color="green" style={{ marginRight: 0 }}>
                                v{tb.phien_ban ?? 1}
                            </Tag>
                            <span style={{ marginLeft: 6, fontSize: 13 }}>
                                {tb.ten_thiet_bi.length > 20
                                    ? tb.ten_thiet_bi.slice(0, 20) + '…'
                                    : tb.ten_thiet_bi}
                            </span>
                        </span>
                    </Tooltip>
                );
            },
        },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                {/* Tiêu đề trang */}
                <Card>
                    <Row gutter={[16, 16]} align="middle">
                        <Col flex="auto">
                            <h2 style={{ margin: 0 }}>Kho thiết bị</h2>
                            <div style={{ color: '#666', fontSize: 13, marginTop: 4 }}>
                                Thiết bị đã được thay thế bởi phiên bản mới — lưu trữ để theo dõi lịch sử
                            </div>
                        </Col>
                    </Row>
                </Card>

                {/* Thống kê tổng quan */}
                <Card>
                    <Row gutter={16}>
                        <Col xs={24} sm={12} md={6}>
                            <Card bordered={false} style={{ background: '#f9f0ff' }}>
                                <Statistic
                                    title="Tổng thiết bị trong kho"
                                    value={stats?.tong ?? 0}
                                    prefix={<InboxOutlined />}
                                    valueStyle={{ color: '#722ed1' }}
                                />
                            </Card>
                        </Col>
                        <Col xs={24} sm={12} md={6}>
                            <Card bordered={false} style={{ background: '#f0f5ff' }}>
                                <Statistic
                                    title="Đang lưu kho"
                                    value={stats?.tot ?? 0}
                                    prefix={<InboxOutlined />}
                                    valueStyle={{ color: '#1890ff' }}
                                    suffix={<span style={{ fontSize: 12, fontWeight: 400, color: '#888' }}>thiết bị</span>}
                                />
                            </Card>
                        </Col>
                        <Col xs={24} sm={12} md={6}>
                            <Card bordered={false} style={{ background: '#fff7e6' }}>
                                <Statistic
                                    title="Chờ sửa chữa"
                                    value={stats?.can_sua_chua ?? 0}
                                    prefix={<ToolOutlined />}
                                    valueStyle={{ color: '#fa8c16' }}
                                    suffix={<span style={{ fontSize: 12, fontWeight: 400, color: '#888' }}>thiết bị</span>}
                                />
                            </Card>
                        </Col>
                        <Col xs={24} sm={12} md={6}>
                            <Card bordered={false} style={{ background: '#fff1f0' }}>
                                <Statistic
                                    title="Chờ thanh lý"
                                    value={stats?.hu_hong ?? 0}
                                    prefix={<ToolOutlined />}
                                    valueStyle={{ color: '#cf1322' }}
                                    suffix={<span style={{ fontSize: 12, fontWeight: 400, color: '#888' }}>thiết bị</span>}
                                />
                            </Card>
                        </Col>
                    </Row>
                </Card>

                {/* Bộ lọc */}
                <Card>
                    <Row gutter={[16, 16]}>
                        <Col xs={24} sm={12} md={8}>
                            <Search
                                placeholder="Tìm kiếm theo mã, tên, serial, hãng..."
                                allowClear
                                enterButton={<SearchOutlined />}
                                size="large"
                                value={searchText}
                                onChange={(e) => setSearchText(e.target.value)}
                                onSearch={handleSearch}
                            />
                        </Col>
                        <Col xs={24} sm={12} md={6}>
                            <Select
                                placeholder="Lọc theo phòng"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                showSearch
                                optionFilterProp="label"
                                value={phongFilter || undefined}
                                onChange={handlePhongFilter}
                                options={(phongs || []).map(p => ({
                                    value: p.id,
                                    label: `${p.ten_phong} - ${p.khu_nha?.ten_khu_nha ?? p.khuNha?.ten_khu_nha ?? ''}`,
                                }))}
                            />
                        </Col>
                        <Col xs={24} sm={12} md={7}>
                            <RangePicker
                                size="large"
                                style={{ width: '100%' }}
                                placeholder={['Từ ngày vào kho', 'Đến ngày']}
                                value={dateRange}
                                onChange={handleDateRange}
                                format="DD/MM/YYYY"
                            />
                        </Col>
                        <Col>
                            <Button icon={<ReloadOutlined />} size="large" onClick={handleReset}>
                                Làm mới
                            </Button>
                        </Col>
                    </Row>
                </Card>

                {/* Bảng danh sách */}
                <Card>
                    {loading ? (
                        <Skeleton active paragraph={{ rows: 10 }} />
                    ) : (
                        <Table
                            columns={columns}
                            dataSource={thietBis.data}
                            rowKey="id"
                            scroll={{ x: 1800 }}
                            locale={{ emptyText: <Empty description="Kho đang trống — chưa có thiết bị nào được lưu trữ" image={Empty.PRESENTED_IMAGE_SIMPLE} /> }}
                            pagination={{
                                current: thietBis.current_page,
                                pageSize: thietBis.per_page,
                                total: thietBis.total,
                                showSizeChanger: false,
                                showTotal: (total) => `Tổng số ${total} thiết bị trong kho`,
                                onChange: (page) => {
                                    setLoading(true);
                                    router.get('/kho', {
                                        page,
                                        search: searchText,
                                        phong_id: phongFilter,
                                        ngay_vao_kho_tu: dateRange?.[0]?.format('YYYY-MM-DD') || '',
                                        ngay_vao_kho_den: dateRange?.[1]?.format('YYYY-MM-DD') || '',
                                    }, { preserveState: true, replace: true });
                                },
                            }}
                        />
                    )}
                </Card>
            </Space>
        </MainLayout>
    );
};

export default Kho;
