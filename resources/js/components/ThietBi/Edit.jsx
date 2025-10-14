import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, DatePicker, Divider, Table, Tag, Tabs } from 'antd';
import { SaveOutlined, RollbackOutlined, HistoryOutlined, PlusOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

const { TextArea } = Input;

const Edit = ({ thietBi, phongs }) => {
    const [form] = Form.useForm();

    // Format initial values for dates
    const initialValues = {
        ...thietBi,
        ngay_mua: thietBi.ngay_mua ? dayjs(thietBi.ngay_mua) : null,
        ngay_bao_duong_cuoi: thietBi.ngay_bao_duong_cuoi ? dayjs(thietBi.ngay_bao_duong_cuoi) : null,
        ngay_bao_duong_tiep_theo: thietBi.ngay_bao_duong_tiep_theo ? dayjs(thietBi.ngay_bao_duong_tiep_theo) : null,
    };

    const handleSubmit = (values) => {
        // Format dates
        const formattedValues = {
            ...values,
            ngay_mua: values.ngay_mua ? dayjs(values.ngay_mua).format('YYYY-MM-DD') : null,
            ngay_bao_duong_cuoi: values.ngay_bao_duong_cuoi ? dayjs(values.ngay_bao_duong_cuoi).format('YYYY-MM-DD') : null,
        };

        router.put(`/thiet-bi/${thietBi.id}`, formattedValues, {
            // success toast hiển thị qua flash ở MainLayout
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    message.error(errors[key]);
                });
            },
        });
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

    const lichSuColumns = [
        {
            title: 'Ngày bảo dưỡng',
            dataIndex: 'ngay_bao_duong',
            key: 'ngay_bao_duong',
            render: (date) => dayjs(date).format('DD/MM/YYYY'),
        },
        {
            title: 'Loại',
            dataIndex: 'loai_bao_duong',
            key: 'loai_bao_duong',
            render: (loai) => <Tag>{getLoaiBaoDuongLabel(loai)}</Tag>,
        },
        {
            title: 'Nội dung',
            dataIndex: 'noi_dung',
            key: 'noi_dung',
        },
        {
            title: 'Chi phí',
            dataIndex: 'chi_phi',
            key: 'chi_phi',
            render: (value) => formatCurrency(value),
        },
        {
            title: 'Người thực hiện',
            dataIndex: 'nguoi_thuc_hien',
            key: 'nguoi_thuc_hien',
        },
        {
            title: 'Trạng thái',
            dataIndex: 'trang_thai',
            key: 'trang_thai',
            render: (trangThai) => (
                <Tag color={getTrangThaiColor(trangThai)}>
                    {getTrangThaiLabel(trangThai)}
                </Tag>
            ),
        },
    ];

    // Tính số ngày đến bảo dưỡng tiếp theo
    const getNgayDenBaoDuong = () => {
        if (!thietBi.ngay_bao_duong_tiep_theo) return null;
        const days = dayjs(thietBi.ngay_bao_duong_tiep_theo).diff(dayjs(), 'day');
        return days;
    };

    const ngayDenBaoDuong = getNgayDenBaoDuong();
    const canBaoDuong = ngayDenBaoDuong !== null && ngayDenBaoDuong <= 0;

    const formContent = (
        <Form
            form={form}
            layout="vertical"
            onFinish={handleSubmit}
            initialValues={initialValues}
        >
            {canBaoDuong && (
                <Card 
                    style={{ marginBottom: 16, background: '#fff2e8', borderColor: '#ff7a45' }}
                >
                    <Space>
                        <HistoryOutlined style={{ fontSize: 20, color: '#ff7a45' }} />
                        <span style={{ fontWeight: 'bold', color: '#ff7a45' }}>
                            {ngayDenBaoDuong < 0 
                                ? `Đã quá hạn bảo dưỡng ${Math.abs(ngayDenBaoDuong)} ngày!`
                                : 'Đến hạn bảo dưỡng hôm nay!'
                            }
                        </span>
                        <Link href={`/lich-su-bao-duong/create?thiet_bi_id=${thietBi.id}`}>
                            <Button type="primary" icon={<PlusOutlined />}>
                                Tạo lịch bảo dưỡng
                            </Button>
                        </Link>
                    </Space>
                </Card>
            )}

            <Row gutter={16}>
                <Col xs={24} md={8}>
                    <Form.Item
                        label="Mã thiết bị"
                        name="ma_thiet_bi"
                        rules={[
                            { required: true, message: 'Vui lòng nhập mã thiết bị!' },
                        ]}
                    >
                        <Input placeholder="Ví dụ: TB001" size="large" />
                    </Form.Item>
                </Col>

                <Col xs={24} md={8}>
                    <Form.Item
                        label="Số Serial"
                        name="serial_number"
                    >
                        <Input placeholder="Nhập số serial (nếu có)" size="large" />
                    </Form.Item>
                </Col>

                <Col xs={24} md={8}>
                    <Form.Item
                        label="Tên thiết bị"
                        name="ten_thiet_bi"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tên thiết bị!' },
                        ]}
                    >
                        <Input placeholder="Nhập tên thiết bị" size="large" />
                    </Form.Item>
                </Col>
            </Row>

            <Row gutter={16}>
                <Col xs={24} md={12}>
                    <Form.Item
                        label="Phòng"
                        name="phong_id"
                    >
                        <Select 
                            size="large" 
                            placeholder="Chọn phòng (có thể bỏ trống)"
                            allowClear
                            showSearch
                            optionFilterProp="label"
                            options={phongs.map(p => ({ 
                                value: p.id, 
                                label: `${p.ten_phong} - ${p.khu_nha.ten_khu_nha}` 
                            }))}
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={12}>
                    <Form.Item
                        label="Loại thiết bị"
                        name="loai_thiet_bi"
                        rules={[
                            { required: true, message: 'Vui lòng chọn loại thiết bị!' },
                        ]}
                    >
                        <Select size="large" placeholder="Chọn loại thiết bị">
                            <Select.Option value="van_phong">Văn phòng</Select.Option>
                            <Select.Option value="day_hoc">Dạy học</Select.Option>
                            <Select.Option value="thi_nghiem">Thí nghiệm</Select.Option>
                            <Select.Option value="thuc_hanh">Thực hành</Select.Option>
                        </Select>
                    </Form.Item>
                </Col>
            </Row>

            <Row gutter={16}>
                <Col xs={24} md={12}>
                    <Form.Item
                        label="Hãng sản xuất"
                        name="hang_san_xuat"
                    >
                        <Input placeholder="Nhập hãng sản xuất" size="large" />
                    </Form.Item>
                </Col>

                <Col xs={24} md={12}>
                    <Form.Item
                        label="Model"
                        name="model"
                    >
                        <Input placeholder="Nhập model" size="large" />
                    </Form.Item>
                </Col>
            </Row>

            <Row gutter={16}>
                <Col xs={24} md={6}>
                    <Form.Item
                        label="Năm mua"
                        name="nam_mua"
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1900}
                            max={new Date().getFullYear() + 10}
                            placeholder="Nhập năm mua"
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={6}>
                    <Form.Item
                        label="Ngày mua"
                        name="ngay_mua"
                    >
                        <DatePicker 
                            style={{ width: '100%' }}
                            size="large"
                            placeholder="Chọn ngày mua"
                            format="DD/MM/YYYY"
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={6}>
                    <Form.Item
                        label="Số lượng"
                        name="so_luong"
                        rules={[
                            { required: true, message: 'Vui lòng nhập số lượng!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1}
                            placeholder="Nhập số lượng"
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={6}>
                    <Form.Item
                        label="Đơn vị tính"
                        name="don_vi_tinh"
                        rules={[
                            { required: true, message: 'Vui lòng nhập đơn vị tính!' },
                        ]}
                    >
                        <Input placeholder="Ví dụ: cái, bộ..." size="large" />
                    </Form.Item>
                </Col>
            </Row>

            <Form.Item
                label="Giá trị (VNĐ)"
                name="gia_tri"
                rules={[
                    { required: true, message: 'Vui lòng nhập giá trị!' },
                ]}
            >
                <InputNumber
                    style={{ width: '100%' }}
                    size="large"
                    min={0}
                    placeholder="Nhập giá trị"
                    formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                    parser={value => value.replace(/\$\s?|(,*)/g, '')}
                />
            </Form.Item>

            <Form.Item
                label="Thông số kỹ thuật"
                name="thong_so_ky_thuat"
            >
                <TextArea rows={3} placeholder="Nhập thông số kỹ thuật" />
            </Form.Item>

            <Form.Item
                label="Mô tả"
                name="mo_ta"
            >
                <TextArea rows={4} placeholder="Nhập mô tả về thiết bị" />
            </Form.Item>

            <Divider orientation="left">Thông tin bảo dưỡng</Divider>

            <Row gutter={16}>
                <Col xs={24} md={8}>
                    <Form.Item
                        label="Chu kỳ bảo dưỡng (tháng)"
                        name="chu_ky_bao_duong"
                        tooltip="Số tháng giữa các lần bảo dưỡng"
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1}
                            placeholder="Ví dụ: 6"
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={8}>
                    <Form.Item
                        label="Ngày bảo dưỡng cuối"
                        name="ngay_bao_duong_cuoi"
                        tooltip="Ngày bảo dưỡng gần nhất"
                    >
                        <DatePicker 
                            style={{ width: '100%' }}
                            size="large"
                            placeholder="Chọn ngày"
                            format="DD/MM/YYYY"
                            disabled
                        />
                    </Form.Item>
                </Col>

                <Col xs={24} md={8}>
                    <Form.Item
                        label="Trạng thái"
                        name="trang_thai"
                        rules={[
                            { required: true, message: 'Vui lòng chọn trạng thái!' },
                        ]}
                    >
                        <Select size="large">
                            <Select.Option value="tot">Tốt</Select.Option>
                            <Select.Option value="can_sua_chua">Cần sửa chữa</Select.Option>
                            <Select.Option value="hu_hong">Hư hỏng</Select.Option>
                        </Select>
                    </Form.Item>
                </Col>
            </Row>

            {thietBi.ngay_bao_duong_tiep_theo && (
                <Card style={{ marginBottom: 16, background: canBaoDuong ? '#fff1f0' : '#f6ffed' }}>
                    <Row gutter={16}>
                        <Col span={12}>
                            <strong>Ngày bảo dưỡng tiếp theo:</strong> {dayjs(thietBi.ngay_bao_duong_tiep_theo).format('DD/MM/YYYY')}
                        </Col>
                        <Col span={12}>
                            <strong>Còn lại:</strong> 
                            <Tag color={canBaoDuong ? 'red' : 'green'} style={{ marginLeft: 8 }}>
                                {ngayDenBaoDuong > 0 
                                    ? `${ngayDenBaoDuong} ngày`
                                    : ngayDenBaoDuong === 0
                                    ? 'Hôm nay'
                                    : `Quá hạn ${Math.abs(ngayDenBaoDuong)} ngày`
                                }
                            </Tag>
                        </Col>
                    </Row>
                </Card>
            )}

            <Form.Item
                label="Ghi chú bảo dưỡng"
                name="ghi_chu_bao_duong"
            >
                <TextArea rows={2} placeholder="Ghi chú về lịch bảo dưỡng" />
            </Form.Item>

            <Form.Item>
                <Space>
                    <Button type="primary" htmlType="submit" icon={<SaveOutlined />} size="large">
                        Cập nhật
                    </Button>
                    <Link href="/thiet-bi">
                        <Button icon={<RollbackOutlined />} size="large">
                            Quay lại
                        </Button>
                    </Link>
                </Space>
            </Form.Item>
        </Form>
    );

    const lichSuContent = (
        <div>
            <Space style={{ marginBottom: 16 }}>
                <Link href={`/lich-su-bao-duong/create?thiet_bi_id=${thietBi.id}`}>
                    <Button type="primary" icon={<PlusOutlined />}>
                        Thêm lịch sử bảo dưỡng
                    </Button>
                </Link>
            </Space>
            <Table
                columns={lichSuColumns}
                dataSource={thietBi.lich_su_bao_duongs || []}
                rowKey="id"
                pagination={{ pageSize: 5 }}
                locale={{ emptyText: 'Chưa có lịch sử bảo dưỡng' }}
            />
        </div>
    );

    const tabItems = [
        {
            key: '1',
            label: 'Thông tin thiết bị',
            children: formContent,
        },
        {
            key: '2',
            label: `Lịch sử bảo dưỡng (${thietBi.lich_su_bao_duongs?.length || 0})`,
            children: lichSuContent,
        },
    ];

    return (
        <MainLayout>
            <Card title={`Chỉnh sửa thiết bị: ${thietBi.ten_thiet_bi}`}>
                <Tabs items={tabItems} />
            </Card>
        </MainLayout>
    );
};

export default Edit;
