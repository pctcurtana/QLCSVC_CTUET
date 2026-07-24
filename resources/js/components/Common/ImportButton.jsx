import React, { useRef, useState } from 'react';
import { Button, Space, Alert, Table, Tag, Tooltip } from 'antd';
import { UploadOutlined, DownloadOutlined } from '@ant-design/icons';
import { router, usePage } from '@inertiajs/react';

/**
 * ImportButton - Nút import đơn giản dùng chung cho tất cả các module.
 *
 * Props:
 *   importUrl     : URL để POST file Excel (VD: '/co-so/import')
 *   templateUrl   : URL để GET download template (VD: '/co-so/template')
 *   label         : nhãn hiển thị (VD: 'Cơ sở')
 */
const ImportButton = ({ importUrl, templateUrl, label }) => {
    const fileInputRef = useRef(null);
    const [uploading, setUploading] = useState(false);

    const handleFileChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setUploading(true);
        const formData = new FormData();
        formData.append('file', file);

        router.post(importUrl, formData, {
            forceFormData: true,
            onFinish: () => {
                setUploading(false);
                // reset input để có thể chọn lại cùng file
                e.target.value = '';
            },
        });
    };

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

            {/* Nút Import - ẩn file input thực, click button kích hoạt */}
            <input
                ref={fileInputRef}
                type="file"
                accept=".xlsx,.xls"
                style={{ display: 'none' }}
                onChange={handleFileChange}
                id={`import-file-${label}`}
            />
            <Button
                icon={<UploadOutlined />}
                size="large"
                loading={uploading}
                onClick={() => fileInputRef.current?.click()}
            >
                Nhập từ Excel
            </Button>
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
                    <Space>
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

            {hasErrors && (
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
