import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message } from 'antd';
import { SaveOutlined, RollbackOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';

const { TextArea } = Input;

const Create = ({ coSos }) => {
    const [form] = Form.useForm();

    const handleSubmit = (values) => {
        router.post('/khu-nha', values, {
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
            <Card title="Thêm khu nhà mới">
                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    initialValues={{
                        trang_thai: 'active',
                        so_tang: 1,
                    }}
                >
                    <Form.Item
                        label="Cơ sở"
                        name="co_so_id"
                        rules={[
                            { required: true, message: 'Vui lòng chọn cơ sở!' },
                        ]}
                    >
                        <Select 
                            size="large" 
                            placeholder="Chọn cơ sở"
                            options={coSos.map(cs => ({ value: cs.id, label: cs.ten_co_so }))}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Mã khu nhà"
                        name="ma_khu_nha"
                        rules={[
                            { required: true, message: 'Vui lòng nhập mã khu nhà!' },
                        ]}
                    >
                        <Input placeholder="Ví dụ: KN001" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Tên khu nhà"
                        name="ten_khu_nha"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tên khu nhà!' },
                        ]}
                    >
                        <Input placeholder="Nhập tên khu nhà" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Loại khu nhà"
                        name="loai_khu_nha"
                        rules={[
                            { required: true, message: 'Vui lòng chọn loại khu nhà!' },
                        ]}
                    >
                        <Select size="large" placeholder="Chọn loại khu nhà">
                            <Select.Option value="phong_hoc">Phòng học</Select.Option>
                            <Select.Option value="phong_lam_viec">Phòng làm việc</Select.Option>
                            <Select.Option value="phong_chuc_nang">Phòng chức năng</Select.Option>
                        </Select>
                    </Form.Item>

                    <Form.Item
                        label="Số tầng"
                        name="so_tang"
                        rules={[
                            { required: true, message: 'Vui lòng nhập số tầng!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1}
                            placeholder="Nhập số tầng"
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
                        />
                    </Form.Item>

                    <Form.Item
                        label="Diện tích sử dụng (m²)"
                        name="dien_tich_su_dung"
                        rules={[
                            { required: true, message: 'Vui lòng nhập diện tích sử dụng!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={0}
                            placeholder="Nhập diện tích sử dụng"
                            formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                            parser={value => value.replace(/\$\s?|(,*)/g, '')}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Năm xây dựng"
                        name="nam_xay_dung"
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1900}
                            max={new Date().getFullYear() + 10}
                            placeholder="Nhập năm xây dựng"
                        />
                    </Form.Item>

                    <Form.Item
                        label="Mô tả"
                        name="mo_ta"
                    >
                        <TextArea rows={4} placeholder="Nhập mô tả về khu nhà" />
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
                            <Link href="/khu-nha">
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

