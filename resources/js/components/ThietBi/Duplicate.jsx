import React, { useState } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, DatePicker, Divider, Alert, Badge } from 'antd';
import { SaveOutlined, RollbackOutlined, CopyOutlined, PlusOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

const { TextArea } = Input;

const Duplicate = ({ thietBi, phongs }) => {
    const [form] = Form.useForm();
    const [copyCount, setCopyCount] = useState(1);

    // Format initial values from source thietBi (except unique fields)
    const initialValues = {
        phong_id: thietBi.phong_id,
        // ma_thiet_bi and serial_number will be empty - user must provide
        ten_thiet_bi: thietBi.ten_thiet_bi,
        loai_thiet_bi: thietBi.loai_thiet_bi,
        hang_san_xuat: thietBi.hang_san_xuat,
        model: thietBi.model,
        nam_mua: thietBi.nam_mua,
        ngay_mua: thietBi.ngay_mua ? dayjs(thietBi.ngay_mua) : dayjs(),
        chu_ky_bao_duong: thietBi.chu_ky_bao_duong || 6,
        gia_tri: thietBi.gia_tri,
        thong_so_ky_thuat: thietBi.thong_so_ky_thuat,
        mo_ta: thietBi.mo_ta,
        trang_thai: 'tot', // Default to good condition for new device
    };

    const handleSubmit = (values) => {
        // Format dates
        const formattedValues = {
            ...values,
            ngay_mua: values.ngay_mua ? dayjs(values.ngay_mua).format('YYYY-MM-DD') : null,
        };

        router.post('/thiet-bi', formattedValues, {
            onSuccess: () => {
                message.success('Sao chép thiết bị thành công!');
            },
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    message.error(errors[key]);
                });
            },
        });
    };

    return (
        <MainLayout>
            <Card 
                title={
                    <Space>
                        <CopyOutlined />
                        <span>Sao chép thiết bị</span>
                        <Badge count={`Từ: ${thietBi.ma_thiet_bi}`} style={{ backgroundColor: '#52c41a' }} />
                    </Space>
                }
            >
                <Alert
                    message="Sao chép thiết bị"
                    description={`Tạo thiết bị mới với thông tin tương tự "${thietBi.ten_thiet_bi}". Bạn cần nhập Mã thiết bị và Số Serial mới (không được trùng).`}
                    type="info"
                    showIcon
                    style={{ marginBottom: 24 }}
                    closable
                />

                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    initialValues={initialValues}
                >
                    <Divider orientation="left">Thông tin định danh (Bắt buộc nhập mới)</Divider>
                    
                    <Row gutter={16}>
                        <Col xs={24} md={12}>
                            <Form.Item
                                label="Mã thiết bị MỚI"
                                name="ma_thiet_bi"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập mã thiết bị mới!' },
                                ]}
                                tooltip="Mã thiết bị phải khác với thiết bị gốc"
                            >
                                <Input placeholder="Ví dụ: TB002" size="large" />
                            </Form.Item>
                        </Col>

                        <Col xs={24} md={12}>
                            <Form.Item
                                label="Số Serial MỚI"
                                name="serial_number"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập số serial mới!' },
                                ]}
                                tooltip="Số serial phải khác với thiết bị gốc"
                            >
                                <Input placeholder="Ví dụ: SN123456790" size="large" />
                            </Form.Item>
                        </Col>
                    </Row>

                    <Divider orientation="left">Thông tin chung (Đã sao chép từ thiết bị gốc)</Divider>

                    <Row gutter={16}>
                        <Col xs={24} md={12}>
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
                        <Col xs={24} md={8}>
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

                        <Col xs={24} md={8}>
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

                        <Col xs={24} md={8}>
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
                        <Col xs={24} md={12}>
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
                    </Row>

                    <Form.Item>
                        <Space size="large">
                            <Button type="primary" htmlType="submit" icon={<SaveOutlined />} size="large">
                                Tạo thiết bị mới
                            </Button>
                            <Link href={`/thiet-bi/${thietBi.id}/edit`}>
                                <Button icon={<RollbackOutlined />} size="large">
                                    Quay lại thiết bị gốc
                                </Button>
                            </Link>
                            <Link href="/thiet-bi">
                                <Button size="large">
                                    Danh sách thiết bị
                                </Button>
                            </Link>
                        </Space>
                    </Form.Item>
                </Form>
            </Card>
        </MainLayout>
    );
};

export default Duplicate;




