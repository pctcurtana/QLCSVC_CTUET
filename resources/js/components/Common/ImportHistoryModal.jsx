import React, { useEffect, useState } from 'react';
import { Modal, Table, Tag, Button, Space, Spin, Tooltip } from 'antd';
import {
    HistoryOutlined,
    ReloadOutlined,
    ClockCircleOutlined,
    SyncOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
    EyeOutlined,
} from '@ant-design/icons';
import axios from 'axios';

const moduleNames = {
    co_so: 'Cơ sở',
    khu_nha: 'Khu nhà',
    phong: 'Phòng',
    thiet_bi: 'Thiết bị',
};

const ImportHistoryModal = ({ open, onClose, onSelectImport, moduleFilter = null }) => {
    const [loading, setLoading] = useState(false);
    const [imports, setImports] = useState([]);

    const fetchImports = async () => {
        setLoading(true);
        try {
            const url = moduleFilter ? `/imports?module=${moduleFilter}` : '/imports';
            const res = await axios.get(url);
            setImports(res.data || []);
        } catch (err) {
            // Error handling fallback
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (open) {
            fetchImports();
        }
    }, [open, moduleFilter]);

    // Lắng nghe sự kiện realtime từ Pusher để làm mới danh sách tự động
    useEffect(() => {
        const handleStatusChange = () => {
            if (open) {
                fetchImports();
            }
        };

        window.addEventListener('import-status-changed', handleStatusChange);
        return () => {
            window.removeEventListener('import-status-changed', handleStatusChange);
        };
    }, [open, moduleFilter]);

    const getStatusTag = (status) => {
        switch (status) {
            case 'pending':
                return <Tag color="orange" icon={<ClockCircleOutlined />}>Chờ xử lý</Tag>;
            case 'processing':
                return <Tag color="blue" icon={<SyncOutlined spin />}>Đang xử lý</Tag>;
            case 'completed':
                return <Tag color="green" icon={<CheckCircleOutlined />}>Hoàn tất</Tag>;
            case 'failed':
                return <Tag color="red" icon={<CloseCircleOutlined />}>Thất bại</Tag>;
            default:
                return <Tag>{status}</Tag>;
        }
    };

    const columns = [
        {
            title: 'Tên file',
            dataIndex: 'original_filename',
            key: 'original_filename',
            render: (text) => <strong>{text || 'File Excel'}</strong>,
        },
        {
            title: 'Trạng thái',
            dataIndex: 'status',
            key: 'status',
            width: 130,
            align: 'center',
            render: (status) => getStatusTag(status),
        },
        {
            title: 'Thời gian',
            dataIndex: 'created_at',
            key: 'created_at',
            width: 180,
            render: (date, record) => (
                <div>
                    <div>{new Date(date).toLocaleString('vi-VN')}</div>
                    {record.execution_time && (
                        <div style={{ fontSize: 11, color: '#888' }}>
                            Thực thi: {record.execution_time}s
                        </div>
                    )}
                </div>
            ),
        },
        {
            title: 'Thao tác',
            key: 'action',
            width: 110,
            align: 'center',
            render: (_, record) => (
                <Button
                    type="primary"
                    ghost
                    size="small"
                    icon={<EyeOutlined />}
                    disabled={record.status === 'pending' || record.status === 'processing'}
                    onClick={() => {
                        onClose();
                        onSelectImport(record.id);
                    }}
                >
                    Chi tiết
                </Button>
            ),
        },
    ];

    return (
        <Modal
            title={
                <Space style={{ justifyContent: 'space-between', width: '100%', paddingRight: 30 }}>
                    <span>
                        <HistoryOutlined /> Lịch sử Import Excel {moduleFilter ? `- ${moduleNames[moduleFilter] || moduleFilter}` : ''}
                    </span>
                    <Button icon={<ReloadOutlined />} size="small" onClick={fetchImports} loading={loading}>
                        Làm mới
                    </Button>
                </Space>
            }
            open={open}
            onCancel={onClose}
            footer={[
                <Button key="close" onClick={onClose}>
                    Đóng
                </Button>,
            ]}
            width={700}
            destroyOnClose
        >
            <Table
                columns={columns}
                dataSource={imports.map((item) => ({ ...item, key: item.id }))}
                loading={loading}
                pagination={{ pageSize: 5, showSizeChanger: false }}
                size="small"
            />
        </Modal>
    );
};

export default ImportHistoryModal;
