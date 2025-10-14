import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message } from 'antd';
import { SaveOutlined, RollbackOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';

const { TextArea } = Input;

const Create = () => {
    const [form] = Form.useForm();

    const handleSubmit = (values) => {
        router.post('/co-so', values, {
            // success toast hiển thị qua flash ở MainLayout
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    message.error(errors[key]);
                });
            },
        });
    };

    const handleDienTichChange = () => {
        const tongDienTich = form.getFieldValue('tong_dien_tich') || 0;
        const dienTichSanXayDung = form.getFieldValue('dien_tich_san_xay_dung') || 0;
        const dienTichConLai = tongDienTich - dienTichSanXayDung;
        form.setFieldsValue({ dien_tich_con_lai: dienTichConLai });
    };

    return (
        <MainLayout>
            <Card title="Thêm cơ sở mới">
                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    initialValues={{
                        trang_thai: 'active',
                    }}
                >
                    <Form.Item
                        label="Mã cơ sở"
                        name="ma_co_so"
                        rules={[
                            { required: true, message: 'Vui lòng nhập mã cơ sở!' },
                        ]}
                    >
                        <Input placeholder="Ví dụ: CS001" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Tên cơ sở"
                        name="ten_co_so"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tên cơ sở!' },
                        ]}
                    >
                        <Input placeholder="Nhập tên cơ sở" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Địa chỉ"
                        name="dia_chi"
                        rules={[
                            { required: true, message: 'Vui lòng nhập địa chỉ!' },
                        ]}
                    >
                        <Input placeholder="Nhập địa chỉ cơ sở" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Tổng diện tích (m²)"
                        name="tong_dien_tich"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tổng diện tích!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={0}
                            placeholder="Nhập tổng diện tích"
                            formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                            parser={value => value.replace(/\$\s?|(,*)/g, '')}
                            onChange={handleDienTichChange}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Diện tích sân xây dựng (m²)"
                        name="dien_tich_san_xay_dung"
                        rules={[
                            { required: true, message: 'Vui lòng nhập diện tích sân xây dựng!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={0}
                            placeholder="Nhập diện tích sân xây dựng"
                            formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                            parser={value => value.replace(/\$\s?|(,*)/g, '')}
                            onChange={handleDienTichChange}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Mô tả"
                        name="mo_ta"
                    >
                        <TextArea rows={4} placeholder="Nhập mô tả về cơ sở" />
                    </Form.Item>

                    <Form.Item
                        label="Trạng thái"
                        name="trang_thai"
                        rules={[
                            { required: true, message: 'Vui lòng chọn trạng thái!' },
                        ]}
                    >
                        <Select size="large">
                            <Select.Option value="active">Hoạt động</Select.Option>
                            <Select.Option value="inactive">Không hoạt động</Select.Option>
                        </Select>
                    </Form.Item>

                    <Form.Item>
                        <Space>
                            <Button type="primary" htmlType="submit" icon={<SaveOutlined />} size="large">
                                Lưu
                            </Button>
                            <Link href="/co-so">
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

