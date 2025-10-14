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

const Index = ({ thietBis, phongs, filters }) => {
    const [searchText, setSearchText] = useState(filters.search || '');
    const [phongFilter, setPhongFilter] = useState(filters.phong_id || '');
    const [loaiFilter, setLoaiFilter] = useState(filters.loai_thiet_bi || '');
    const [trangThaiFilter, setTrangThaiFilter] = useState(filters.trang_thai || '');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [thietBis]);

    const handleSearch = (value) => {
        router.get('/thiet-bi', { 
            search: value, 
            phong_id: phongFilter,
            loai_thiet_bi: loaiFilter,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handlePhongFilter = (value) => {
        setPhongFilter(value);
        router.get('/thiet-bi', { 
            search: searchText, 
            phong_id: value,
            loai_thiet_bi: loaiFilter,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleLoaiFilter = (value) => {
        setLoaiFilter(value);
        router.get('/thiet-bi', { 
            search: searchText, 
            phong_id: phongFilter,
            loai_thiet_bi: value,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleTrangThaiFilter = (value) => {
        setTrangThaiFilter(value);
        router.get('/thiet-bi', { 
            search: searchText, 
            phong_id: phongFilter,
            loai_thiet_bi: loaiFilter,
            trang_thai: value 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (id) => {
        router.delete(`/thiet-bi/${id}`, {
            onSuccess: () => {
                // success toast hiển thị qua flash ở MainLayout
            },
            onError: () => {
                message.error('Có lỗi xảy ra khi xóa thiết bị!');
            },
        });
    };

    const handleReset = () => {
        setSearchText('');
        setPhongFilter('');
        setLoaiFilter('');
        setTrangThaiFilter('');
        router.get('/thiet-bi');
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value);
    };

    const getLoaiThietBiLabel = (loai) => {
        const labels = {
            'van_phong': 'Văn phòng',
            'day_hoc': 'Dạy học',
            'thi_nghiem': 'Thí nghiệm',
            'thuc_hanh': 'Thực hành',
        };
        return labels[loai] || loai;
    };

    const getLoaiThietBiColor = (loai) => {
        const colors = {
            'van_phong': 'blue',
            'day_hoc': 'green',
            'thi_nghiem': 'purple',
            'thuc_hanh': 'orange',
        };
        return colors[loai] || 'default';
    };

    const getTrangThaiLabel = (trangThai) => {
        const labels = {
            'tot': 'Tốt',
            'can_sua_chua': 'Cần sửa chữa',
            'hu_hong': 'Hư hỏng',
        };
        return labels[trangThai] || trangThai;
    };

    const getTrangThaiColor = (trangThai) => {
        const colors = {
            'tot': 'green',
            'can_sua_chua': 'orange',
            'hu_hong': 'red',
        };
        return colors[trangThai] || 'default';
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 60,
            fixed: 'left',
            align: 'center',
            render: (text, record, index) => (thietBis.current_page - 1) * thietBis.per_page + index + 1,
        },
        {
            title: 'Mã TB',
            dataIndex: 'ma_thiet_bi',
            key: 'ma_thiet_bi',
            width: 100,
            fixed: 'left',
        },
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
            title: 'Phòng',
            dataIndex: ['phong', 'ten_phong'],
            key: 'phong',
            width: 150,
            ellipsis: true,
            render: (text) => text || <Tag>Chưa phân bổ</Tag>,
        },
        {
            title: 'Khu nhà',
            dataIndex: ['phong', 'khu_nha', 'ten_khu_nha'],
            key: 'khu_nha',
            width: 150,
            ellipsis: true,
        },
        {
            title: 'Loại TB',
            dataIndex: 'loai_thiet_bi',
            key: 'loai_thiet_bi',
            width: 110,
            render: (loai) => (
                <Tag color={getLoaiThietBiColor(loai)}>
                    {getLoaiThietBiLabel(loai)}
                </Tag>
            ),
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
            width: 100,
            ellipsis: true,
        },
        {
            title: 'Năm mua',
            dataIndex: 'nam_mua',
            key: 'nam_mua',
            width: 80,
            align: 'center',
        },
        {
            title: 'SL',
            dataIndex: 'so_luong',
            key: 'so_luong',
            width: 50,
            align: 'center',
        },
        {
            title: 'Đơn vị',
            dataIndex: 'don_vi_tinh',
            key: 'don_vi_tinh',
            width: 70,
            align: 'center',
        },
        {
            title: 'Giá trị',
            dataIndex: 'gia_tri',
            key: 'gia_tri',
            width: 110,
            align: 'right',
            render: (value) => formatCurrency(value),
        },
        {
            title: 'Tổng giá trị',
            key: 'tong_gia_tri',
            width: 120,
            align: 'right',
            render: (_, record) => formatCurrency(record.gia_tri * record.so_luong),
        },
        {
            title: 'Trạng thái',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            width: 120,
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
                    <Link href={`/thiet-bi/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    <Popconfirm
                        title="Xác nhận xóa"
                        description="Bạn có chắc chắn muốn xóa thiết bị này?"
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
                            <h2 style={{ margin: 0 }}>Quản lý thiết bị</h2>
                        </Col>
                        <Col>
                            <Link href="/thiet-bi/create">
                                <Button type="primary" icon={<PlusOutlined />} size="large">
                                    Thêm thiết bị
                                </Button>
                            </Link>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    <Row gutter={[16, 16]}>
                        <Col xs={24} sm={12} md={6}>
                            <Search
                                placeholder="Tìm kiếm theo mã, tên, hãng..."
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
                                placeholder="Lọc theo phòng"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                showSearch
                                optionFilterProp="label"
                                value={phongFilter || undefined}
                                onChange={handlePhongFilter}
                                options={phongs.map(p => ({ 
                                    value: p.id, 
                                    label: `${p.ten_phong} - ${p.khu_nha.ten_khu_nha}` 
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
                                    { value: 'van_phong', label: 'Văn phòng' },
                                    { value: 'day_hoc', label: 'Dạy học' },
                                    { value: 'thi_nghiem', label: 'Thí nghiệm' },
                                    { value: 'thuc_hanh', label: 'Thực hành' },
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
                                    { value: 'tot', label: 'Tốt' },
                                    { value: 'can_sua_chua', label: 'Cần sửa chữa' },
                                    { value: 'hu_hong', label: 'Hư hỏng' },
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
                            dataSource={thietBis.data}
                        rowKey="id"
                        scroll={{ x: 1700 }}
                        pagination={{
                            current: thietBis.current_page,
                            pageSize: thietBis.per_page,
                            total: thietBis.total,
                            showSizeChanger: true,
                            showTotal: (total) => `Tổng số ${total} thiết bị`,
                            onChange: (page, pageSize) => {
                                router.get('/thiet-bi', {
                                    page,
                                    per_page: pageSize,
                                    search: searchText,
                                    phong_id: phongFilter,
                                    loai_thiet_bi: loaiFilter,
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

