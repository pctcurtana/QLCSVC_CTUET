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
                    background: 'linear-gradient(135deg, #1a365d 0%, #244380 50%, #2d5aa3 100%)',
                    padding: '20px',
                }}
            >
                <Card
                    style={{
                        width: '100%',
                        maxWidth: 420,
                        boxShadow: '0 20px 60px rgba(0, 0, 0, 0.3)',
                        borderRadius: 16,
                        border: 'none',
                    }}
                    bodyStyle={{
                        padding: '40px 32px',
                    }}
                >
                    <div style={{ textAlign: 'center', marginBottom: 20 }}>
                        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', marginBottom: 10 }}>
                            <img src="/images/logoctuet.png" alt="Logo" style={{ width: 100, height: 100 }} />
                        </div>
                        <Text type="secondary" style={{ fontSize: 18, fontWeight: 600, color: 'black' }} >
                            Hệ thống Quản lý Cơ sở Vật chất
                        </Text>
                    </div>

                    {errors.email && (
                        <Alert
                            message={errors.email}
                            type="error"
                            showIcon
                            style={{ marginBottom: 24, borderRadius: 8 }}
                        />
                    )}

                    <Form
                        layout="vertical"
                        onFinish={handleSubmit}
                        autoComplete="off"
                        size="large"
                    >
                        <Form.Item
                            label={<span style={{ fontWeight: 500 }}>Email</span>}
                            validateStatus={errors.email ? 'error' : ''}
                            style={{ marginBottom: 20 }}
                        >
                            <Input
                                prefix={<UserOutlined style={{ color: '#244380' }} />}
                                placeholder="Nhập email của bạn"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 10,
                                    fontSize: 15,
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            label={<span style={{ fontWeight: 500 }}>Mật khẩu</span>}
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                            style={{ marginBottom: 20 }}
                        >
                            <Input.Password
                                prefix={<LockOutlined style={{ color: '#244380' }} />}
                                placeholder="Nhập mật khẩu"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 10,
                                    fontSize: 15,
                                }}
                            />
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 24 }}>
                            <Checkbox
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
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
                                    borderRadius: 10,
                                    fontSize: 16,
                                    fontWeight: 600,
                                    background: 'linear-gradient(135deg, #244380 0%, #3d6cb8 100%)',
                                    border: 'none',
                                    boxShadow: '0 4px 16px rgba(36, 67, 128, 0.35)',
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
                            borderTop: '1px solid #f0f0f0',
                        }}
                    >
                        <Text type="secondary" style={{ fontSize: 13 }}>
                            © Trường Đại Học Kỹ Thuật Công Nghệ Cần Thơ
                        </Text>
                    </div>
                </Card>
            </div>
        </>
    );
};

export default Login;
