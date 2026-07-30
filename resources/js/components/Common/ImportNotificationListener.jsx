import React, { useEffect, useState } from 'react';
import { usePage, router } from '@inertiajs/react';
import { message, Modal, Spin, Button, Alert } from 'antd';
import axios from 'axios';
import { ImportResult } from './ImportButton';

const moduleNames = {
    co_so: 'Cơ sở',
    khu_nha: 'Khu nhà',
    phong: 'Phòng',
    thiet_bi: 'Thiết bị',
};

const moduleRoutes = {
    co_so: '/co-so',
    khu_nha: '/khu-nha',
    phong: '/phong',
    thiet_bi: '/thiet-bi',
};

const ImportNotificationListener = () => {
    const { props } = usePage();
    const user = props.auth?.user;

    const [modalVisible, setModalVisible] = useState(false);
    const [modalLoading, setModalLoading] = useState(false);
    const [importData, setImportData] = useState(null);

    const handleFetchAndShowResult = async (importId) => {
        setModalVisible(true);
        setModalLoading(true);
        try {
            const res = await axios.get(`/imports/${importId}`);
            setImportData(res.data);
        } catch (err) {
            message.error(err.response?.data?.message || 'Không thể tải kết quả import!');
            setModalVisible(false);
        } finally {
            setModalLoading(false);
        }
    };

    // Lắng nghe yêu cầu mở modal xem chi tiết từ Lịch sử Import
    useEffect(() => {
        const handleOpenDetail = (e) => {
            if (e.detail?.importId) {
                handleFetchAndShowResult(e.detail.importId);
            }
        };

        window.addEventListener('open-import-detail-modal', handleOpenDetail);
        return () => {
            window.removeEventListener('open-import-detail-modal', handleOpenDetail);
        };
    }, []);

    useEffect(() => {
        if (!user?.id || !window.Echo) {
            return;
        }

        const channel = window.Echo.private(`user.${user.id}`);

        channel.listen('.import.processed', (event) => {
            const moduleName = moduleNames[event.module] || event.module;
            const moduleRoute = moduleRoutes[event.module];
            const currentPath = window.location.pathname;

            // Báo cho ImportHistoryModal làm mới dữ liệu
            window.dispatchEvent(new CustomEvent('import-status-changed', { detail: event }));

            // Nếu đang ở đúng trang module vừa import thì tự reload danh sách
            if (moduleRoute && currentPath.startsWith(moduleRoute)) {
                router.reload({ preserveScroll: true });
            }

            const isSuccess = event.status === 'completed';
            const hasErrors = event.errors > 0;

            if (isSuccess) {
                if (hasErrors) {
                    message.warning(
                        `Import ${moduleName} hoàn tất: ${event.created} mới, ${event.updated} cập nhật, ${event.errors} lỗi.`
                    );
                } else {
                    message.success(
                        `Import ${moduleName} thành công: ${event.created} tạo mới, ${event.updated} cập nhật.`
                    );
                }
            } else {
                message.error(
                    `Import ${moduleName} thất bại: ${event.message || 'Xử lý file không thành công.'}`
                );
            }
        });

        return () => {
            channel.stopListening('.import.processed');
            window.Echo.leave(`user.${user.id}`);
        };
    }, [user?.id]);

    return (
        <Modal
            title={
                importData
                    ? `Kết quả Import ${moduleNames[importData.module] || importData.module}`
                    : 'Kết quả Import Excel'
            }
            open={modalVisible}
            onCancel={() => setModalVisible(false)}
            footer={[
                <Button key="close" onClick={() => setModalVisible(false)}>
                    Đóng
                </Button>,
            ]}
            width={800}
            destroyOnClose
        >
            {modalLoading ? (
                <div style={{ textAlign: 'center', padding: '30px 0' }}>
                    <Spin size="large" tip="Đang tải kết quả import..." />
                </div>
            ) : importData ? (
                <div>
                    {importData.status === 'failed' && (
                        <Alert
                            type="error"
                            showIcon
                            message="Xử lý file thất bại"
                            description={importData.error_message || 'Có lỗi xảy ra khi đọc hoặc lưu dữ liệu từ file Excel.'}
                            style={{ marginBottom: 16 }}
                        />
                    )}
                    <ImportResult result={importData} />
                </div>
            ) : null}
        </Modal>
    );
};

export default ImportNotificationListener;
