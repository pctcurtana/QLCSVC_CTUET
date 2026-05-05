import React, { useState } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Head, router } from '@inertiajs/react';
import {
    Badge,
    Button,
    Card,
    Col,
    DatePicker,
    Form,
    Input,
    Modal,
    Popconfirm,
    Row,
    Space,
    Statistic,
    Switch,
    Table,
    Typography,
} from 'antd';
import {
    CheckCircleOutlined,
    PlusOutlined,
    ReloadOutlined,
    ToolOutlined,
    DeleteOutlined,
    CalendarOutlined,
} from '@ant-design/icons';
import dayjs from 'dayjs';
import usePermission from '../../hooks/usePermission';

const { Title, Text } = Typography;
const { TextArea } = Input;

const DotKiemTraThietBiIndex = ({ dotKiemTras, filters, stats }) => {
    const perm = usePermission('dot-kiem-tra-thiet-bi');
    const [openCreateModal, setOpenCreateModal] = useState(false);
    const [search, setSearch] = useState(filters?.search || '');
    const [form] = Form.useForm();

    const submitCreate = (values) => {
        router.post('/dot-kiem-tra-thiet-bi', {
            ...values,
            ngay_bat_dau: values.ngay_bat_dau ? values.ngay_bat_dau.format('YYYY-MM-DD') : null,
            ngay_ket_thuc: values.ngay_ket_thuc ? values.ngay_ket_thuc.format('YYYY-MM-DD') : null,
            is_active: !!values.is_active,
        }, {
            onSuccess: () => {
                setOpenCreateModal(false);
                form.resetFields();
            },
        });
    };

    const activateDot = (id) => {
        router.post(`/dot-kiem-tra-thiet-bi/${id}/activate`);
    };

    const deleteDot = (id) => {
        router.delete(`/dot-kiem-tra-thiet-bi/${id}`);
    };

    const doSearch = () => {
        router.get('/dot-kiem-tra-thiet-bi', { search }, { preserveState: true, replace: true });
    };

    const resetFilters = () => {
        setSearch('');
        router.get('/dot-kiem-tra-thiet-bi');
    };

    const columns = [
        {
            title: 'STT',
            key: 'index',
            width: 70,
            align: 'center',
            render: (_, __, index) => (dotKiemTras.current_page - 1) * dotKiemTras.per_page + index + 1,
        },
        {
            title: 'Tên đợt',
            dataIndex: 'ten_dot',
            key: 'ten_dot',
            render: (_, record) => <Text strong>{record.ten_dot}</Text>,
        },
        {
            title: 'Thời gian',
            key: 'thoi_gian',
            render: (_, record) => {
                const from = record.ngay_bat_dau ? dayjs(record.ngay_bat_dau).format('DD/MM/YYYY') : 'Chưa đặt';
                const to = record.ngay_ket_thuc ? dayjs(record.ngay_ket_thuc).format('DD/MM/YYYY') : 'Chưa đặt';
                return <Text>{from} - {to}</Text>;
            },
        },
        {
            title: 'Trạng thái',
            key: 'is_active',
            width: 160,
            align: 'center',
            render: (_, record) => record.is_active
                ? <Badge status="success" text="Active" />
                : <Badge status="default" text="Không active" />,
        },
        {
            title: 'Thao tác',
            key: 'action',
            width: 220,
            align: 'center',
            render: (_, record) => (
                <Space>
                    {perm.can_edit && !record.is_active && (
                        <Button type="primary" ghost onClick={() => activateDot(record.id)}>
                            Kích hoạt
                        </Button>
                    )}
                    {perm.can_delete && (
                        <Popconfirm
                            title="Xóa đợt kiểm tra này?"
                            onConfirm={() => deleteDot(record.id)}
                            okText="Xóa"
                            cancelText="Hủy"
                        >
                            <Button danger icon={<DeleteOutlined />} disabled={record.is_active} />
                        </Popconfirm>
                    )}
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Đợt kiểm tra thiết bị" />
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <Row gutter={16}>
                    <Col xs={24} sm={8}>
                        <Card><Statistic title="Tổng đợt" value={stats?.tong ?? 0} prefix={<CalendarOutlined />} /></Card>
                    </Col>
                    <Col xs={24} sm={8}>
                        <Card><Statistic title="Đang active" value={stats?.dang_active ?? 0} valueStyle={{ color: '#52c41a' }} prefix={<CheckCircleOutlined />} /></Card>
                    </Col>
                    <Col xs={24} sm={8}>
                        <Card><Statistic title="Chưa active" value={stats?.chua_active ?? 0} valueStyle={{ color: '#fa8c16' }} prefix={<ToolOutlined />} /></Card>
                    </Col>
                </Row>

                <Card>
                    <Row gutter={[16, 16]} align="middle">
                        <Col flex="auto">
                            <Space>
                                <ToolOutlined style={{ fontSize: 20 }} />
                                <Title level={4} style={{ margin: 0 }}>Đợt kiểm tra thiết bị</Title>
                            </Space>
                        </Col>
                        <Col>
                            <Space>
                                <Input
                                    placeholder="Tìm theo tên đợt"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onPressEnter={doSearch}
                                    allowClear
                                    style={{ width: 280 }}
                                />
                                <Button type="primary" onClick={doSearch}>Lọc</Button>
                                <Button icon={<ReloadOutlined />} onClick={resetFilters}>Làm mới</Button>
                                {perm.can_create && (
                                    <Button type="primary" icon={<PlusOutlined />} onClick={() => setOpenCreateModal(true)}>
                                        Tạo đợt
                                    </Button>
                                )}
                            </Space>
                        </Col>
                    </Row>
                </Card>

                <Card>
                    <Table
                        rowKey="id"
                        columns={columns}
                        dataSource={dotKiemTras.data}
                        pagination={{
                            current: dotKiemTras.current_page,
                            pageSize: dotKiemTras.per_page,
                            total: dotKiemTras.total,
                            showTotal: (total) => `Tổng ${total} đợt`,
                            onChange: (page) => router.get('/dot-kiem-tra-thiet-bi', { ...filters, page }, { preserveState: true, replace: true }),
                        }}
                    />
                </Card>
            </Space>

            <Modal
                title="Tạo đợt kiểm tra thiết bị"
                open={openCreateModal}
                onCancel={() => setOpenCreateModal(false)}
                onOk={() => form.submit()}
                okText="Lưu"
                cancelText="Hủy"
            >
                <Form layout="vertical" form={form} onFinish={submitCreate}>
                    <Form.Item label="Tên đợt" name="ten_dot" rules={[{ required: true, message: 'Vui lòng nhập tên đợt' }]}>
                        <Input placeholder="Ví dụ: Đợt kiểm tra quý 2/2026" />
                    </Form.Item>
                    <Row gutter={12}>
                        <Col span={12}>
                            <Form.Item label="Ngày bắt đầu" name="ngay_bat_dau">
                                <DatePicker style={{ width: '100%' }} format="DD/MM/YYYY" />
                            </Form.Item>
                        </Col>
                        <Col span={12}>
                            <Form.Item label="Ngày kết thúc" name="ngay_ket_thuc">
                                <DatePicker style={{ width: '100%' }} format="DD/MM/YYYY" />
                            </Form.Item>
                        </Col>
                    </Row>
                    <Form.Item label="Mô tả" name="mo_ta">
                        <TextArea rows={3} placeholder="Mô tả thêm (nếu có)" />
                    </Form.Item>
                    <Form.Item label="Kích hoạt ngay" name="is_active" valuePropName="checked" initialValue={false}>
                        <Switch checkedChildren="Active" unCheckedChildren="Chưa active" />
                    </Form.Item>
                </Form>
            </Modal>
        </MainLayout>
    );
};

export default DotKiemTraThietBiIndex;
