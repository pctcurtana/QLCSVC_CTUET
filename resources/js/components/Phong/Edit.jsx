import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message } from 'antd';
import { SaveOutlined, RollbackOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';

const { TextArea } = Input;

const Edit = ({ phong, khuNhas }) => {
    const [form] = Form.useForm();

    const handleSubmit = (values) => {
        router.put(`/phong/${phong.id}`, values, {
            // Không tự show success ở đây; để MainLayout show flash
            onError: (errors) => {
                if (errors && typeof errors === 'object') {
                    Object.keys(errors).forEach(key => message.error(errors[key]));
                }
            },
        });
    };

    return (
        <MainLayout>
            <Card title={`Chỉnh sửa phòng: ${phong.ten_phong}`}>
                <Form
                    form={form}
                    layout="vertical"
                    onFinish={handleSubmit}
                    initialValues={phong}
                >
                    <Form.Item
                        label="Khu nhà"
                        name="khu_nha_id"
                        rules={[
                            { required: true, message: 'Vui lòng chọn khu nhà!' },
                        ]}
                    >
                        <Select 
                            size="large" 
                            placeholder="Chọn khu nhà"
                            showSearch
                            optionFilterProp="label"
                            options={khuNhas.map(kn => ({ 
                                value: kn.id, 
                                label: `${kn.ten_khu_nha} - ${kn.co_so.ten_co_so}` 
                            }))}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Mã phòng"
                        name="ma_phong"
                        rules={[
                            { required: true, message: 'Vui lòng nhập mã phòng!' },
                        ]}
                    >
                        <Input placeholder="Ví dụ: P001" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Tên phòng"
                        name="ten_phong"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tên phòng!' },
                        ]}
                    >
                        <Input placeholder="Nhập tên phòng" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Loại phòng"
                        name="loai_phong"
                        rules={[
                            { required: true, message: 'Vui lòng chọn loại phòng!' },
                        ]}
                    >
                        <Select size="large" placeholder="Chọn loại phòng">
                            <Select.Option value="phong_hoc">Phòng học</Select.Option>
                            <Select.Option value="phong_thi_nghiem">Phòng thí nghiệm</Select.Option>
                            <Select.Option value="phong_thuc_hanh">Phòng thực hành</Select.Option>
                            <Select.Option value="phong_lam_viec">Phòng làm việc</Select.Option>
                            <Select.Option value="phong_chuc_nang">Phòng chức năng</Select.Option>
                        </Select>
                    </Form.Item>

                    <Form.Item
                        label="Tầng"
                        name="tang"
                        rules={[
                            { required: true, message: 'Vui lòng nhập tầng!' },
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
                        label="Diện tích (m²)"
                        name="dien_tich"
                        rules={[
                            { required: true, message: 'Vui lòng nhập diện tích!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={0}
                            placeholder="Nhập diện tích"
                            formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                            parser={value => value.replace(/\$\s?|(,*)/g, '')}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Sức chứa (người)"
                        name="suc_chua"
                        rules={[
                            { required: true, message: 'Vui lòng nhập sức chứa!' },
                        ]}
                    >
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={0}
                            placeholder="Nhập sức chứa"
                        />
                    </Form.Item>

                    <Form.Item
                        label="Trang thiết bị"
                        name="trang_thiet_bi"
                    >
                        <TextArea rows={3} placeholder="Nhập danh sách trang thiết bị" />
                    </Form.Item>

                    <Form.Item
                        label="Mô tả"
                        name="mo_ta"
                    >
                        <TextArea rows={4} placeholder="Nhập mô tả về phòng" />
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
                            <Select.Option value="maintenance">Bảo trì</Select.Option>
                            <Select.Option value="inactive">Không hoạt động</Select.Option>
                        </Select>
                    </Form.Item>

                    <Form.Item>
                        <Space>
                            <Button type="primary" htmlType="submit" icon={<SaveOutlined />} size="large">
                                Cập nhật
                            </Button>
                            <Link href="/phong">
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

export default Edit;

