import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Layout, Menu, theme, Badge, Avatar, Dropdown, Space, message } from 'antd';
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
} from '@ant-design/icons';

const { Header, Sider, Content } = Layout;

const MainLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);
    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();
    
    const { url, props } = usePage();

    // Show flash messages from backend
    useEffect(() => {
        if (props.flash?.success) message.success(props.flash.success);
        if (props.flash?.error) message.error(props.flash.error);
    }, [props.flash]);

    const menuItems = [
        {
            key: '/',
            icon: <DashboardOutlined />,
            label: <Link href="/">Dashboard</Link>,
        },
        {
            key: 'co-so',
            icon: <BankOutlined />,
            label: 'QL Cơ sở hạ tầng',
            children: [
                {
                    key: '/co-so',
                    label: <Link href="/co-so">Danh sách cơ sở</Link>,
                },
            ],
        },
        {
            key: 'khu-nha',
            icon: <HomeOutlined />,
            label: 'QL Khu nhà, Chức năng',
            children: [
                {
                    key: '/khu-nha',
                    label: <Link href="/khu-nha">Danh sách khu nhà</Link>,
                },
            ],
        },
        {
            key: 'phong',
            icon: <AppstoreOutlined />,
            label: 'QL Phòng',
            children: [
                {
                    key: '/phong',
                    label: <Link href="/phong">Danh sách phòng</Link>,
                },
            ],
        },
        {
            key: 'thiet-bi',
            icon: <ToolOutlined />,
            label: 'QL Thiết bị',
            children: [
                {
                    key: '/thiet-bi',
                    label: <Link href="/thiet-bi">Danh sách thiết bị</Link>,
                },
                {
                    key: '/lich-su-bao-duong',
                    label: <Link href="/lich-su-bao-duong">Lịch sử bảo dưỡng</Link>,
                },
            ],
        },
    ];

    const userMenuItems = [
        {
            key: 'profile',
            icon: <UserOutlined />,
            label: 'Thông tin cá nhân',
        },
        {
            key: 'settings',
            icon: <SettingOutlined />,
            label: 'Cài đặt',
        },
        {
            type: 'divider',
        },
        {
            key: 'logout',
            icon: <LogoutOutlined />,
            label: 'Đăng xuất',
            danger: true,
        },
    ];

    const getSelectedKey = () => {
        if (url.startsWith('/co-so')) return '/co-so';
        if (url.startsWith('/khu-nha')) return '/khu-nha';
        if (url.startsWith('/phong')) return '/phong';
        if (url.startsWith('/lich-su-bao-duong')) return '/lich-su-bao-duong';
        if (url.startsWith('/thiet-bi')) return '/thiet-bi';
        return url;
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
                    defaultOpenKeys={['co-so', 'khu-nha', 'phong', 'thiet-bi']}
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
                        <Dropdown menu={{ items: userMenuItems }} placement="bottomRight">
                            <Space style={{ cursor: 'pointer' }}>
                                <Avatar style={{ backgroundColor: '#1890ff' }} icon={<UserOutlined />} />
                                {!collapsed && <span>Admin</span>}
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

