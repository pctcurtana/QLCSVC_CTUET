import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, Alert, Statistic } from 'antd';
import { SaveOutlined, RollbackOutlined, InfoCircleOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';

const { TextArea } = Input;

const Edit = ({ coSo }) => {
    const [form] = Form.useForm();
    const [dienTichQuyDoi, setDienTichQuyDoi] = React.useState(
        (coSo.dien_tich_dat || 0) * (coSo.vi_tri_khuon_vien || 2.5)
    );

    const handleSubmit = (values) => {
        router.put(`/co-so/${coSo.id}`, values, {
            // success toast hiển thị qua flash ở MainLayout
            onError: (errors) => {
                Object.keys(errors).forEach(key => {
                    message.error(errors[key]);
                });
            },
        });
    };

    const handleDienTichChange = () => {
        const dienTichDat = form.getFieldValue('dien_tich_dat') || 0;
        const viTriKhuonVien = form.getFieldValue('vi_tri_khuon_vien') || 2.5;
        const dienTichQuyDoiMoi = dienTichDat * viTriKhuonVien;
        setDienTichQuyDoi(dienTichQuyDoiMoi);
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    };

    return (
        <MainLayout>
            <Card title={`Chỉnh sửa cơ sở: ${coSo.ten_co_so}`}>
                <Alert
                    message="Công thức tính diện tích quy đổi (theo BGD)"
                    description="Diện tích quy đổi = Diện tích đất × Vị trí khuôn viên. Hệ số vị trí khuôn viên mặc định là 2.5 theo quy định của Bộ Giáo dục."
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
                        ...coSo,
                        vi_tri_khuon_vien: coSo.vi_tri_khuon_vien || 2.5,
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

                    <Row gutter={16}>
                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Diện tích đất (m²)"
                                name="dien_tich_dat"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập diện tích đất!' },
                                ]}
                                tooltip="Diện tích đất thực tế của cơ sở"
                            >
                                <InputNumber
                                    style={{ width: '100%' }}
                                    size="large"
                                    min={0}
                                    placeholder="Nhập diện tích đất"
                                    formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                                    parser={value => value.replace(/\$\s?|(,*)/g, '')}
                                    onChange={handleDienTichChange}
                                />
                            </Form.Item>
                        </Col>

                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Vị trí khuôn viên"
                                name="vi_tri_khuon_vien"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập hệ số vị trí khuôn viên!' },
                                ]}
                                tooltip="Hệ số vị trí khuôn viên theo BGD (mặc định 2.5)"
                            >
                                <InputNumber
                                    style={{ width: '100%' }}
                                    size="large"
                                    min={0}
                                    step={0.1}
                                    placeholder="Mặc định: 2.5"
                                    onChange={handleDienTichChange}
                                />
                            </Form.Item>
                        </Col>

                        <Col xs={24} md={8}>
                            <Form.Item label="Diện tích quy đổi (m²)">
                                <Card size="small" style={{ background: '#f6ffed', border: '1px solid #b7eb8f' }}>
                                    <Statistic
                                        value={dienTichQuyDoi}
                                        precision={2}
                                        suffix="m²"
                                        valueStyle={{ color: '#3f8600', fontSize: '20px' }}
                                    />
                                    <small style={{ color: '#666' }}>= DT đất × Vị trí KV</small>
                                </Card>
                            </Form.Item>
                        </Col>
                    </Row>

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
                                Cập nhật
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

export default Edit;

