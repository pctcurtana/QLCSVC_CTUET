import React, { useState, useEffect } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Table, Button, Space, Input, Tag, Card, Row, Col, Popconfirm, message, Select, Skeleton } from 'antd';
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    SearchOutlined,
    ReloadOutlined,
} from '@ant-design/icons';
import { Link, router } from '@inertiajs/react';

const { Search } = Input;

const Index = ({ phongs, khuNhas, filters }) => {
    const [searchText, setSearchText] = useState(filters.search || '');
    const [khuNhaFilter, setKhuNhaFilter] = useState(filters.khu_nha_id || '');
    const [loaiFilter, setLoaiFilter] = useState(filters.loai_phong || '');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [phongs]);
    const [trangThaiFilter, setTrangThaiFilter] = useState(filters.trang_thai || '');

    const handleSearch = (value) => {
        router.get('/phong', { 
            search: value, 
            khu_nha_id: khuNhaFilter,
            loai_phong: loaiFilter,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleKhuNhaFilter = (value) => {
        setKhuNhaFilter(value);
        router.get('/phong', { 
            search: searchText, 
            khu_nha_id: value,
            loai_phong: loaiFilter,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleLoaiFilter = (value) => {
        setLoaiFilter(value);
        router.get('/phong', { 
            search: searchText, 
            khu_nha_id: khuNhaFilter,
            loai_phong: value,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleTrangThaiFilter = (value) => {
        setTrangThaiFilter(value);
        router.get('/phong', { 
            search: searchText, 
            khu_nha_id: khuNhaFilter,
            loai_phong: loaiFilter,
            trang_thai: value 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (id) => {
        router.delete(`/phong/${id}`, {
            onSuccess: () => {
                // success toast hiển thị qua flash ở MainLayout
            },
            onError: () => {
                message.error('Có lỗi xảy ra khi xóa phòng!');
            },
        });
    };

    const handleReset = () => {
        setSearchText('');
        setKhuNhaFilter('');
        setLoaiFilter('');
        setTrangThaiFilter('');
        router.get('/phong');
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    };

    const getLoaiPhongLabel = (loai) => {
        const labels = {
            'phong_hoc': 'Phòng học',
            'phong_thi_nghiem': 'Phòng thí nghiệm',
            'phong_thuc_hanh': 'Phòng thực hành',
            'phong_lam_viec': 'Phòng làm việc',
            'phong_chuc_nang': 'Phòng chức năng',
        };
        return labels[loai] || loai;
    };

    const getLoaiPhongColor = (loai) => {
        const colors = {
            'phong_hoc': 'blue',
            'phong_thi_nghiem': 'purple',
            'phong_thuc_hanh': 'cyan',
            'phong_lam_viec': 'green',
            'phong_chuc_nang': 'orange',
        };
        return colors[loai] || 'default';
    };

    const getTrangThaiLabel = (trangThai) => {
        const labels = {
            'active': 'Hoạt động',
            'maintenance': 'Bảo trì',
            'inactive': 'Không hoạt động',
        };
        return labels[trangThai] || trangThai;
    };

    const getTrangThaiColor = (trangThai) => {
        const colors = {
            'active': 'green',
            'maintenance': 'orange',
            'inactive': 'red',
        };
        return colors[trangThai] || 'default';
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 60,
            fixed: 'left',
            render: (text, record, index) => (phongs.current_page - 1) * phongs.per_page + index + 1,
        },
        {
            title: 'Mã phòng',
            dataIndex: 'ma_phong',
            key: 'ma_phong',
            width: 120,
            fixed: 'left',
        },
        {
            title: 'Tên phòng',
            dataIndex: 'ten_phong',
            key: 'ten_phong',
            width: 160,
            fixed: 'left',
            ellipsis: true,
            render: (text) => <strong>{text}</strong>,
        },
        {
            title: 'Khu nhà',
            dataIndex: ['khu_nha', 'ten_khu_nha'],
            key: 'khu_nha',
            width: 140,
            ellipsis: true,
        },
        {
            title: 'Cơ sở',
            dataIndex: ['khu_nha', 'co_so', 'ten_co_so'],
            key: 'co_so',
            width: 140,
            ellipsis: true,
        },
        {
            title: 'Loại phòng',
            dataIndex: 'loai_phong',
            key: 'loai_phong',
            width: 130,
            render: (loai) => (
                <Tag color={getLoaiPhongColor(loai)}>
                    {getLoaiPhongLabel(loai)}
                </Tag>
            ),
        },
        {
            title: 'Tầng',
            dataIndex: 'tang',
            key: 'tang',
            width: 70,
            align: 'center',
        },
        {
            title: 'Diện tích (m²)',
            dataIndex: 'dien_tich',
            key: 'dien_tich',
            width: 110,
            align: 'right',
            render: (value) => formatNumber(value),
        },
        {
            title: 'Sức chứa',
            dataIndex: 'suc_chua',
            key: 'suc_chua',
            width: 90,
            align: 'center',
            render: (value) => `${value} người`,
        },
        {
            title: 'Số TB',
            dataIndex: 'thiet_bis_count',
            key: 'thiet_bis_count',
            width: 70,
            align: 'center',
            render: (value) => <Tag color="blue">{value}</Tag>,
        },
        {
            title: 'Trạng thái',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            width: 110,
            align: 'center',
            render: (trangThai) => (
                <Tag color={getTrangThaiColor(trangThai)}>
                    {getTrangThaiLabel(trangThai)}
                </Tag>
            ),
        },
        {
            title: 'Thao tác',
            key: 'action',
            fixed: 'right',
            width: 150,
            render: (_, record) => (
                <Space size="small">
                    <Link href={`/phong/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    <Popconfirm
                        title="Xác nhận xóa"
                        description="Bạn có chắc chắn muốn xóa phòng này?"
                        onConfirm={() => handleDelete(record.id)}
                        okText="Xóa"
                        cancelText="Hủy"
                        okButtonProps={{ danger: true }}
                    >
                        <Button danger size="small" icon={<DeleteOutlined />}>
                            Xóa
                        </Button>
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <Card>
                    <Row gutter={[16, 16]} align="middle">
                        <Col flex="auto">
                            <h2 style={{ margin: 0 }}>Quản lý phòng</h2>
                        </Col>
                        <Col>
                            <Link href="/phong/create">
                                <Button type="primary" icon={<PlusOutlined />} size="large">
                                    Thêm phòng
                                </Button>
                            </Link>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    <Row gutter={[16, 16]}>
                        <Col xs={24} sm={12} md={6}>
                            <Search
                                placeholder="Tìm kiếm theo mã, tên..."
                                allowClear
                                enterButton={<SearchOutlined />}
                                size="large"
                                value={searchText}
                                onChange={(e) => setSearchText(e.target.value)}
                                onSearch={handleSearch}
                            />
                        </Col>
                        <Col xs={24} sm={12} md={5}>
                            <Select
                                placeholder="Lọc theo khu nhà"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                value={khuNhaFilter || undefined}
                                onChange={handleKhuNhaFilter}
                                options={khuNhas.map(kn => ({ 
                                    value: kn.id, 
                                    label: `${kn.ten_khu_nha} - ${kn.co_so.ten_co_so}` 
                                }))}
                            />
                        </Col>
                        <Col xs={24} sm={12} md={5}>
                            <Select
                                placeholder="Lọc theo loại"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                value={loaiFilter || undefined}
                                onChange={handleLoaiFilter}
                                options={[
                                    { value: 'phong_hoc', label: 'Phòng học' },
                                    { value: 'phong_thi_nghiem', label: 'Phòng thí nghiệm' },
                                    { value: 'phong_thuc_hanh', label: 'Phòng thực hành' },
                                    { value: 'phong_lam_viec', label: 'Phòng làm việc' },
                                    { value: 'phong_chuc_nang', label: 'Phòng chức năng' },
                                ]}
                            />
                        </Col>
                        <Col xs={24} sm={12} md={5}>
                            <Select
                                placeholder="Lọc theo trạng thái"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                value={trangThaiFilter || undefined}
                                onChange={handleTrangThaiFilter}
                                options={[
                                    { value: 'active', label: 'Hoạt động' },
                                    { value: 'maintenance', label: 'Bảo trì' },
                                    { value: 'inactive', label: 'Không hoạt động' },
                                ]}
                            />
                        </Col>
                        <Col>
                            <Button icon={<ReloadOutlined />} size="large" onClick={handleReset}>
                                Làm mới
                            </Button>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    {loading ? (
                        <Skeleton active paragraph={{ rows: 10 }} />
                    ) : (
                        <Table
                            columns={columns}
                            dataSource={phongs.data}
                        rowKey="id"
                        scroll={{ x: 1550 }}
                        pagination={{
                            current: phongs.current_page,
                            pageSize: phongs.per_page,
                            total: phongs.total,
                            showSizeChanger: true,
                            showTotal: (total) => `Tổng số ${total} phòng`,
                            onChange: (page, pageSize) => {
                                router.get('/phong', {
                                    page,
                                    per_page: pageSize,
                                    search: searchText,
                                    khu_nha_id: khuNhaFilter,
                                    loai_phong: loaiFilter,
                                    trang_thai: trangThaiFilter,
                                }, {
                                    preserveState: true,
                                    replace: true,
                                });
                            },
                        }}
                        />
                    )}
                </Card>
            </Space>
        </MainLayout>
    );
};

export default Index;

