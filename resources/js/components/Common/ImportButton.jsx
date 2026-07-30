import React, { useRef, useState, useEffect } from 'react';
import { Button, Space, Alert, Table, Tag, Tooltip } from 'antd';
import { UploadOutlined, DownloadOutlined, HistoryOutlined } from '@ant-design/icons';
import { router } from '@inertiajs/react';
import axios from 'axios';
import ImportHistoryModal from './ImportHistoryModal';

const moduleMap = {
    'Cơ sở': 'co_so',
    'Khu nhà': 'khu_nha',
    'Toà nhà': 'khu_nha',
    'Phòng': 'phong',
    'Thiết bị': 'thiet_bi',
};

/**
 * ImportButton - Nút import dùng chung cho tất cả các module.
 *
 * Props:
 *   importUrl     : URL để POST file Excel (VD: '/co-so/import')
 *   templateUrl   : URL để GET download template (VD: '/co-so/template')
 *   label         : nhãn hiển thị (VD: 'Cơ sở')
 */
const ImportButton = ({ importUrl, templateUrl, label }) => {
    const fileInputRef = useRef(null);
    const [uploading, setUploading] = useState(false);
    const [historyVisible, setHistoryVisible] = useState(false);
    const [hasActiveImport, setHasActiveImport] = useState(false);

    const checkActiveImportStatus = async () => {
        try {
            const res = await axios.get('/imports/status');
            setHasActiveImport(!!res.data?.has_active);
        } catch (e) {
            // Ignore error
        }
    };

    useEffect(() => {
        checkActiveImportStatus();

        const handleStatusChange = () => {
            checkActiveImportStatus();
        };

        window.addEventListener('import-status-changed', handleStatusChange);
        return () => {
            window.removeEventListener('import-status-changed', handleStatusChange);
        };
    }, []);

    const handleFileChange = (e) => {
        const file = e.target.files?.[0];
        if (!file || uploading || hasActiveImport) return;

        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);

        router.post(importUrl, formData, {
            forceFormData: true,
            onSuccess: () => {
                setHasActiveImport(true);
            },
            onFinish: () => {
                setUploading(false);
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
            onError: () => {
                setUploading(false);
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
                checkActiveImportStatus();
            },
        });
    };

    const handleSelectImportFromHistory = (importId) => {
        window.dispatchEvent(new CustomEvent('open-import-detail-modal', { detail: { importId } }));
    };

    const isButtonDisabled = uploading || hasActiveImport;

    return (
        <Space>
            {/* Nút Tải template */}
            <Tooltip title={`Tải file Excel mẫu cho ${label}`}>
                <a href={templateUrl} download>
                    <Button icon={<DownloadOutlined />} size="large">
                        Tải mẫu
                    </Button>
                </a>
            </Tooltip>

            {/* Nút Xem lịch sử import */}
            <Tooltip title={`Xem lịch sử các lần import ${label}`}>
                <Button
                    icon={<HistoryOutlined />}
                    size="large"
                    onClick={() => setHistoryVisible(true)}
                >
                    Lịch sử
                </Button>
            </Tooltip>

            {/* Nút Import - ẩn file input thực, click button kích hoạt */}
            <input
                ref={fileInputRef}
                type="file"
                accept=".xlsx,.xls"
                style={{ display: 'none' }}
                onChange={handleFileChange}
                id={`import-file-${label}`}
                disabled={isButtonDisabled}
            />
            <Tooltip title={hasActiveImport ? 'Hệ thống đang xử lý file import. Vui lòng chờ hoàn tất.' : ''}>
                <Button
                    icon={<UploadOutlined />}
                    size="large"
                    loading={uploading || hasActiveImport}
                    disabled={isButtonDisabled}
                    onClick={() => !isButtonDisabled && fileInputRef.current?.click()}
                >
                    {hasActiveImport ? 'Đang import...' : 'Nhập từ Excel'}
                </Button>
            </Tooltip>

            <ImportHistoryModal
                open={historyVisible}
                onClose={() => setHistoryVisible(false)}
                onSelectImport={handleSelectImportFromHistory}
                moduleFilter={moduleMap[label] || null}
            />
        </Space>
    );
};

/**
 * ImportResult - Hiển thị kết quả sau khi import.
 *
 * Props:
 *   result: object { total, created, updated, errors, error_details }
 */
const ImportResult = ({ result }) => {
    if (!result) return null;

    const hasErrors = result.errors > 0;

    const errorColumns = [
        {
            title: 'Dòng',
            dataIndex: 'row',
            key: 'row',
            width: 70,
            align: 'center',
            render: (row) => <Tag color="red">Dòng {row}</Tag>,
        },
        {
            title: 'Trường',
            dataIndex: 'field',
            key: 'field',
            width: 140,
            render: (field) => field ? <code>{field}</code> : <span style={{ color: '#999' }}>—</span>,
        },
        {
            title: 'Lỗi',
            dataIndex: 'message',
            key: 'message',
        },
    ];

    return (
        <div style={{ marginTop: 12 }}>
            <Alert
                type={hasErrors && result.created + result.updated === 0 ? 'error' : hasErrors ? 'warning' : 'success'}
                showIcon
                message={
                    <Space wrap style={{ gap: 8 }}>
                        <span>
                            Tổng: <strong>{result.total}</strong> dòng
                        </span>
                        <span>|</span>
                        <span style={{ color: '#52c41a' }}>
                            Tạo mới: <strong>{result.created}</strong>
                        </span>
                        <span>|</span>
                        <span style={{ color: '#1890ff' }}>
                            Cập nhật: <strong>{result.updated}</strong>
                        </span>
                        {hasErrors && (
                            <>
                                <span>|</span>
                                <span style={{ color: '#ff4d4f' }}>
                                    Lỗi: <strong>{result.errors}</strong> dòng
                                </span>
                            </>
                        )}
                        {(result.execution_time !== undefined && result.execution_time !== null) && (
                            <>
                                <span>|</span>
                                <span>
                                    Thời gian xử lý: <strong>{result.execution_time}</strong> giây
                                </span>
                            </>
                        )}
                    </Space>
                }
            />

            {hasErrors && Array.isArray(result.error_details) && result.error_details.length > 0 && (
                <Table
                    style={{ marginTop: 8 }}
                    size="small"
                    columns={errorColumns}
                    dataSource={result.error_details.map((e, i) => ({ ...e, key: i }))}
                    pagination={{ pageSize: 10, showSizeChanger: false }}
                    scroll={{ x: 600 }}
                />
            )}
        </div>
    );
};

export { ImportButton, ImportResult };
