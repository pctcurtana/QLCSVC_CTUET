import React, { useState, useEffect, useMemo } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { Layout, Menu, theme, Badge, Avatar, Dropdown, Space, message, Modal } from 'antd';
import {
    DashboardOutlined,
    BankOutlined,
    HomeOutlined,
    AppstoreOutlined,
    ToolOutlined,
    HistoryOutlined,
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    UserOutlined,
    LogoutOutlined,
    SettingOutlined,
    ExclamationCircleOutlined,
    TeamOutlined,
    KeyOutlined,
} from '@ant-design/icons';

const { Header, Sider, Content } = Layout;
const { confirm } = Modal;

// Map icon string to component
const iconMap = {
    DashboardOutlined: <DashboardOutlined />,
    BankOutlined: <BankOutlined />,
    HomeOutlined: <HomeOutlined />,
    AppstoreOutlined: <AppstoreOutlined />,
    ToolOutlined: <ToolOutlined />,
    HistoryOutlined: <HistoryOutlined />,
    SettingOutlined: <SettingOutlined />,
    TeamOutlined: <TeamOutlined />,
    KeyOutlined: <KeyOutlined />,
    UserOutlined: <UserOutlined />,
};

const MainLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);
    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();
    
    const { url, props } = usePage();
    const { auth, menuScreens, userPermissions } = props;
    const user = auth?.user;

    // Show flash messages from backend
    useEffect(() => {
        if (props.flash?.success) message.success(props.flash.success);
        if (props.flash?.error) message.error(props.flash.error);
    }, [props.flash]);

    const handleLogout = () => {
        confirm({
            title: 'Xác nhận đăng xuất',
            icon: <ExclamationCircleOutlined />,
            content: 'Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?',
            okText: 'Đăng xuất',
            okType: 'danger',
            cancelText: 'Hủy',
            onOk() {
                router.post('/logout');
            },
        });
    };

    // Tạo menu items từ menuScreens
    const menuItems = useMemo(() => {
        if (!menuScreens || menuScreens.length === 0) {
            // Fallback menu khi chưa có data
            return [
                {
                    key: '/',
                    icon: <DashboardOutlined />,
                    label: <Link href="/">Dashboard</Link>,
                },
            ];
        }

        return menuScreens.map((screen) => {
            const icon = iconMap[screen.icon] || <AppstoreOutlined />;

            // Nếu có children -> tạo submenu
            if (screen.children && screen.children.length > 0) {
                return {
                    key: screen.code,
                    icon: icon,
                    label: screen.name,
                    children: screen.children.map((child) => ({
                        key: child.route || child.code,
                        label: <Link href={child.route}>{child.name}</Link>,
                    })),
                };
            }

            // Nếu không có children và có route -> menu item đơn
            if (screen.route) {
                return {
                    key: screen.route,
                    icon: icon,
                    label: <Link href={screen.route}>{screen.name}</Link>,
                };
            }

            // Fallback
            return {
                key: screen.code,
                icon: icon,
                label: screen.name,
            };
        });
    }, [menuScreens]);

    // Lấy default open keys (chỉ các menu có children)
    const defaultOpenKeys = useMemo(() => {
        if (!menuScreens) return [];
        return menuScreens
            .filter((screen) => screen.children && screen.children.length > 0)
            .map((screen) => screen.code);
    }, [menuScreens]);

    const userMenuItems = [
        {
            key: 'user-info',
            label: (
                <div style={{ padding: '8px 0' }}>
                    <div style={{ fontWeight: 600, color: '#1a365d' }}>{user?.name}</div>
                    <div style={{ fontSize: 12, color: '#666' }}>{user?.email}</div>
                    <div style={{ 
                        fontSize: 11, 
                        color: '#fff', 
                        background: user?.role === 'admin' ? '#f5222d' : '#1890ff',
                        padding: '2px 8px',
                        borderRadius: 4,
                        marginTop: 4,
                        display: 'inline-block',
                    }}>
                        {user?.role === 'admin' ? 'Quản trị viên' : 'Người dùng'}
                    </div>
                </div>
            ),
            disabled: true,
        },
        {
            type: 'divider',
        },
        {
            key: 'logout',
            icon: <LogoutOutlined />,
            label: 'Đăng xuất',
            danger: true,
            onClick: handleLogout,
        },
    ];

    const getSelectedKey = () => {
        // Tìm key phù hợp nhất với URL hiện tại
        const path = url.split('?')[0]; // Bỏ query string
        
        // Kiểm tra exact match trước
        if (path === '/') return '/';
        
        // Kiểm tra các route cụ thể
        const routes = [
            '/co-so', '/khu-nha', '/phong', '/thiet-bi', 
            '/lich-su-bao-duong', '/nguoi-dung', '/phan-quyen'
        ];
        
        for (const route of routes) {
            if (path.startsWith(route)) {
                return route;
            }
        }
        
        return path;
    };

    // Role badge color
    const getRoleBadgeColor = () => {
        return user?.role === 'admin' ? '#f5222d' : '#1890ff';
    };

    return (
        <Layout style={{ minHeight: '100vh' }}>
            <Sider 
                trigger={null} 
                theme="light"
                collapsible 
                collapsed={collapsed}
                style={{
                    overflow: 'auto',
                    height: '100vh',
                    position: 'fixed',
                    left: 0,
                    top: 0,
                    bottom: 0,
                }}
            >
                <div style={{ 
                    height: 64, 
                    margin: 16, 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center',
                    color: 'black',
                    fontSize: collapsed ? '16px' : '18px',
                    fontWeight: 'bold',
                }}>
                    {collapsed ? 'CTUT' : 'QLCSVC CTUT'}
                </div>
                <Menu
                    theme="light"
                    mode="inline"
                    selectedKeys={[getSelectedKey()]}
                    defaultOpenKeys={defaultOpenKeys}
                    items={menuItems}
                />
            </Sider>
            <Layout style={{ marginLeft: collapsed ? 80 : 200, transition: 'all 0.2s' }}>
                <Header
                    style={{
                        padding: '0 24px',
                        background: colorBgContainer,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        position: 'sticky',
                        top: 0,
                        zIndex: 1,
                        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                    }}
                >
                    <div style={{ fontSize: '18px', cursor: 'pointer' }} onClick={() => setCollapsed(!collapsed)}>
                        {collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
                    </div>
                    <Space size="large">
                        <Dropdown menu={{ items: userMenuItems }} placement="bottomRight" trigger={['click']}>
                            <Space style={{ cursor: 'pointer' }}>
                                <Badge dot color={getRoleBadgeColor()}>
                                    <Avatar 
                                        style={{ 
                                            backgroundColor: user?.role === 'admin' ? '#f5222d' : '#1890ff' 
                                        }} 
                                        icon={<UserOutlined />} 
                                    />
                                </Badge>
                                <span style={{ fontWeight: 500 }}>{user?.name || 'Người dùng'}</span>
                            </Space>
                        </Dropdown>
                    </Space>
                </Header>
                <Content
                    style={{
                        margin: '24px 16px',
                        padding: 24,
                        minHeight: 280,
                        background: colorBgContainer,
                        borderRadius: borderRadiusLG,
                    }}
                >
                    {children}
                </Content>
            </Layout>
        </Layout>
    );
};

export default MainLayout;
