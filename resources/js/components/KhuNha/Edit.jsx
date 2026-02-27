import React, { useState } from 'react';
import MainLayout from '../Layout/MainLayout';
import { Form, Input, InputNumber, Button, Card, Space, Select, message, Row, Col, Alert, Statistic, Modal, Tag, Tooltip } from 'antd';
import { SaveOutlined, RollbackOutlined, InfoCircleOutlined, HistoryOutlined, ExclamationCircleOutlined } from '@ant-design/icons';
import { router, Link } from '@inertiajs/react';
import dayjs from 'dayjs';

const { TextArea } = Input;

const Edit = ({ khuNha, coSos }) => {
    const [form] = Form.useForm();
    const [submitting, setSubmitting] = useState(false);
    const [versionModalVisible, setVersionModalVisible] = useState(false);
    const [pendingValues, setPendingValues] = useState(null);
    const [dienTichSanDaoTao, setDienTichSanDaoTao] = useState(
        (khuNha.tong_dien_tich_san || 0) * (khuNha.he_so_su_dung_dao_tao || 0.7)
    );

    const handleDienTichChange = () => {
        const tongDienTichSan = form.getFieldValue('tong_dien_tich_san') || 0;
        const heSoSuDung = form.getFieldValue('he_so_su_dung_dao_tao') || 0.7;
        setDienTichSanDaoTao(tongDienTichSan * heSoSuDung);
    };

    // Kiểm tra thay đổi cho version update (không tính ma_khu_nha vì backend luôn giữ gốc)
    const checkHasChanges = (formValues) => {
        const fields = {
            co_so_id: (v) => Number(v),
            ten_khu_nha: (v) => String(v ?? '').trim(),
            loai_khu_nha: (v) => String(v ?? ''),
            so_tang: (v) => Number(v),
            tong_dien_tich_san: (v) => parseFloat(v) || 0,
            he_so_su_dung_dao_tao: (v) => parseFloat(v) || 0,
            nam_xay_dung: (v) => v ? Number(v) : null,
            mo_ta: (v) => String(v ?? '').trim(),
            trang_thai: (v) => String(v ?? ''),
        };
        return Object.keys(fields).some(key => {
            const norm = fields[key];
            return norm(formValues[key]) !== norm(khuNha[key]);
        });
    };

    // Cập nhật trực tiếp — thực thi submit
    const handleDirectUpdate = (values) => {
        setSubmitting(true);
        router.put(`/khu-nha/${khuNha.id}`, values, {
            onError: (errors) => {
                if (errors && typeof errors === 'object') {
                    Object.keys(errors).forEach(key => message.error(errors[key]));
                }
                setSubmitting(false);
            },
            onFinish: () => setSubmitting(false),
        });
    };

    // Nút Cập nhật trực tiếp — validate trước khi submit
    const handleDirectUpdateClick = () => {
        form.validateFields().then(values => {
            const allFields = {
                ma_khu_nha: (v) => String(v ?? '').trim(),
                co_so_id: (v) => Number(v),
                ten_khu_nha: (v) => String(v ?? '').trim(),
                loai_khu_nha: (v) => String(v ?? ''),
                so_tang: (v) => Number(v),
                tong_dien_tich_san: (v) => parseFloat(v) || 0,
                he_so_su_dung_dao_tao: (v) => parseFloat(v) || 0,
                nam_xay_dung: (v) => v ? Number(v) : null,
                mo_ta: (v) => String(v ?? '').trim(),
                trang_thai: (v) => String(v ?? ''),
            };
            const hasChanges = Object.keys(allFields).some(key => {
                const norm = allFields[key];
                return norm(values[key]) !== norm(khuNha[key]);
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
            const maChanged = String(values.ma_khu_nha ?? '').trim() !== String(khuNha.ma_khu_nha ?? '').trim();
            if (!checkHasChanges(values)) {
                if (maChanged) {
                    message.warning('Mã khu nhà chỉ thay đổi được bằng "Cập nhật trực tiếp". Phiên bản mới không ghi nhận thay đổi mã.');
                } else {
                    message.warning('Không có thay đổi nào để lưu phiên bản mới.');
                }
                return;
            }
            if (maChanged) {
                message.info('Lưu ý: Mã khu nhà sẽ không thay đổi khi lưu phiên bản mới. Nếu muốn đổi mã, hãy dùng "Cập nhật trực tiếp".');
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
        router.post(`/khu-nha/${khuNha.id}/version-update`, pendingValues, {
            onError: (errors) => {
                if (errors && typeof errors === 'object') {
                    Object.keys(errors).forEach(key => message.error(errors[key]));
                }
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
                        <span>Chỉnh sửa khu nhà: {khuNha.ten_khu_nha}</span>
                        <Tag color="blue">Phiên bản {khuNha.phien_ban ?? 1}</Tag>
                        {khuNha.hieu_luc_tu && (
                            <Tooltip title="Ngày bắt đầu hiệu lực">
                                <Tag color="green">Từ: {dayjs(khuNha.hieu_luc_tu).format('DD/MM/YYYY')}</Tag>
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
                    message="Công thức tính diện tích sàn đào tạo"
                    description="DT sàn đào tạo = Tổng DT sàn xây dựng × Hệ số sử dụng cho đào tạo. Hệ số mặc định là 0.7 (70%)."
                    type="warning"
                    icon={<InfoCircleOutlined />}
                    showIcon
                    style={{ marginBottom: 24 }}
                    closable
                />

                <Form
                    form={form}
                    layout="vertical"
                    initialValues={{ ...khuNha, he_so_su_dung_dao_tao: khuNha.he_so_su_dung_dao_tao || 0.7 }}
                >
                    <Form.Item
                        label="Mã khu nhà"
                        name="ma_khu_nha"
                        rules={[{ required: true, message: 'Vui lòng nhập mã khu nhà!' }]}
                        tooltip="Khi lưu phiên bản mới, mã khu nhà được giữ nguyên từ bản gốc dù có thay đổi ở đây"
                    >
                        <Input placeholder="Ví dụ: KNA" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Cơ sở"
                        name="co_so_id"
                        rules={[{ required: true, message: 'Vui lòng chọn cơ sở!' }]}
                    >
                        <Select
                            size="large"
                            placeholder="Chọn cơ sở"
                            options={coSos.map(cs => ({ value: cs.id, label: cs.ten_co_so }))}
                        />
                    </Form.Item>

                    <Form.Item
                        label="Tên khu nhà"
                        name="ten_khu_nha"
                        rules={[{ required: true, message: 'Vui lòng nhập tên khu nhà!' }]}
                    >
                        <Input placeholder="Nhập tên khu nhà" size="large" />
                    </Form.Item>

                    <Form.Item
                        label="Loại khu nhà"
                        name="loai_khu_nha"
                        rules={[{ required: true, message: 'Vui lòng chọn loại khu nhà!' }]}
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
                        rules={[{ required: true, message: 'Vui lòng nhập số tầng!' }]}
                    >
                        <InputNumber style={{ width: '100%' }} size="large" min={1} placeholder="Nhập số tầng" />
                    </Form.Item>

                    <Row gutter={16}>
                        <Col xs={24} md={8}>
                            <Form.Item
                                label="Tổng diện tích sàn XD (m²)"
                                name="tong_dien_tich_san"
                                rules={[{ required: true, message: 'Vui lòng nhập tổng diện tích sàn!' }]}
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
                                rules={[{ required: true, message: 'Vui lòng nhập hệ số!' }]}
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

                    <Form.Item label="Năm xây dựng" name="nam_xay_dung">
                        <InputNumber
                            style={{ width: '100%' }}
                            size="large"
                            min={1900}
                            max={new Date().getFullYear() + 10}
                            placeholder="Nhập năm xây dựng"
                        />
                    </Form.Item>

                    <Form.Item label="Mô tả" name="mo_ta">
                        <TextArea rows={4} placeholder="Nhập mô tả về khu nhà" />
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
                            <Link href="/khu-nha">
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
                    Hệ thống sẽ <strong>lưu trữ dữ liệu hiện tại</strong> của khu nhà <strong>{khuNha.ten_khu_nha}</strong> vào lịch sử và tạo <strong>phiên bản {(khuNha.phien_ban ?? 1) + 1}</strong> với các thay đổi bạn vừa nhập.
                </p>
                <Alert
                    message="Dữ liệu cũ sẽ được giữ lại để xuất báo cáo theo mốc thời gian."
                    type="success"
                    showIcon
                    style={{ marginTop: 12 }}
                />
                <Alert
                    message="Tất cả phòng đang hoạt động thuộc khu nhà này sẽ tự động liên kết sang phiên bản mới."
                    type="info"
                    showIcon
                    style={{ marginTop: 8 }}
                />
            </Modal>
        </MainLayout>
    );
};

export default Edit;
