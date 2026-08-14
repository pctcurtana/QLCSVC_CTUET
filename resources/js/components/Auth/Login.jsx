import React, { useState, useEffect } from 'react';
import { useForm, usePage, Head } from '@inertiajs/react';
import { LoginBtn } from '../Common/LoginBtn';
import {
    Form,
    Input,
    Button,
    Checkbox,
    Typography,
    Alert,
    message
} from 'antd';
import {
    UserOutlined,
    LockOutlined,
    LoginOutlined,
    QrcodeOutlined
} from '@ant-design/icons';

const { Text } = Typography;

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
            <Head title="Đăng nhập - QLCSVC CTUET" />

            {/* Note: This container is transparent to allow default page glow orbs to float behind */}
            <div className="min-h-screen md:h-screen flex flex-col md:flex-row w-full overflow-hidden text-[#0f172a] select-none relative">

                {/* Background decorative grid from app.css */}
                <div className="absolute inset-0 login-grid-bg opacity-90 z-0 pointer-events-none" />

                {/* LEFT SIDE: Minimalist Tech Showcase */}
                <div className="hidden md:flex md:w-[45%] lg:w-[50%] flex-col justify-between p-16 relative z-10">

                    {/* Top Branding Header */}
                    <div className="flex items-center gap-3">
                        <img
                            src="/images/logoctuet.png"
                            alt="Logo CTUET"
                            className="w-16 h-16 object-contain"
                            onError={(e) => { e.target.src = '/favicon.png'; }}
                        />
                        <div className="h-5 w-[1px] bg-[#244380]/15" />
                        <span className="text-xs font-bold text-[#244380]/70 uppercase tracking-widest block font-mono">
                            Can Tho University of Technology
                        </span>
                    </div>

                    {/* Central minimalist typography & showcase */}
                    <div className="my-auto max-w-[420px]">
                        <h1 className="text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.15] text-[#0f172a] m-0">
                            Hệ thống<br />
                            <span className="bg-gradient-to-r from-[#244380] via-[#3b82f6] to-[#00c9a7] bg-clip-text text-transparent">
                                Quản lý thiết bị
                            </span><br />
                            thế hệ mới.
                        </h1>
                        <p className="text-xs text-slate-500 mt-5 leading-relaxed font-light m-0">
                            Số hóa tài sản, tối ưu hóa công tác kiểm kê và bảo trì định kỳ thông qua quy trình QR Code thông minh.
                        </p>

                        {/* Ultra-minimal scanner mockup */}
                        <div className="mt-10 relative w-40 h-40 border border-[#244380]/10 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center overflow-hidden group shadow-[0_8px_30px_rgba(36,67,128,0.02)]">
                            {/* Scanning borders */}
                            <div className="absolute top-4 left-4 w-4 h-4 border-t-2 border-l-2 border-[#244380]/40 rounded-tl" />
                            <div className="absolute top-4 right-4 w-4 h-4 border-t-2 border-r-2 border-[#244380]/40 rounded-tr" />
                            <div className="absolute bottom-4 left-4 w-4 h-4 border-b-2 border-l-2 border-[#244380]/40 rounded-bl" />
                            <div className="absolute bottom-4 right-4 w-4 h-4 border-b-2 border-r-2 border-[#244380]/40 rounded-br" />

                            {/* Abstract QR Icon */}
                            <QrcodeOutlined className="text-[#244380]/10 text-6xl transition-all duration-700 group-hover:text-[#244380]/20 group-hover:scale-105" />

                            {/* Laser bar class animated in app.css */}
                            <div className="absolute left-0 right-0 h-[1.5px] bg-gradient-to-r from-transparent via-[#244380] to-transparent shadow-[0_0_6px_rgba(36,67,128,0.2)] animate-laser" />
                        </div>
                    </div>

                    {/* Bottom minimal tag */}
                    <div className="text-[9px] text-[#244380]/50 tracking-wider uppercase font-mono">
                        quanlycsvc.ctuet.edu.vn
                    </div>
                </div>

                {/* RIGHT SIDE: Harmonious Frosted Glass Form */}
                <div className="w-full md:w-[55%] lg:w-[50%] flex flex-col justify-between p-8 sm:p-16 relative z-10 border-l border-slate-200/40 bg-white/5">


                    {/* Top Branding (Mobile only) */}
                    <div className="flex md:hidden items-center justify-center gap-3 mt-4">
                        <img
                            src="/images/logoctuet.png"
                            alt="Logo CTUET"
                            className="w-12 h-12 object-contain"
                            onError={(e) => { e.target.src = '/favicon.png'; }}
                        />
                        <span className="text-xs font-bold tracking-widest text-[#244380] font-sans uppercase">
                            Can Tho University of Technology
                        </span>
                    </div>

                    {/* Desktop placeholder spacer */}
                    <div className="hidden md:block h-6" />

                    {/* Main Login Block */}
                    <div className="my-auto py-8 w-full max-w-[380px] mx-auto">
                        {/* Mirror Glass Card styling */}
                        <div
                            style={{
                                position: 'relative',
                                overflow: 'hidden',
                                background: 'rgba(255,255,255,0.06)',
                                backdropFilter: 'blur(24px)',
                                WebkitBackdropFilter: 'blur(24px)',
                                border: '1px solid rgba(255,255,255,0.18)',
                                borderRadius: 28,
                                boxShadow: `
                                    0 8px 32px rgba(15, 23, 42, 0.05),
                                    inset 0 1px 0 rgba(255, 255, 255, 0.25)
                                `,
                                padding: '36px 32px',
                            }}
                        >
                            <div className="mb-6 text-center md:text-left">
                                <h2 className="text-xl font-black text-[#244380] tracking-tight uppercase mb-1.5">
                                    Đăng nhập
                                </h2>
                                <p className="text-xs text-slate-500 m-0">
                                    Nhập thông tin để truy cập hệ thống quản lý
                                </p>
                            </div>

                            {/* Login Alerts */}
                            {errors.email && (
                                <Alert
                                    message={errors.email}
                                    type="error"
                                    showIcon
                                    style={{
                                        marginBottom: 20,
                                        borderRadius: 8,
                                        background: 'rgba(239, 68, 68, 0.06)',
                                        border: '1px solid rgba(239, 68, 68, 0.12)',
                                    }}
                                />
                            )}

                            <Form
                                layout="vertical"
                                onFinish={handleSubmit}
                                autoComplete="off"
                                size="large"
                                requiredMark={false}
                            >
                                <Form.Item
                                    label={<span className="font-bold text-slate-500 text-[10px] uppercase tracking-wider">Tên đăng nhập / Email</span>}
                                    validateStatus={errors.email ? 'error' : ''}
                                    style={{ marginBottom: 16 }}
                                >
                                    <Input
                                        prefix={<UserOutlined style={{ color: '#244380', marginRight: 6 }} />}
                                        placeholder="user@ctuet.edu.vn"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        style={{ height: 44, fontSize: 16 }}
                                    />
                                </Form.Item>

                                <Form.Item
                                    label={<span className="font-bold text-slate-500 text-[10px] uppercase tracking-wider">Mật khẩu</span>}
                                    validateStatus={errors.password ? 'error' : ''}
                                    help={errors.password}
                                    style={{ marginBottom: 16 }}
                                >
                                    <Input.Password
                                        prefix={<LockOutlined style={{ color: '#244380', marginRight: 6 }} />}
                                        placeholder="••••••••"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        style={{ height: 44, fontSize: 16 }}
                                    />
                                </Form.Item>

                                <Form.Item style={{ marginBottom: 20 }}>
                                    <Checkbox
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        style={{ color: '#475569' }}
                                        className="text-slate-600 text-xs font-medium"
                                    >
                                        Ghi nhớ đăng nhập
                                    </Checkbox>
                                </Form.Item>

                                <Form.Item style={{ marginBottom: 0 }}>
                                    <LoginBtn
                                        htmlType="submit"
                                        loading={processing || loading}

                                    >
                                        Đăng nhập
                                    </LoginBtn>
                                </Form.Item>
                            </Form>
                        </div>
                    </div>

                    {/* Bottom copyright info */}
                    <div className="text-center text-[10px] text-slate-400 font-sans mt-4">
                        © {new Date().getFullYear()} Trường Đại Học Kỹ Thuật - Công Nghệ Cần Thơ. All rights reserved.
                    </div>
                </div>
            </div>
        </>
    );
};

export default Login;
