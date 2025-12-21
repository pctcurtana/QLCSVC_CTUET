import React, { useState, useEffect, useMemo } from 'react';
import { usePage, router } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Table,
    Select,
    Button,
    Checkbox,
    Typography,
    Space,
    message,
    Spin,
    Empty,
    Tag,
} from 'antd';
import {
    SaveOutlined,
    ReloadOutlined,
    UserOutlined,
    PlusOutlined,
    MinusOutlined,
} from '@ant-design/icons';
import axios from 'axios';

const { Title, Text } = Typography;
const { Option } = Select;

const PermissionIndex = ({ users, screens }) => {
    const [selectedUserId, setSelectedUserId] = useState(null);
    const [permissions, setPermissions] = useState({});
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [originalPermissions, setOriginalPermissions] = useState({});
    const [expandedRowKeys, setExpandedRowKeys] = useState([]);

    // Chuyển screens tree thành data cho table với expandable
    const tableData = useMemo(() => {
        const result = [];
        
        screens.forEach((screen) => {
            const item = {
                key: screen.id,
                id: screen.id,
                name: screen.name,
                code: screen.code,
                route: screen.route,
                isParent: screen.children && screen.children.length > 0,
                children: [],
                childIds: [], // Lưu danh sách ID của children
            };

            if (screen.children && screen.children.length > 0) {
                item.children = screen.children.map((child) => ({
                    key: child.id,
                    id: child.id,
                    name: child.name,
                    code: child.code,
                    route: child.route,
                    isParent: false,
                    parentId: screen.id,
                }));
                item.childIds = screen.children.map((child) => child.id);
            }

            result.push(item);
        });

        return result;
    }, [screens]);

    // Lấy tất cả screen IDs (bao gồm cả children)
    const allScreenIds = useMemo(() => {
        const ids = [];
        tableData.forEach((item) => {
            ids.push(item.id);
            if (item.children) {
                item.children.forEach((child) => ids.push(child.id));
            }
        });
        return ids;
    }, [tableData]);

    // Tìm parent của một screen
    const findParent = (screenId) => {
        for (const item of tableData) {
            if (item.childIds && item.childIds.includes(screenId)) {
                return item;
            }
        }
        return null;
    };

    // Tìm screen theo ID
    const findScreen = (screenId) => {
        for (const item of tableData) {
            if (item.id === screenId) return item;
            if (item.children) {
                for (const child of item.children) {
                    if (child.id === screenId) return child;
                }
            }
        }
        return null;
    };

    // Load permissions khi chọn user
    useEffect(() => {
        if (selectedUserId) {
            loadUserPermissions(selectedUserId);
        } else {
            setPermissions({});
            setOriginalPermissions({});
        }
    }, [selectedUserId]);

    const loadUserPermissions = async (userId) => {
        setLoading(true);
        try {
            const response = await axios.get(`/phan-quyen/${userId}/permissions`);
            const perms = response.data || {};
            setPermissions(perms);
            setOriginalPermissions(JSON.parse(JSON.stringify(perms)));
        } catch (error) {
            message.error('Không thể tải phân quyền');
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    // Xử lý thay đổi checkbox - CÓ CASCADE
    const handlePermissionChange = (screenId, permissionType, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            const screen = findScreen(screenId);
            
            // Hàm helper để set permission cho 1 screen
            const setPermission = (id, type, value) => {
                if (!newPermissions[id]) {
                    newPermissions[id] = {
                        can_view: false,
                        can_create: false,
                        can_edit: false,
                        can_delete: false,
                    };
                }
                newPermissions[id][type] = value;
                
                // Nếu bỏ tích can_view, tự động bỏ các quyền khác
                if (type === 'can_view' && !value) {
                    newPermissions[id].can_create = false;
                    newPermissions[id].can_edit = false;
                    newPermissions[id].can_delete = false;
                }
                
                // Nếu tích quyền khác, tự động tích can_view
                if (type !== 'can_view' && value) {
                    newPermissions[id].can_view = true;
                }
            };

            // Set cho chính nó
            setPermission(screenId, permissionType, checked);

            // Nếu là parent -> cascade xuống tất cả children
            if (screen && screen.isParent && screen.childIds) {
                screen.childIds.forEach((childId) => {
                    setPermission(childId, permissionType, checked);
                });
            }

            // Nếu là child
            if (screen && screen.parentId) {
                const parent = findParent(screenId);
                if (parent) {
                    if (checked) {
                        // Nếu tích child -> tích parent
                        setPermission(screen.parentId, permissionType, true);
                    } else {
                        // Nếu bỏ tích child -> kiểm tra xem còn child nào được tích không
                        const anyChildChecked = parent.childIds.some((childId) => {
                            if (childId === screenId) return false; // Bỏ qua child hiện tại vì đã set ở trên
                            return newPermissions[childId]?.[permissionType];
                        });
                        // Nếu không còn child nào được tích -> bỏ tích parent
                        if (!anyChildChecked) {
                            setPermission(screen.parentId, permissionType, false);
                        }
                    }
                }
            }

            return newPermissions;
        });
    };

    // Xử lý tích/bỏ tích cả dòng - CÓ CASCADE
    const handleRowCheckAll = (screenId, checked, record) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            
            // Hàm helper để set tất cả permission cho 1 screen
            const setAllPermissions = (id, value) => {
                newPermissions[id] = {
                    can_view: value,
                    can_create: value,
                    can_edit: value,
                    can_delete: value,
                };
            };

            // Cập nhật cho chính nó
            setAllPermissions(screenId, checked);

            // Nếu là parent, cập nhật cho tất cả children
            if (record.isParent && record.childIds) {
                record.childIds.forEach((childId) => {
                    setAllPermissions(childId, checked);
                });
            }

            // Nếu là child
            if (record.parentId) {
                const parent = findParent(screenId);
                if (parent) {
                    if (checked) {
                        // Nếu tích child -> tích parent
                        setAllPermissions(record.parentId, true);
                    } else {
                        // Nếu bỏ tích child -> kiểm tra xem còn child nào được tích không
                        const anyChildHasPermission = parent.childIds.some((childId) => {
                            if (childId === screenId) return false;
                            const perms = newPermissions[childId] || {};
                            return perms.can_view || perms.can_create || perms.can_edit || perms.can_delete;
                        });
                        if (!anyChildHasPermission) {
                            setAllPermissions(record.parentId, false);
                        }
                    }
                }
            }

            return newPermissions;
        });
    };

    // Xử lý tích/bỏ tích cả cột
    const handleColumnCheckAll = (permissionType, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            allScreenIds.forEach((screenId) => {
                if (!newPermissions[screenId]) {
                    newPermissions[screenId] = {
                        can_view: false,
                        can_create: false,
                        can_edit: false,
                        can_delete: false,
                    };
                }
                
                if (permissionType === 'can_view') {
                    newPermissions[screenId].can_view = checked;
                    if (!checked) {
                        newPermissions[screenId].can_create = false;
                        newPermissions[screenId].can_edit = false;
                        newPermissions[screenId].can_delete = false;
                    }
                } else {
                    newPermissions[screenId][permissionType] = checked;
                    if (checked) {
                        newPermissions[screenId].can_view = true;
                    }
                }
            });
            return newPermissions;
        });
    };

    // Lưu phân quyền
    const handleSave = () => {
        if (!selectedUserId) {
            message.warning('Vui lòng chọn người dùng');
            return;
        }

        setSaving(true);
        const permissionsArray = Object.entries(permissions).map(([screenId, perms]) => ({
            screen_id: parseInt(screenId),
            ...perms,
        }));

        router.post(
            `/phan-quyen/${selectedUserId}/permissions`,
            { permissions: permissionsArray },
            {
                onSuccess: () => {
                    setOriginalPermissions(JSON.parse(JSON.stringify(permissions)));
                },
                onFinish: () => {
                    setSaving(false);
                },
            }
        );
    };

    // Reset về permissions ban đầu
    const handleReset = () => {
        setPermissions(JSON.parse(JSON.stringify(originalPermissions)));
        message.info('Đã khôi phục phân quyền ban đầu');
    };

    // Kiểm tra có thay đổi không
    const hasChanges = JSON.stringify(permissions) !== JSON.stringify(originalPermissions);

    // Kiểm tra checkbox cột có được tích hết không
    const isColumnAllChecked = (permissionType) => {
        return allScreenIds.every((id) => permissions[id]?.[permissionType]);
    };

    const isColumnIndeterminate = (permissionType) => {
        const checkedCount = allScreenIds.filter((id) => permissions[id]?.[permissionType]).length;
        return checkedCount > 0 && checkedCount < allScreenIds.length;
    };

    // Kiểm tra row có tích hết không (bao gồm children nếu là parent)
    const isRowAllChecked = (record) => {
        // Nếu là parent, chỉ kiểm tra children
        if (record.isParent && record.childIds && record.childIds.length > 0) {
            return record.childIds.every((id) => {
                const perms = permissions[id] || {};
                return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            });
        }
        
        // Nếu không phải parent, kiểm tra chính nó
        const perms = permissions[record.id] || {};
        return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
    };

    const isRowIndeterminate = (record) => {
        // Nếu là parent, kiểm tra children
        if (record.isParent && record.childIds && record.childIds.length > 0) {
            let totalChecked = 0;
            let totalUnchecked = 0;
            let totalPartial = 0;
            
            record.childIds.forEach((id) => {
                const perms = permissions[id] || {};
                const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
                const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
                
                if (allPerms) totalChecked++;
                else if (noPerms) totalUnchecked++;
                else totalPartial++;
            });
            
            // Indeterminate khi có mix hoặc có partial
            return totalPartial > 0 || (totalChecked > 0 && totalUnchecked > 0);
        }
        
        // Nếu không phải parent, kiểm tra chính nó có partial không
        const perms = permissions[record.id] || {};
        const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
        
        return !allPerms && !noPerms;
    };

    // Kiểm tra permission của 1 cell có checked không (bao gồm logic parent-child)
    const isCellChecked = (record, permissionType) => {
        // Nếu là parent, checked khi TẤT CẢ children đều được tích
        if (record.isParent && record.childIds && record.childIds.length > 0) {
            return record.childIds.every((id) => permissions[id]?.[permissionType]);
        }
        return permissions[record.id]?.[permissionType] || false;
    };

    // Kiểm tra cell có indeterminate không (chỉ cho parent)
    const isCellIndeterminate = (record, permissionType) => {
        if (!record.isParent || !record.childIds || record.childIds.length === 0) return false;
        
        const childrenCheckedCount = record.childIds.filter(
            (id) => permissions[id]?.[permissionType]
        ).length;
        
        // Indeterminate khi một số (không phải tất cả, không phải 0) children được tích
        return childrenCheckedCount > 0 && childrenCheckedCount < record.childIds.length;
    };

    // Mở rộng/thu gọn tất cả
    const handleExpandAll = () => {
        const allParentKeys = tableData
            .filter((item) => item.children && item.children.length > 0)
            .map((item) => item.key);
        setExpandedRowKeys(allParentKeys);
    };

    const handleCollapseAll = () => {
        setExpandedRowKeys([]);
    };

    // Columns cho table
    const columns = [
        {
            title: 'Tên chức năng',
            dataIndex: 'name',
            key: 'name',
            width: 350,
            render: (text, record) => (
                <Space>
                    <Text strong={record.isParent}>{text}</Text>
                    {record.route && (
                        <Tag color="blue" style={{ fontSize: 11 }}>
                            {record.route}
                        </Tag>
                    )}
                </Space>
            ),
        },
        {
            title: (
                <Space direction="vertical" align="center" size={0}>
                    <Checkbox
                        checked={isColumnAllChecked('can_view')}
                        indeterminate={isColumnIndeterminate('can_view')}
                        onChange={(e) => handleColumnCheckAll('can_view', e.target.checked)}
                        disabled={!selectedUserId}
                    />
                    <Text>Xem</Text>
                </Space>
            ),
            dataIndex: 'can_view',
            key: 'can_view',
            width: 100,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={isCellChecked(record, 'can_view')}
                    indeterminate={isCellIndeterminate(record, 'can_view')}
                    onChange={(e) => handlePermissionChange(record.id, 'can_view', e.target.checked)}
                    disabled={!selectedUserId}
                />
            ),
        },
        {
            title: (
                <Space direction="vertical" align="center" size={0}>
                    <Checkbox
                        checked={isColumnAllChecked('can_create')}
                        indeterminate={isColumnIndeterminate('can_create')}
                        onChange={(e) => handleColumnCheckAll('can_create', e.target.checked)}
                        disabled={!selectedUserId}
                    />
                    <Text>Thêm</Text>
                </Space>
            ),
            dataIndex: 'can_create',
            key: 'can_create',
            width: 100,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={isCellChecked(record, 'can_create')}
                    indeterminate={isCellIndeterminate(record, 'can_create')}
                    onChange={(e) => handlePermissionChange(record.id, 'can_create', e.target.checked)}
                    disabled={!selectedUserId}
                />
            ),
        },
        {
            title: (
                <Space direction="vertical" align="center" size={0}>
                    <Checkbox
                        checked={isColumnAllChecked('can_edit')}
                        indeterminate={isColumnIndeterminate('can_edit')}
                        onChange={(e) => handleColumnCheckAll('can_edit', e.target.checked)}
                        disabled={!selectedUserId}
                    />
                    <Text>Sửa</Text>
                </Space>
            ),
            dataIndex: 'can_edit',
            key: 'can_edit',
            width: 100,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={isCellChecked(record, 'can_edit')}
                    indeterminate={isCellIndeterminate(record, 'can_edit')}
                    onChange={(e) => handlePermissionChange(record.id, 'can_edit', e.target.checked)}
                    disabled={!selectedUserId}
                />
            ),
        },
        {
            title: (
                <Space direction="vertical" align="center" size={0}>
                    <Checkbox
                        checked={isColumnAllChecked('can_delete')}
                        indeterminate={isColumnIndeterminate('can_delete')}
                        onChange={(e) => handleColumnCheckAll('can_delete', e.target.checked)}
                        disabled={!selectedUserId}
                    />
                    <Text>Xóa</Text>
                </Space>
            ),
            dataIndex: 'can_delete',
            key: 'can_delete',
            width: 100,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={isCellChecked(record, 'can_delete')}
                    indeterminate={isCellIndeterminate(record, 'can_delete')}
                    onChange={(e) => handlePermissionChange(record.id, 'can_delete', e.target.checked)}
                    disabled={!selectedUserId}
                />
            ),
        },
        {
            title: 'Tất cả',
            key: 'all',
            width: 100,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={isRowAllChecked(record)}
                    indeterminate={isRowIndeterminate(record)}
                    onChange={(e) => handleRowCheckAll(record.id, e.target.checked, record)}
                    disabled={!selectedUserId}
                />
            ),
        },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>Phân quyền người dùng</Title>
                </div>

                <Card>
                    <Space direction="vertical" size="middle" style={{ width: '100%' }}>
                        {/* Chọn user */}
                        <div style={{ display: 'flex', alignItems: 'center', gap: 16, flexWrap: 'wrap' }}>
                            <Text strong>Chọn người dùng:</Text>
                            <Select
                                style={{ width: 350 }}
                                placeholder="-- Chọn người dùng để phân quyền --"
                                value={selectedUserId}
                                onChange={setSelectedUserId}
                                showSearch
                                optionFilterProp="children"
                                filterOption={(input, option) =>
                                    option.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
                                }
                            >
                                {users.map((user) => (
                                    <Option key={user.id} value={user.id}>
                                        <Space>
                                            <UserOutlined />
                                            {user.name} - {user.email}
                                        </Space>
                                    </Option>
                                ))}
                            </Select>

                            {selectedUserId && (
                                <Space>
                                    <Button
                                        type="primary"
                                        icon={<SaveOutlined />}
                                        onClick={handleSave}
                                        loading={saving}
                                        disabled={!hasChanges}
                                    >
                                        Lưu phân quyền
                                    </Button>
                                    <Button
                                        icon={<ReloadOutlined />}
                                        onClick={handleReset}
                                        disabled={!hasChanges}
                                    >
                                        Khôi phục
                                    </Button>
                                </Space>
                            )}
                        </div>

                        {/* Nút mở rộng/thu gọn */}
                        {selectedUserId && (
                            <Space>
                                <Button 
                                    size="small" 
                                    icon={<PlusOutlined />}
                                    onClick={handleExpandAll}
                                >
                                    Mở rộng tất cả
                                </Button>
                                <Button 
                                    size="small" 
                                    icon={<MinusOutlined />}
                                    onClick={handleCollapseAll}
                                >
                                    Thu gọn tất cả
                                </Button>
                            </Space>
                        )}

                        {/* Bảng phân quyền */}
                        <Spin spinning={loading}>
                            {selectedUserId ? (
                                <Table
                                    columns={columns}
                                    dataSource={tableData}
                                    pagination={false}
                                    bordered
                                    size="middle"
                                    expandable={{
                                        expandedRowKeys: expandedRowKeys,
                                        onExpandedRowsChange: (keys) => setExpandedRowKeys(keys),
                                        rowExpandable: (record) => record.children && record.children.length > 0,
                                    }}
                                    rowClassName={(record) => 
                                        record.isParent ? 'parent-row' : ''
                                    }
                                />
                            ) : (
                                <Empty
                                    description="Vui lòng chọn người dùng để phân quyền"
                                    style={{ padding: '60px 0' }}
                                />
                            )}
                        </Spin>

                        {/* Chú thích */}
                        {selectedUserId && (
                            <div style={{ marginTop: 16, padding: 16, background: '#f5f5f5', borderRadius: 8 }}>
                                <Text strong>Chú thích:</Text>
                                <ul style={{ margin: '8px 0 0 0', paddingLeft: 20 }}>
                                    <li>
                                        <Text>Tích vào <strong>nhóm chức năng</strong> sẽ tự động tích cho <strong>tất cả chức năng con</strong> bên trong</Text>
                                    </li>
                                    <li>
                                        <Text><strong>Xem:</strong> Cho phép truy cập và xem dữ liệu của màn hình</Text>
                                    </li>
                                    <li>
                                        <Text><strong>Thêm:</strong> Cho phép thêm mới dữ liệu</Text>
                                    </li>
                                    <li>
                                        <Text><strong>Sửa:</strong> Cho phép chỉnh sửa dữ liệu hiện có</Text>
                                    </li>
                                    <li>
                                        <Text><strong>Xóa:</strong> Cho phép xóa dữ liệu</Text>
                                    </li>
                                    <li>
                                        <Text type="secondary">Admin có tất cả quyền mặc định, không cần phân quyền</Text>
                                    </li>
                                </ul>
                            </div>
                        )}
                    </Space>
                </Card>
            </Space>

            <style>{`
                .parent-row {
                    background-color: #fafafa;
                    font-weight: 500;
                }
                .parent-row:hover > td {
                    background-color: #f0f0f0 !important;
                }
                .ant-table-row-expand-icon {
                    margin-right: 8px;
                }
            `}</style>
        </MainLayout>
    );
};

export default PermissionIndex;
