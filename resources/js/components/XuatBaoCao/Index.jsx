import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card, Table, Button, Space, Typography, Tag, Modal, Form, Input,
    Popconfirm, Row, Col, Empty, Tabs, Dropdown,
} from 'antd';
import {
    PlusOutlined, FileExcelOutlined, SyncOutlined, EyeOutlined,
    DeleteOutlined, CheckCircleOutlined, ClockCircleOutlined,
    FileTextOutlined, DownloadOutlined, CloseCircleOutlined,
    HomeOutlined, BankOutlined, ToolOutlined, GlobalOutlined,
} from '@ant-design/icons';
import usePermission from '../../hooks/usePermission';

const { Title, Text } = Typography;
const { TextArea } = Input;

const XuatBaoCaoIndex = ({ dotBaoCaos, previewData, selectedId }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [form] = Form.useForm();
    const [loading, setLoading] = useState(false);
    const perm = usePermission('xuat-bao-cao');

    const fmt = (v) => new Intl.NumberFormat('vi-VN').format(v || 0);
    const fmtDec = (v) => new Intl.NumberFormat('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0);

    const handleCreate = () => {
        form.validateFields().then((values) => {
            setLoading(true);
            router.post('/xuat-bao-cao', values, {
                onSuccess: () => {
                    setIsModalOpen(false);
                    form.resetFields();
                },
                onFinish: () => setLoading(false),
            });
        });
    };

    const handleTongHop = (id) => {
        router.post(`/xuat-bao-cao/${id}/tong-hop`);
    };

    const handleDelete = (id) => {
        router.delete(`/xuat-bao-cao/${id}`);
    };

    const handlePreview = (id) => {
        router.get('/xuat-bao-cao', { preview: id }, { preserveState: true });
    };

    const handleClosePreview = () => {
        router.get('/xuat-bao-cao', {}, { preserveState: true });
    };

    const handleExport = (loai) => {
        if (selectedId) {
            window.location.href = `/xuat-bao-cao/${selectedId}/export?loai=${loai}`;
        }
    };

    const exportItems = [
        { key: 'all', label: 'Tất cả biểu mẫu' },
        { type: 'divider' },
        { key: 'loai_phong', label: 'Phục vụ tuyển sinh' },
        { key: 'tieu_chuan', label: 'Tiêu chuẩn cơ sở vật chất' },
        { key: 'khuon_vien', label: 'Khuôn viên trụ sở và các phân hiệu' },
        { key: 'cong_trinh', label: 'Công trình đào tạo' },
        { key: 'ha_tang', label: 'Hạ tầng công nghệ thông tin' },
    ];

    const columns = [
        {
            title: 'Tên đợt báo cáo',
            dataIndex: 'ten_dot',
            key: 'ten_dot',
            render: (text, record) => (
                <Space direction="vertical" size={0}>
                    <Text strong style={{ fontSize: 14 }}>{text}</Text>
                    {record.nam_hoc && <Text type="secondary" style={{ fontSize: 12 }}>Năm học: {record.nam_hoc}</Text>}
                </Space>
            ),
        },
        {
            title: 'Ngày tổng hợp',
            dataIndex: 'ngay_tong_hop',
            key: 'ngay_tong_hop',
            width: 120,
            align: 'center',
            render: (text) => text || <Text type="secondary">—</Text>,
        },
        {
            title: 'Trạng thái',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            width: 120,
            align: 'center',
            render: (status) => (
                status === 'completed' ? (
                    <Tag icon={<CheckCircleOutlined />} color="success">Hoàn thành</Tag>
                ) : (
                    <Tag icon={<ClockCircleOutlined />} color="default">Nháp</Tag>
                )
            ),
        },
        {
            title: 'Thao tác',
            key: 'action',
            width: 250,
            align: 'center',
            render: (_, record) => (
                <Space size="small">
                    {perm.can_create && (
                        <Popconfirm
                            title="Tổng hợp báo cáo?"
                            description="Dữ liệu sẽ được cập nhật từ hệ thống."
                            onConfirm={() => handleTongHop(record.id)}
                            okText="Tổng hợp"
                            cancelText="Hủy"
                        >
                            <Button size="small" icon={<SyncOutlined />} type="primary" ghost>
                                Tổng hợp
                            </Button>
                        </Popconfirm>
                    )}

                    {record.trang_thai === 'completed' && (
                        <Button
                            size="small"
                            icon={<EyeOutlined />}
                            type={selectedId === record.id ? 'primary' : 'default'}
                            onClick={() => selectedId === record.id ? handleClosePreview() : handlePreview(record.id)}
                        >
                            {selectedId === record.id ? 'Đóng' : 'Xem'}
                        </Button>
                    )}

                    {perm.can_delete && (
                        <Popconfirm
                            title="Xóa đợt báo cáo?"
                            onConfirm={() => handleDelete(record.id)}
                            okText="Xóa"
                            cancelText="Hủy"
                            okButtonProps={{ danger: true }}
                        >
                            <Button size="small" danger icon={<DeleteOutlined />} />
                        </Popconfirm>
                    )}
                </Space>
            ),
        },
    ];

    // Columns cho preview tables
    const loaiPhongColumns = [
        { title: 'STT', dataIndex: 'stt', key: 'stt', width: 60, align: 'center' },
        {
            title: 'Loại Phòng', dataIndex: 'loai_phong', key: 'loai_phong',
            render: (text, record) => record.is_tong ? <Text strong>{text}</Text> : text,
        },
        {
            title: 'Số lượng', dataIndex: 'so_luong', key: 'so_luong', width: 100, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmt(v)}</Text> : fmt(v),
        },
        {
            title: 'Diện tích (m²)', dataIndex: 'dien_tich', key: 'dien_tich', width: 130, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmtDec(v)}</Text> : fmtDec(v),
        },
    ];

    const tieuChuanColumns = [
        { title: 'Mã', dataIndex: 'ma_chi_so', key: 'ma_chi_so', width: 70, align: 'center' },
        { title: 'Chỉ số đánh giá', dataIndex: 'chi_so_danh_gia', key: 'chi_so_danh_gia' },
        { title: 'Ngưỡng', dataIndex: 'nguong', key: 'nguong', width: 90, align: 'center', render: (v) => v ?? '' },
        { title: 'Thực tế', dataIndex: 'thuc_te', key: 'thuc_te', width: 100, align: 'center', render: (v) => v ?? '' },
        {
            title: 'Kết quả', dataIndex: 'ket_qua', key: 'ket_qua', width: 110, align: 'center',
            render: (v) => v ?? ''
        },
        { title: 'Giải trình', dataIndex: 'giai_trinh', key: 'giai_trinh', width: 180, render: (v) => v ?? '' },
    ];

    const khuonVienColumns = [
        { title: 'STT', dataIndex: 'thu_tu', key: 'thu_tu', width: 50, align: 'center', render: (v, record) => record.is_tong ? '' : v },
        {
            title: 'Khuôn viên', dataIndex: 'ten_khuon_vien', key: 'ten_khuon_vien', width: 250,
            render: (text, record) => record.is_tong ? <Text strong>{text}</Text> : text,
        },
        { title: 'Ký hiệu', dataIndex: 'ky_hieu', key: 'ky_hieu', width: 120 },
        { title: 'Hình thức', dataIndex: 'hinh_thuc_su_dung', key: 'hinh_thuc_su_dung', width: 100, align: 'center' },
        {
            title: 'DT đất (m²)', dataIndex: 'dien_tich_dat', key: 'dien_tich_dat', width: 120, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmtDec(v)}</Text> : fmtDec(v),
        },
        { title: 'Vị trí khuôn viên', dataIndex: 'vi_tri_khuon_vien', key: 'vi_tri_khuon_vien', width: 120, align: 'center' },
        {
            title: 'DT quy đổi (m²)', dataIndex: 'dien_tich_quy_doi', key: 'dien_tich_quy_doi', width: 130, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmtDec(v)}</Text> : fmtDec(v),
        },
        { title: 'Địa chỉ', dataIndex: 'dia_chi', key: 'dia_chi', render: (v) => v },
    ];

    const congTrinhColumns = [
        { title: 'STT', dataIndex: 'stt', key: 'stt', width: 50, align: 'center', render: (v, record) => record.is_tong ? '' : v },
        {
            title: 'Công trình', dataIndex: 'ten_cong_trinh', key: 'ten_cong_trinh',
            render: (text, record) => record.is_tong ? <Text strong>{text}</Text> : text,
        },
        { title: 'Ký hiệu', dataIndex: 'ky_hieu', key: 'ky_hieu', width: 120 },

        {
            title: 'Tổng DT sàn', dataIndex: 'tong_dien_tich_san', key: 'tong_dien_tich_san', width: 120, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmtDec(v)}</Text> : fmtDec(v),
        },
        { title: 'Hệ số sử dụng cho đào tạo', dataIndex: 'he_so_dien_tich', key: 'he_so_dien_tich', width: 120, align: 'center' },
        {
            title: 'DT đào tạo', dataIndex: 'dien_tich_san_dao_tao', key: 'dien_tich_san_dao_tao', width: 120, align: 'right',
            render: (v, record) => record.is_tong ? <Text strong>{fmtDec(v)}</Text> : fmtDec(v),
        },
        { title: 'Địa chỉ', dataIndex: 'dia_chi', key: 'dia_chi', width: 250, render: (v) => v },
    ];

    const haTangColumns = [
        { title: 'STT', dataIndex: 'stt', key: 'stt', width: 50, align: 'center' },
        { title: 'Chỉ số thống kê', dataIndex: 'chi_so_thong_ke', key: 'chi_so_thong_ke' },
        { title: 'Giá trị', dataIndex: 'gia_tri', key: 'gia_tri', width: 100, align: 'right', render: (v) => v > 0 ? fmt(v) : '' },
        { title: 'Ghi chú', dataIndex: 'ghi_chu', key: 'ghi_chu', width: 200, render: (v) => v ?? '' },
    ];

    const tabItems = previewData ? [
        {
            key: '1',
            label: <span><HomeOutlined style={{ marginRight: 6 }} />Phục vụ tuyển sinh</span>,
            children: <Table columns={loaiPhongColumns} dataSource={previewData.bcLoaiPhongs} rowKey="id" pagination={false} size="small" bordered rowClassName={(r) => r.is_tong ? 'total-row' : ''} />,
        },
        {
            key: '2',
            label: <span><CheckCircleOutlined style={{ marginRight: 6 }} />Tiêu chuẩn cơ sở vật chất</span>,
            children: <Table columns={tieuChuanColumns} dataSource={previewData.bcTieuChuanCsvcs} rowKey="id" pagination={false} size="small" bordered />,
        },
        {
            key: '3',
            label: <span><BankOutlined style={{ marginRight: 6 }} />Khuôn viên trụ sở và các phân hiệu</span>,
            children: <Table columns={khuonVienColumns} dataSource={previewData.bcKhuonViens} rowKey="id" pagination={false} size="small" bordered rowClassName={(r) => r.is_tong ? 'total-row' : ''} />,
        },
        {
            key: '4',
            label: <span><ToolOutlined style={{ marginRight: 6 }} />Công trình đào tạo</span>,
            children: <Table columns={congTrinhColumns} dataSource={previewData.bcCongTrinhDaoTaos} rowKey="id" pagination={false} size="small" bordered rowClassName={(r) => r.is_tong ? 'total-row' : ''} />,
        },
        {
            key: '5',
            label: <span><GlobalOutlined style={{ marginRight: 6 }} />Hạ tầng công nghệ thông tin</span>,
            children: <Table columns={haTangColumns} dataSource={previewData.bcHaTangCntts} rowKey="id" pagination={false} size="small" bordered />,
        },
    ] : [];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <Row justify="space-between" align="middle">
                    <Col>
                        <Title level={2} style={{ margin: 0 }}>
                            <FileTextOutlined style={{ marginRight: 12, color: '#1890ff' }} />
                            Xuất báo cáo theo mẫu của Bộ GD&ĐT
                        </Title>
                    </Col>
                    <Col>
                        {perm.can_create && (
                            <Button type="primary" icon={<PlusOutlined />} size="large" onClick={() => setIsModalOpen(true)}>
                                Tạo đợt mới
                            </Button>
                        )}
                    </Col>
                </Row>

                <Card style={{ borderRadius: 12 }} bodyStyle={{ padding: 0 }}>
                    <Table
                        columns={columns}
                        dataSource={dotBaoCaos}
                        rowKey="id"
                        pagination={false}
                        size="middle"
                        locale={{ emptyText: <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Chưa có đợt báo cáo nào" /> }}
                    />
                </Card>

                {previewData && (
                    <Card
                        title={
                            <Space>
                                <FileExcelOutlined style={{ color: '#52c41a' }} />
                                <span>Preview: {previewData.ten_dot}</span>
                            </Space>
                        }
                        extra={
                            perm.can_export ? (
                                <Dropdown menu={{ items: exportItems, onClick: ({ key }) => handleExport(key) }} placement="bottomRight">
                                    <Button
                                        size="large"
                                        style={{
                                            background: '#217346',
                                            borderColor: '#217346',
                                            color: '#fff',
                                            fontWeight: 600,
                                        }}
                                        icon={<FileExcelOutlined />}
                                    >
                                        Xuất Excel
                                    </Button>
                                </Dropdown>
                            ) : null
                        }
                        style={{ borderRadius: 12 }}
                    >
                        <Tabs items={tabItems} />
                    </Card>
                )}

                <Card style={{ borderRadius: 12, background: '#f6ffed', border: '1px solid #b7eb8f' }}>
                    <Title level={5} style={{ margin: 0, marginBottom: 8 }}>
                        <FileExcelOutlined style={{ color: '#52c41a', marginRight: 8 }} />
                        Hướng dẫn
                    </Title>
                    <ol style={{ margin: 0, paddingLeft: 20, color: '#555' }}>
                        <li>Tạo đợt báo cáo mới → Nhấn <Text code>Tổng hợp</Text> để trích xuất dữ liệu</li>
                        <li>Nhấn <Text code>Xem</Text> để preview biểu mẫu → <Text code>Xuất Excel</Text> để tải file</li>
                    </ol>
                </Card>
            </Space>

            <Modal
                title="Tạo đợt báo cáo mới"
                open={isModalOpen}
                onCancel={() => { setIsModalOpen(false); form.resetFields(); }}
                onOk={handleCreate}
                confirmLoading={loading}
                okText="Tạo"
                cancelText="Hủy"
            >
                <Form form={form} layout="vertical" style={{ marginTop: 16 }}>
                    <Form.Item name="ten_dot" label="Tên đợt báo cáo" rules={[{ required: true, message: 'Vui lòng nhập tên đợt' }]}>
                        <Input placeholder="VD: Báo cáo CSVC Học kỳ 1 năm 2024-2025" />
                    </Form.Item>
                    <Form.Item name="nam_hoc" label="Năm học">
                        <Input placeholder="VD: 2024-2025" />
                    </Form.Item>
                    <Form.Item name="mo_ta" label="Mô tả">
                        <TextArea rows={2} placeholder="Ghi chú..." />
                    </Form.Item>
                </Form>
            </Modal>

            <style>{`
                .total-row { background-color: #fafafa; font-weight: 600; }
                .total-row:hover > td { background-color: #f0f0f0 !important; }
            `}</style>
        </MainLayout>
    );
};

export default XuatBaoCaoIndex;
