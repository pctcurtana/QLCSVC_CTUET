import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, DatePicker, Divider, Alert } from 'antd';
import { SaveOutlined, RollbackOutlined, InfoCircleOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

const { TextArea } = Input;

const Create = ({ phongs }) => {
    const [form] = Form.useForm();

    const handleSubmit = (values) => {
        // Format dates
        const formattedValues = {
            ...values,
            ngay_mua: values.ngay_mua ? dayjs(values.ngay_mua).format('YYYY-MM-DD') : null,
            ngay_bao_duong_cuoi: values.ngay_bao_duong_cuoi ? dayjs(values.ngay_bao_duong_cuoi).format('YYYY-MM-DD') : null,
        };

        router.post('/thiet-bi', formattedValues, {
            // success toast hiển thị qua flash ở MainLayout
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    message.error(errors[key]);
                });
            },
        });
    };

    return (
        <MainLayout>
            <Card title="Thêm thiết bị mới">
                <Alert
                    message="Lưu ý: Quản lý từng thiết bị riêng biệt"
                    description="Mỗi thiết bị (máy tính, máy in...) cần nhập 1 lần với số serial riêng để theo dõi lịch sử bảo dưỡng và trạng thái cụ thể."
                    type="info"
                    icon={<InfoCircleOutlined />}
                    showIcon
                    style={{ marginBottom: 24 }}
                    closable
                />
                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    initialValues={{
                        trang_thai: 'tot',
                        chu_ky_bao_duong: 6,
                    }}
                >
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
                                rules={[
                                    { required: true, message: 'Vui lòng nhập số serial!' },
                                ]}
                                tooltip="Số serial duy nhất để phân biệt từng thiết bị"
                            >
                                <Input placeholder="Ví dụ: SN123456789" size="large" />
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
                                rules={[
                                    { required: true, message: 'Vui lòng chọn ngày mua!' },
                                ]}
                                tooltip="Ngày mua để tính chu kỳ bảo dưỡng"
                            >
                                <DatePicker 
                                    style={{ width: '100%' }}
                                    size="large"
                                    placeholder="Chọn ngày mua"
                                    format="DD/MM/YYYY"
                                />
                            </Form.Item>
                        </Col>
                    </Row>

                    <Row gutter={16}>
                        <Col xs={24} md={12}>
                            <Form.Item
                                label="Giá trị (VNĐ)"
                                name="gia_tri"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập giá trị!' },
                                ]}
                                tooltip="Giá trị của 1 thiết bị này"
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
                        </Col>
                    </Row>

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
                                tooltip="Ngày bảo dưỡng gần nhất (nếu đã bảo dưỡng)"
                            >
                                <DatePicker 
                                    style={{ width: '100%' }}
                                    size="large"
                                    placeholder="Chọn ngày"
                                    format="DD/MM/YYYY"
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

                    <Form.Item
                        label="Ghi chú bảo dưỡng"
                        name="ghi_chu_bao_duong"
                    >
                        <TextArea rows={2} placeholder="Ghi chú về lịch bảo dưỡng" />
                    </Form.Item>

                    <Form.Item>
                        <Space>
                            <Button type="primary" htmlType="submit" icon={<SaveOutlined />} size="large">
                                Lưu
                            </Button>
                            <Link href="/thiet-bi">
                                <Button icon={<RollbackOutlined />} size="large">
                                    Quay lại
                                </Button>
                            </Link>
                        </Space>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
};

export default Create;

