import React, { useState } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, Alert, Statistic, Modal, Tag, Tooltip } from 'antd';
import { SaveOutlined, RollbackOutlined, InfoCircleOutlined, HistoryOutlined, ExclamationCircleOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

const { TextArea } = Input;

const Edit = ({ coSo }) => {
    const [form] = Form.useForm();
    const [submitting, setSubmitting] = useState(false);
    const [versionModalVisible, setVersionModalVisible] = useState(false);
    const [pendingValues, setPendingValues] = useState(null);
    const [dienTichQuyDoi, setDienTichQuyDoi] = useState(
        (coSo.dien_tich_dat || 0) * (coSo.vi_tri_khuon_vien || 2.5)
    );

    const handleDienTichChange = () => {
        const dienTichDat = form.getFieldValue('dien_tich_dat') || 0;
        const viTriKhuonVien = form.getFieldValue('vi_tri_khuon_vien') || 2.5;
        setDienTichQuyDoi(dienTichDat * viTriKhuonVien);
    };

    // Kiểm tra thay đổi cho version update (không tính ma_co_so vì backend luôn giữ gốc)
    const checkHasChanges = (formValues) => {
        const fields = {
            ten_co_so: (v) => String(v ?? '').trim(),
            dia_chi: (v) => String(v ?? '').trim(),
            dien_tich_dat: (v) => parseFloat(v) || 0,
            vi_tri_khuon_vien: (v) => parseFloat(v) || 0,
            mo_ta: (v) => String(v ?? '').trim(),
            trang_thai: (v) => String(v ?? ''),
        };
        return Object.keys(fields).some(key => {
            const norm = fields[key];
            return norm(formValues[key]) !== norm(coSo[key]);
        });
    };

    // Cập nhật trực tiếp — thực thi submit
    const handleDirectUpdate = (values) => {
        setSubmitting(true);
        router.put(`/co-so/${coSo.id}`, values, {
            onError: (errors) => {
                Object.keys(errors).forEach(key => message.error(errors[key]));
                setSubmitting(false);
            },
            onFinish: () => setSubmitting(false),
        });
    };

    // Nút Cập nhật trực tiếp — validate trước khi submit
    const handleDirectUpdateClick = () => {
        form.validateFields().then(values => {
            const allFields = {
                ma_co_so: (v) => String(v ?? '').trim(),
                ten_co_so: (v) => String(v ?? '').trim(),
                dia_chi: (v) => String(v ?? '').trim(),
                dien_tich_dat: (v) => parseFloat(v) || 0,
                vi_tri_khuon_vien: (v) => parseFloat(v) || 0,
                mo_ta: (v) => String(v ?? '').trim(),
                trang_thai: (v) => String(v ?? ''),
            };
            const hasChanges = Object.keys(allFields).some(key => {
                const norm = allFields[key];
                return norm(values[key]) !== norm(coSo[key]);
            });
            if (!hasChanges) {
                message.info('Không có thay đổi nào để cập nhật.');
                return;
            }
            handleDirectUpdate(values);
        }).catch(() => {
            message.error('Vui lòng kiểm tra lại các trường bắt buộc.');
        });
    };

    // Mở modal xác nhận lưu phiên bản mới
    const handleVersionUpdateClick = () => {
        form.validateFields().then(values => {
            const maChanged = String(values.ma_co_so ?? '').trim() !== String(coSo.ma_co_so ?? '').trim();
            if (!checkHasChanges(values)) {
                if (maChanged) {
                    message.warning('Mã cơ sở chỉ thay đổi được bằng "Cập nhật trực tiếp". Phiên bản mới không ghi nhận thay đổi mã.');
                } else {
                    message.warning('Không có thay đổi nào để lưu phiên bản mới.');
                }
                return;
            }
            if (maChanged) {
                message.info('Lưu ý: Mã cơ sở sẽ không thay đổi khi lưu phiên bản mới. Nếu muốn đổi mã, hãy dùng "Cập nhật trực tiếp".');
            }
            setPendingValues(values);
            setVersionModalVisible(true);
        }).catch(() => {
            message.error('Vui lòng kiểm tra lại các trường bắt buộc.');
        });
    };

    // Xác nhận lưu phiên bản mới
    const handleVersionUpdateConfirm = () => {
        if (!pendingValues) return;
        setSubmitting(true);
        setVersionModalVisible(false);
        router.post(`/co-so/${coSo.id}/version-update`, pendingValues, {
            onError: (errors) => {
                Object.keys(errors).forEach(key => message.error(errors[key]));
                setSubmitting(false);
            },
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <MainLayout>
            <Card
                title={
                    <Space>
                        <span>Chỉnh sửa cơ sở: {coSo.ten_co_so}</span>
                        <Tag color="blue">Phiên bản {coSo.phien_ban ?? 1}</Tag>
                        {coSo.hieu_luc_tu && (
                            <Tooltip title="Ngày bắt đầu hiệu lực">
                                <Tag color="green">Từ: {dayjs(coSo.hieu_luc_tu).format('DD/MM/YYYY')}</Tag>
                            </Tooltip>
                        )}
                    </Space>
                }
            >
                <Alert
                    message="Chọn cách cập nhật"
                    description={
                        <ul style={{ marginBottom: 0, paddingLeft: 20 }}>
                            <li><strong>Cập nhật trực tiếp:</strong> Ghi đè dữ liệu hiện tại. Dữ liệu cũ sẽ bị mất.</li>
                            <li><strong>Lưu phiên bản mới:</strong> Giữ nguyên dữ liệu cũ làm lịch sử, tạo bản ghi mới với thay đổi. Dùng khi cần xuất báo cáo theo mốc thời gian.</li>
                        </ul>
                    }
                    type="info"
                    showIcon
                    style={{ marginBottom: 12 }}
                />
                <Alert
                    message="Công thức tính diện tích quy đổi (theo BGD)"
                    description="Diện tích quy đổi = Diện tích đất × Vị trí khuôn viên. Hệ số vị trí khuôn viên mặc định là 2.5 theo quy định của Bộ Giáo dục."
                    type="warning"
                    icon={<InfoCircleOutlined />}
                    showIcon
                    style={{ marginBottom: 24 }}
                    closable
                />

                <Form
                    form={form}
                    layout="vertical"
                    initialValues={{ ...coSo, vi_tri_khuon_vien: coSo.vi_tri_khuon_vien || 2.5 }}
                >
                    <Form.Item
                        label="Mã cơ sở"
                        name="ma_co_so"
                        rules={[{ required: true, message: 'Vui lòng nhập mã cơ sở!' }]}
                        tooltip="Khi lưu phiên bản mới, mã cơ sở được giữ nguyên từ bản gốc dù có thay đổi ở đây"
                    >
                        <Input placeholder="Ví dụ: CS01" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Tên cơ sở"
                        name="ten_co_so"
                        rules={[{ required: true, message: 'Vui lòng nhập tên cơ sở!' }]}
                    >
                        <Input placeholder="Nhập tên cơ sở" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Địa chỉ"
                        name="dia_chi"
                        rules={[{ required: true, message: 'Vui lòng nhập địa chỉ!' }]}
                    >
                        <Input placeholder="Nhập địa chỉ cơ sở" size="large" />
                    </Form.Item>

                    <Row gutter={16}>
                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Diện tích đất (m²)"
                                name="dien_tich_dat"
                                rules={[{ required: true, message: 'Vui lòng nhập diện tích đất!' }]}
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
                                rules={[{ required: true, message: 'Vui lòng nhập hệ số vị trí khuôn viên!' }]}
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

                    <Form.Item label="Mô tả" name="mo_ta">
                        <TextArea rows={4} placeholder="Nhập mô tả về cơ sở" />
                    </Form.Item>

                    <Form.Item
                        label="Trạng thái"
                        name="trang_thai"
                        rules={[{ required: true, message: 'Vui lòng chọn trạng thái!' }]}
                    >
                        <Select size="large">
                            <Select.Option value="active">Hoạt động</Select.Option>
                            <Select.Option value="inactive">Không hoạt động</Select.Option>
                        </Select>
                    </Form.Item>

                    <Form.Item>
                        <Space wrap>
                            <Button
                                type="primary"
                                icon={<SaveOutlined />}
                                size="large"
                                loading={submitting}
                                onClick={handleDirectUpdateClick}
                            >
                                Cập nhật trực tiếp
                            </Button>
                            <Button
                                type="default"
                                icon={<HistoryOutlined />}
                                size="large"
                                loading={submitting}
                                onClick={handleVersionUpdateClick}
                                style={{ borderColor: '#fa8c16', color: '#fa8c16' }}
                            >
                                Lưu phiên bản mới
                            </Button>
                            <Link href="/co-so">
                                <Button icon={<RollbackOutlined />} size="large">Quay lại</Button>
                            </Link>
                        </Space>
                    </Form.Item>
                </Form>
            </Card>

            <Modal
                title={
                    <Space>
                        <ExclamationCircleOutlined style={{ color: '#fa8c16' }} />
                        <span>Xác nhận lưu phiên bản mới</span>
                    </Space>
                }
                open={versionModalVisible}
                onOk={handleVersionUpdateConfirm}
                onCancel={() => setVersionModalVisible(false)}
                okText="Xác nhận lưu phiên bản mới"
                cancelText="Hủy"
                okButtonProps={{ style: { background: '#fa8c16', borderColor: '#fa8c16' } }}
            >
                <p>
                    Hệ thống sẽ <strong>lưu trữ dữ liệu hiện tại</strong> của cơ sở <strong>{coSo.ten_co_so}</strong> vào lịch sử và tạo <strong>phiên bản {(coSo.phien_ban ?? 1) + 1}</strong> với các thay đổi bạn vừa nhập.
                </p>
                <Alert
                    message="Dữ liệu cũ sẽ được giữ lại để xuất báo cáo theo mốc thời gian."
                    type="success"
                    showIcon
                    style={{ marginTop: 12 }}
                />
                <Alert
                    message="Tất cả khu nhà đang hoạt động thuộc cơ sở này sẽ tự động liên kết sang phiên bản mới."
                    type="info"
                    showIcon
                    style={{ marginTop: 8 }}
                />
            </Modal>
        </MainLayout>
    );
};

export default Edit;
