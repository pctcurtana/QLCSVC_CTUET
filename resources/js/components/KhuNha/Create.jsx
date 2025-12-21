import React from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, Alert, Statistic } from 'antd';
import { SaveOutlined, RollbackOutlined, InfoCircleOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';

const { TextArea } = Input;

const Create = ({ coSos }) => {
    const [form] = Form.useForm();
    const [dienTichSanDaoTao, setDienTichSanDaoTao] = React.useState(0);

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

    const handleDienTichChange = () => {
        const tongDienTichSan = form.getFieldValue('tong_dien_tich_san') || 0;
        const heSoSuDung = form.getFieldValue('he_so_su_dung_dao_tao') || 0.7;
        const dienTichSanDaoTaoMoi = tongDienTichSan * heSoSuDung;
        setDienTichSanDaoTao(dienTichSanDaoTaoMoi);
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    };

    return (
        <MainLayout>
            <Card title="Thêm khu nhà mới">
                <Alert
                    message="Công thức tính diện tích sàn đào tạo"
                    description="DT sàn đào tạo = Tổng DT sàn xây dựng × Hệ số sử dụng cho đào tạo. Hệ số mặc định là 0.7 (70%)."
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
                        trang_thai: 'active',
                        so_tang: 1,
                        he_so_su_dung_dao_tao: 0.7,
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

                    <Row gutter={16}>
                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Tổng diện tích sàn XD (m²)"
                                name="tong_dien_tich_san"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập tổng diện tích sàn!' },
                                ]}
                                tooltip="Tổng diện tích sàn xây dựng của khu nhà"
                            >
                                <InputNumber
                                    style={{ width: '100%' }}
                                    size="large"
                                    min={0}
                                    placeholder="Nhập tổng DT sàn"
                                    formatter={value => `${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
                                    parser={value => value.replace(/\$\s?|(,*)/g, '')}
                                    onChange={handleDienTichChange}
                                />
                            </Form.Item>
                        </Col>

                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Hệ số sử dụng cho đào tạo"
                                name="he_so_su_dung_dao_tao"
                                rules={[
                                    { required: true, message: 'Vui lòng nhập hệ số!' },
                                ]}
                                tooltip="Hệ số phần diện tích sử dụng cho đào tạo (mặc định 0.7 = 70%)"
                            >
                                <InputNumber
                                    style={{ width: '100%' }}
                                    size="large"
                                    min={0}
                                    max={1}
                                    step={0.05}
                                    placeholder="Mặc định: 0.7"
                                    onChange={handleDienTichChange}
                                />
                            </Form.Item>
                        </Col>

                        <Col xs={24} md={8}>
                            <Form.Item label="DT sàn sử dụng cho đào tạo (m²)">
                                <Card size="small" style={{ background: '#f6ffed', border: '1px solid #b7eb8f' }}>
                                    <Statistic
                                        value={dienTichSanDaoTao}
                                        precision={2}
                                        suffix="m²"
                                        valueStyle={{ color: '#3f8600', fontSize: '20px' }}
                                    />
                                    <small style={{ color: '#666' }}>= Tổng DT sàn × Hệ số</small>
                                </Card>
                            </Form.Item>
                        </Col>
                    </Row>

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

