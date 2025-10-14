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

const Index = ({ coSos, filters }) => {
    const [searchText, setSearchText] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.trang_thai || '');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [coSos]);

    const handleSearch = (value) => {
        router.get('/co-so', { search: value, trang_thai: statusFilter }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStatusFilter = (value) => {
        setStatusFilter(value);
        router.get('/co-so', { search: searchText, trang_thai: value }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (id) => {
        router.delete(`/co-so/${id}`, {
            onSuccess: () => {
                // success toast hiển thị qua flash ở MainLayout
            },
            onError: () => {
                message.error('Có lỗi xảy ra khi xóa cơ sở!');
            },
        });
    };

    const handleReset = () => {
        setSearchText('');
        setStatusFilter('');
        router.get('/co-so');
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 60,
            render: (text, record, index) => (coSos.current_page - 1) * coSos.per_page + index + 1,
        },
        {
            title: 'Mã cơ sở',
            dataIndex: 'ma_co_so',
            key: 'ma_co_so',
            width: 90,
        },
        {
            title: 'Tên cơ sở',
            dataIndex: 'ten_co_so',
            key: 'ten_co_so',
            width: 200,
            render: (text) => <strong>{text}</strong>,
        },
        {
            title: 'Địa chỉ',
            dataIndex: 'dia_chi',
            key: 'dia_chi',
            width: 200,
            ellipsis: true,
        },
        {
            title: 'Tổng diện tích (m²)',
            dataIndex: 'tong_dien_tich',
            key: 'tong_dien_tich',
            align: 'right',
            width: 110,

            render: (value) => formatNumber(value),
        },
        {
            title: 'DT sân xây dựng (m²)',
            dataIndex: 'dien_tich_san_xay_dung',
            key: 'dien_tich_san_xay_dung',
            align: 'right',
            width: 110,

            render: (value) => formatNumber(value),
        },
        {
            title: 'DT còn lại (m²)',
            dataIndex: 'dien_tich_con_lai',
            key: 'dien_tich_con_lai',
            align: 'right',
            width: 110,

            render: (value) => formatNumber(value),
        },
        {
            title: 'Số khu nhà',
            dataIndex: 'khu_nhas_count',
            
            key: 'khu_nhas_count',
            align: 'center',
            width: 80,

            render: (value) => <Tag color="blue">{value}</Tag>,
        },
        {
            title: 'Trạng thái',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            align: 'center',
            width: 100,

            render: (status) => (
                <Tag color={status === 'active' ? 'green' : 'red'}>
                    {status === 'active' ? 'Hoạt động' : 'Không hoạt động'}
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
                    <Link href={`/co-so/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    <Popconfirm
                        title="Xác nhận xóa"
                        description="Bạn có chắc chắn muốn xóa cơ sở này?"
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
                            <h2 style={{ margin: 0 }}>Quản lý cơ sở hạ tầng</h2>
                        </Col>
                        <Col>
                            <Link href="/co-so/create">
                                <Button type="primary" icon={<PlusOutlined />} size="large">
                                    Thêm cơ sở
                                </Button>
                            </Link>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    <Row gutter={[16, 16]}>
                        <Col xs={24} sm={12} md={8}>
                            <Search
                                placeholder="Tìm kiếm theo mã, tên, địa chỉ..."
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
                                placeholder="Lọc theo trạng thái"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                value={statusFilter || undefined}
                                onChange={handleStatusFilter}
                                options={[
                                    { value: 'active', label: 'Hoạt động' },
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
                            dataSource={coSos.data}
                            rowKey="id"
                            scroll={{ x: 1400 }}
                            pagination={{
                            current: coSos.current_page,
                            pageSize: coSos.per_page,
                            total: coSos.total,
                            showSizeChanger: true,
                            showTotal: (total) => `Tổng số ${total} cơ sở`,
                            onChange: (page, pageSize) => {
                                router.get('/co-so', {
                                    page,
                                    per_page: pageSize,
                                    search: searchText,
                                    trang_thai: statusFilter,
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

