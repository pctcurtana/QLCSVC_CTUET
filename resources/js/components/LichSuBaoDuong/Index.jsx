import React, { useState, useEffect } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Table, Button, Space, Card, Row, Col, Popconfirm, message, Select, Tag, Skeleton } from 'antd';
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ReloadOutlined,
} from '@ant-design/icons';
import { Link, router } from '@inertiajs/react';
import dayjs from 'dayjs';

const Index = ({ lichSuBaoDuongs, thietBis, filters }) => {
    const [thietBiFilter, setThietBiFilter] = useState(filters.thiet_bi_id || '');
    const [loaiFilter, setLoaiFilter] = useState(filters.loai_bao_duong || '');
    const [trangThaiFilter, setTrangThaiFilter] = useState(filters.trang_thai || '');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(false);
    }, [lichSuBaoDuongs]);

    const handleThietBiFilter = (value) => {
        setThietBiFilter(value);
        router.get('/lich-su-bao-duong', { 
            thiet_bi_id: value,
            loai_bao_duong: loaiFilter,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleLoaiFilter = (value) => {
        setLoaiFilter(value);
        router.get('/lich-su-bao-duong', { 
            thiet_bi_id: thietBiFilter,
            loai_bao_duong: value,
            trang_thai: trangThaiFilter 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleTrangThaiFilter = (value) => {
        setTrangThaiFilter(value);
        router.get('/lich-su-bao-duong', { 
            thiet_bi_id: thietBiFilter,
            loai_bao_duong: loaiFilter,
            trang_thai: value 
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = (id) => {
        router.delete(`/lich-su-bao-duong/${id}`, {
            onSuccess: () => {
                // success toast hiển thị qua flash ở MainLayout
            },
            onError: () => {
                message.error('Có lỗi xảy ra khi xóa lịch sử bảo dưỡng!');
            },
        });
    };

    const handleReset = () => {
        setThietBiFilter('');
        setLoaiFilter('');
        setTrangThaiFilter('');
        router.get('/lich-su-bao-duong');
    };

    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(value);
    };

    const getLoaiBaoDuongLabel = (loai) => {
        const labels = {
            'dinh_ky': 'Định kỳ',
            'sua_chua': 'Sửa chữa',
            'thay_the': 'Thay thế',
        };
        return labels[loai] || loai;
    };

    const getLoaiBaoDuongColor = (loai) => {
        const colors = {
            'dinh_ky': 'blue',
            'sua_chua': 'orange',
            'thay_the': 'red',
        };
        return colors[loai] || 'default';
    };

    const getTrangThaiLabel = (trangThai) => {
        const labels = {
            'hoan_thanh': 'Hoàn thành',
            'dang_thuc_hien': 'Đang thực hiện',
            'chua_thuc_hien': 'Chưa thực hiện',
        };
        return labels[trangThai] || trangThai;
    };

    const getTrangThaiColor = (trangThai) => {
        const colors = {
            'hoan_thanh': 'green',
            'dang_thuc_hien': 'blue',
            'chua_thuc_hien': 'orange',
        };
        return colors[trangThai] || 'default';
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 60,
            render: (text, record, index) => (lichSuBaoDuongs.current_page - 1) * lichSuBaoDuongs.per_page + index + 1,
        },
        {
            title: 'Ngày BĐ',
            dataIndex: 'ngay_bao_duong',
            key: 'ngay_bao_duong',
            width: 100,
            render: (date) => dayjs(date).format('DD/MM/YYYY'),
        },
        {
            title: 'Thiết bị',
            dataIndex: ['thiet_bi', 'ten_thiet_bi'],
            key: 'thiet_bi',
            width: 180,
            ellipsis: true,
            render: (text, record) => (
                <div>
                    <strong>{text}</strong>
                    <br />
                    <small style={{ color: '#888' }}>{record.thiet_bi?.ma_thiet_bi}</small>
                </div>
            ),
        },
        {
            title: 'Phòng',
            dataIndex: ['thiet_bi', 'phong', 'ten_phong'],
            key: 'phong',
            width: 140,
            ellipsis: true,
        },
        {
            title: 'Loại BĐ',
            dataIndex: 'loai_bao_duong',
            key: 'loai_bao_duong',
            width: 100,
            render: (loai) => (
                <Tag color={getLoaiBaoDuongColor(loai)}>
                    {getLoaiBaoDuongLabel(loai)}
                </Tag>
            ),
        },
        {
            title: 'Nội dung',
            dataIndex: 'noi_dung',
            key: 'noi_dung',
            width: 200,
            ellipsis: true,
        },
        {
            title: 'Chi phí',
            dataIndex: 'chi_phi',
            key: 'chi_phi',
            width: 110,
            align: 'right',
            render: (value) => value ? formatCurrency(value) : '-',
        },
        {
            title: 'Người thực hiện',
            dataIndex: 'nguoi_thuc_hien',
            key: 'nguoi_thuc_hien',
            width: 130,
            ellipsis: true,
        },
        {
            title: 'Đơn vị',
            dataIndex: 'don_vi_thuc_hien',
            key: 'don_vi_thuc_hien',
            width: 140,
            ellipsis: true,
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
                    <Link href={`/lich-su-bao-duong/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    <Popconfirm
                        title="Xác nhận xóa"
                        description="Bạn có chắc chắn muốn xóa lịch sử này?"
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
                            <h2 style={{ margin: 0 }}>Lịch sử bảo dưỡng thiết bị</h2>
                        </Col>
                        <Col>
                            <Link href="/lich-su-bao-duong/create">
                                <Button type="primary" icon={<PlusOutlined />} size="large">
                                    Thêm lịch sử
                                </Button>
                            </Link>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    <Row gutter={[16, 16]}>
                        <Col xs={24} sm={12} md={6}>
                            <Select
                                placeholder="Lọc theo thiết bị"
                                size="large"
                                style={{ width: '100%' }}
                                allowClear
                                showSearch
                                optionFilterProp="label"
                                value={thietBiFilter || undefined}
                                onChange={handleThietBiFilter}
                                options={thietBis.map(tb => ({ 
                                    value: tb.id, 
                                    label: `${tb.ma_thiet_bi} - ${tb.ten_thiet_bi}` 
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
                                    { value: 'dinh_ky', label: 'Định kỳ' },
                                    { value: 'sua_chua', label: 'Sửa chữa' },
                                    { value: 'thay_the', label: 'Thay thế' },
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
                                    { value: 'hoan_thanh', label: 'Hoàn thành' },
                                    { value: 'dang_thuc_hien', label: 'Đang thực hiện' },
                                    { value: 'chua_thuc_hien', label: 'Chưa thực hiện' },
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
                            dataSource={lichSuBaoDuongs.data}
                        rowKey="id"
                        scroll={{ x: 1550 }}
                        pagination={{
                            current: lichSuBaoDuongs.current_page,
                            pageSize: lichSuBaoDuongs.per_page,
                            total: lichSuBaoDuongs.total,
                            showSizeChanger: true,
                            showTotal: (total) => `Tổng số ${total} lịch sử`,
                            onChange: (page, pageSize) => {
                                router.get('/lich-su-bao-duong', {
                                    page,
                                    per_page: pageSize,
                                    thiet_bi_id: thietBiFilter,
                                    loai_bao_duong: loaiFilter,
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

