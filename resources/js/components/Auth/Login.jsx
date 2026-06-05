import React, { useState, useEffect } from 'react';
import { useForm, usePage, Head } from '@inertiajs/react';
import { 
    Form, 
    Input, 
    Button, 
    Checkbox, 
    Card, 
    Typography, 
    Alert, 
    message 
} from 'antd';
import { UserOutlined, LockOutlined, LoginOutlined } from '@ant-design/icons';

const { Title, Text } = Typography;

const Login = () => {
    const { errors, flash } = usePage().props;
    const [loading, setLoading] = useState(false);
    
    const { data, setData, post, processing, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        if (flash?.success) {
            message.success(flash.success);
        }
        if (flash?.error) {
            message.error(flash.error);
        }
    }, [flash]);

    const handleSubmit = () => {
        setLoading(true);
        post('/login', {
            onFinish: () => {
                setLoading(false);
                reset('password');
            },
        });
    };

    return (
        <>
            <Head title="Đăng nhập" />
            <div 
                style={{ 
                    minHeight: '100vh',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    background: 'radial-gradient(circle at center, #0f172a 0%, #020617 100%)',
                    padding: '20px',
                    position: 'relative',
                    overflow: 'hidden',
                }}
            >
                {/* Local glow orbs for login page background decoration */}
                <div className="glow-orb orb-1" style={{ opacity: 0.25, zIndex: 1 }} />
                <div className="glow-orb orb-2" style={{ opacity: 0.2, zIndex: 1 }} />

                <Card
                    style={{
                        width: '100%',
                        maxWidth: 420,
                        background: 'rgba(255, 255, 255, 0.06)',
                        backdropFilter: 'blur(20px)',
                        WebkitBackdropFilter: 'blur(20px)',
                        border: '1px solid rgba(255, 255, 255, 0.12)',
                        boxShadow: '0 20px 50px rgba(0, 0, 0, 0.3)',
                        borderRadius: 24,
                        zIndex: 10,
                        position: 'relative',
                    }}
                    bodyStyle={{
                        padding: '40px 32px',
                    }}
                >
                    <div style={{ textAlign: 'center', marginBottom: 32 }}>
                        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', marginBottom: 16 }}>
                            <img 
                                src="/images/logoctuet.png" 
                                alt="Logo" 
                                style={{ 
                                    width: 100, 
                                    height: 100,
                                    filter: 'drop-shadow(0 0 16px rgba(59, 130, 246, 0.4))',
                                }} 
                                onError={(e) => { e.target.src = '/favicon.png'; }}
                            />
                        </div>
                        <Typography.Title 
                            level={3} 
                            style={{ 
                                margin: 0, 
                                color: '#ffffff', 
                                fontSize: 20, 
                                fontWeight: 700,
                                letterSpacing: '0.5px',
                            }}
                        >
                            HỆ THỐNG QLCSVC
                        </Typography.Title>
                        <Text style={{ fontSize: 13, color: '#94a3b8', marginTop: 4, display: 'block' }} >
                            Trường Đại Học Kỹ Thuật Công Nghệ Cần Thơ
                        </Text>
                    </div>

                    {errors.email && (
                        <Alert
                            message={errors.email}
                            type="error"
                            showIcon
                            style={{ marginBottom: 24, borderRadius: 10, background: 'rgba(239, 68, 68, 0.15)', border: '1px solid rgba(239, 68, 68, 0.25)', color: '#fca5a5' }}
                        />
                    )}

                    <Form
                        layout="vertical"
                        onFinish={handleSubmit}
                        autoComplete="off"
                        size="large"
                    >
                        <Form.Item
                            label={<span style={{ fontWeight: 500, color: '#e2e8f0' }}>Email</span>}
                            validateStatus={errors.email ? 'error' : ''}
                            style={{ marginBottom: 20 }}
                        >
                            <Input
                                prefix={<UserOutlined style={{ color: '#52a3ff' }} />}
                                placeholder="Nhập email của bạn"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 12,
                                    fontSize: 15,
                                    background: 'rgba(255, 255, 255, 0.05)',
                                    border: '1px solid rgba(255, 255, 255, 0.15)',
                                    color: '#fff',
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            label={<span style={{ fontWeight: 500, color: '#e2e8f0' }}>Mật khẩu</span>}
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                            style={{ marginBottom: 20 }}
                        >
                            <Input.Password
                                prefix={<LockOutlined style={{ color: '#52a3ff' }} />}
                                placeholder="Nhập mật khẩu"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 12,
                                    fontSize: 15,
                                    background: 'rgba(255, 255, 255, 0.05)',
                                    border: '1px solid rgba(255, 255, 255, 0.15)',
                                    color: '#fff',
                                }}
                            />
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 24 }}>
                            <Checkbox
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                style={{ color: '#cbd5e1' }}
                            >
                                Ghi nhớ đăng nhập
                            </Checkbox>
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 0 }}>
                            <Button
                                type="primary"
                                htmlType="submit"
                                loading={processing || loading}
                                block
                                style={{
                                    height: 50,
                                    borderRadius: 12,
                                    fontSize: 16,
                                    fontWeight: 600,
                                    background: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
                                    border: 'none',
                                    boxShadow: '0 8px 24px rgba(59, 130, 246, 0.45)',
                                }}
                                icon={<LoginOutlined />}
                            >
                                Đăng nhập
                            </Button>
                        </Form.Item>
                    </Form>

                    <div 
                        style={{ 
                            textAlign: 'center', 
                            marginTop: 24,
                            paddingTop: 24,
                            borderTop: '1px solid rgba(255, 255, 255, 0.1)',
                        }}
                    >
                        <Text style={{ fontSize: 13, color: '#64748b' }}>
                            © Trường Đại Học Kỹ Thuật Công Nghệ Cần Thơ
                        </Text>
                    </div>
                </Card>
            </div>
        </>
    );
};

export default Login;
