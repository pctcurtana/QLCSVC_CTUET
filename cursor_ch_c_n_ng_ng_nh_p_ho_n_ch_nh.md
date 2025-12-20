# Chức năng đăng nhập hoàn chỉnh
_Exported on 12/20/2025 at 20:08:49 GMT+7 from Cursor (2.2.43)_

---

**User**

trước tiên tôi có 1 dự án laravel 8.6 chạy trên php 7.4.9 ( do điều kiện bắt buộc tôi phải sử dụng) nên kèm theo đó tôi có cấu hình 1 số thứ trong dự án của tôi đương nhiên là phiên bản cũ để tương thích với phiên bản dự án như  laravel mix 6. tailwindcss 3. nodejs 18 và tôi có cài react để sử dụng trong dự án này thông qua inertiajs. tôi đã cài sẵn ant design UI cũng như rechartjs. để cẩn thận thì bạn cứ đọc và phân tích thật kỹ dự án của tôi để giúp tôi code không bị lỗi do xung đột phiên bản. bạn hãy giúp tôi làm chức năng đăng nhập hoàn chỉnh nhé nhé có admin và user. theo ý tôi muốn thì chỉ khi đăng nhập thành công thì mới vào được giao diện quản lý. dự định tôi sau khi xong chức năng đăng nhập là làm tiếp chức năng phân quyền cho user theo màn hình và dùng đệ quy á tôi nói trước cho bạn biết hướng thôi chứ trước hết làm chức năng đăng nhập hoàn chỉnh cho tôi cái đã

---

**Cursor**

Tôi hiểu yêu cầu của bạn. Trước tiên, tôi cần phân tích kỹ dự án của bạn để hiểu cấu trúc và tránh xung đột phiên bản. Hãy để tôi đọc các file quan trọng.

Tôi đã phân tích kỹ dự án của bạn. Dự án sử dụng:
- **Laravel 8.6** với **PHP 7.4+**
- **Inertia.js + React 18** với **Ant Design 5**
- **Laravel Mix 6** + **TailwindCSS 3**
- **Node.js 18**

Bây giờ tôi sẽ tạo chức năng đăng nhập hoàn chỉnh cho bạn:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
            // role: 'admin' hoặc 'user'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}

```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     *
     * @return \Inertia\Response
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Xử lý đăng nhập
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }
}

```

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ];
    }
}

```

```javascriptreact
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
    message,
    Spin 
} from 'antd';
import { 
    UserOutlined, 
    LockOutlined, 
    LoginOutlined 
} from '@ant-design/icons';

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
                    <div style={{ textAlign: 'center', marginBottom: 32 }}>
                        <div
                            style={{
                                width: 72,
                                height: 72,
                                borderRadius: '50%',
                                background: 'linear-gradient(135deg, #244380 0%, #3d6cb8 100%)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                margin: '0 auto 20px',
                                boxShadow: '0 8px 24px rgba(36, 67, 128, 0.35)',
                            }}
                        >
                            <LoginOutlined style={{ fontSize: 32, color: '#fff' }} />
                        </div>
                        <Title level={2} style={{ margin: 0, color: '#1a365d', fontWeight: 700 }}>
                            QLCSVC CTUT
                        </Title>
                        <Text type="secondary" style={{ fontSize: 15 }}>
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
                            marginTop: 32,
                            paddingTop: 24,
                            borderTop: '1px solid #f0f0f0',
                        }}
                    >
                        <Text type="secondary" style={{ fontSize: 13 }}>
                            © 2024 Trường Cao đẳng Kỹ thuật Cần Thơ
                        </Text>
                    </div>
                </Card>
            </div>
        </>
    );
};

export default Login;

```

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes - Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout - Auth only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý Cơ sở
    Route::resource('co-so', CoSoController::class);

    // Quản lý Khu nhà
    Route::resource('khu-nha', KhuNhaController::class);

    // Quản lý Phòng
    Route::resource('phong', PhongController::class);

    // Quản lý Thiết bị
    Route::get('thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
    Route::get('thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    Route::resource('thiet-bi', ThietBiController::class);

    // Quản lý Lịch sử Bảo dưỡng
    Route::resource('lich-su-bao-duong', LichSuBaoDuongController::class);
    Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
});

```

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }
}

```

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}

```

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->role === 'user';
    }
}

```

```javascriptreact
import React, { useState, useEffect } from 'react';
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
} from '@ant-design/icons';

const { Header, Sider, Content } = Layout;
const { confirm } = Modal;

const MainLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);
    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();
    
    const { url, props } = usePage();
    const { auth } = props;
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
            onClick: handleLogout,
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

```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tạo tài khoản Admin
        User::updateOrCreate(
            ['email' => 'admin@ctut.edu.vn'],
            [
                'name' => 'Administrator',
                'email' => 'admin@ctut.edu.vn',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Tạo tài khoản User mẫu
        User::updateOrCreate(
            ['email' => 'user@ctut.edu.vn'],
            [
                'name' => 'Người dùng',
                'email' => 'user@ctut.edu.vn',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Đã tạo tài khoản mặc định:');
        $this->command->info('Admin: admin@ctut.edu.vn / password');
        $this->command->info('User: user@ctut.edu.vn / password');
    }
}

```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        
        // Seed dữ liệu mẫu cho QLCSVC
        $this->call([
            UserSeeder::class,
            QLCSVCSeeder::class,
        ]);
    }
}

```

Bây giờ tôi sẽ kiểm tra các lỗi lint và chạy migration:

Tuyệt vời! Không có lỗi. Bây giờ hãy chạy migration và seed:

Tuyệt vời! Chức năng đăng nhập đã hoàn chỉnh. Dưới đây là tóm tắt những gì tôi đã làm:

## ✅ Đã hoàn thành chức năng đăng nhập

### 📁 Các file đã tạo mới:

1. **`database/migrations/2025_12_13_000001_add_role_to_users_table.php`**
   - Thêm trường `role` vào bảng users (admin/user)

2. **`app/Http/Controllers/AuthController.php`**
   - Xử lý hiển thị form đăng nhập, login và logout

3. **`app/Http/Requests/LoginRequest.php`**
   - Validation cho form đăng nhập với message tiếng Việt

4. **`resources/js/components/Auth/Login.jsx`**
   - Giao diện đăng nhập đẹp mắt với Ant Design

5. **`database/seeders/UserSeeder.php`**
   - Seeder tạo tài khoản mặc định

### 📁 Các file đã cập nhật:

1. **`routes/web.php`** - Thêm routes auth, bảo vệ tất cả routes với middleware `auth`
2. **`app/Models/User.php`** - Thêm role và helper methods
3. **`app/Http/Middleware/HandleInertiaRequests.php`** - Chia sẻ thông tin user
4. **`resources/js/components/Layout/MainLayout.jsx`** - Hiển thị user info + logout
5. **`app/Providers/RouteServiceProvider.php`** - Đổi HOME thành `/`
6. **`database/seeders/DatabaseSeeder.php`** - Thêm UserSeeder

### 🔐 Tài khoản đăng nhập:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@ctut.edu.vn` | `password` |
| **User** | `user@ctut.edu.vn` | `password` |

### 🎯 Tính năng:

- ✅ Trang đăng nhập đẹp với gradient background
- ✅ Validation form với thông báo tiếng Việt  
- ✅ Remember me (Ghi nhớ đăng nhập)
- ✅ Flash messages thông báo thành công/lỗi
- ✅ Tất cả routes được bảo vệ - chỉ đăng nhập mới vào được
- ✅ Hiển thị tên user + role trong header
- ✅ Dropdown menu với thông tin user
- ✅ Nút đăng xuất với xác nhận
- ✅ Phân biệt admin/user bằng màu badge

### 🚀 Cách sử dụng:

Truy cập dự án của bạn, bạn sẽ được chuyển đến trang đăng nhập tự động. Đăng nhập với tài khoản trên để vào giao diện quản lý.

Bây giờ bạn có thể tiếp tục phát triển chức năng **phân quyền theo màn hình** khi sẵn sàng! 🎉

---

**User**

vừa rồi bạn làm tính năng đăng nhập bằng jwt hay là laravel sanctum đấy

---

**Cursor**

Tôi đã sử dụng **Laravel's built-in Session Authentication** (xác thực dựa trên session truyền thống của Laravel), **không phải JWT hay Sanctum API tokens**.

## Giải thích chi tiết:

### 🔐 Phương pháp đã sử dụng: **Session-based Authentication**

Trong `AuthController.php` tôi đã viết:

```php
// Sử dụng Auth::attempt() - session-based auth
if (Auth::attempt($credentials, $remember)) {
    $request->session()->regenerate();
    return redirect()->intended('/');
}
```

Cấu hình trong `config/auth.php` của bạn:

```php
'guards' => [
    'web' => [
        'driver' => 'session',  // ← Dùng session driver
        'provider' => 'users',
    ],
],
```

### 📊 So sánh các phương pháp:

| Phương pháp | Mô tả | Phù hợp cho |
|-------------|-------|-------------|
| **Session Auth** ✅ (đang dùng) | Lưu trạng thái đăng nhập trong session server | Web apps, Inertia.js |
| **JWT** | Token stateless, thường dùng cho API | Mobile apps, microservices |
| **Sanctum SPA** | Session + CSRF cho SPA cùng domain | SPA với API riêng |
| **Sanctum API Tokens** | Personal access tokens | Mobile apps, 3rd party APIs |

### 🎯 Lý do chọn Session Auth cho Inertia.js:

1. **Inertia.js được khuyến nghị dùng Session Auth** - vì frontend và backend cùng domain, không cần API tokens
2. **Đơn giản hơn** - không cần quản lý token, refresh token
3. **Bảo mật hơn** - session được lưu server-side, không expose token ở client
4. **Laravel 8 mặc định** - hoạt động ngay không cần cấu hình thêm
5. **Tương thích với CSRF protection** của Laravel

Dự án của bạn đã có **Laravel Sanctum** trong `composer.json`, nhưng Sanctum cũng có thể dùng cho session-based auth (SPA mode). Trong trường hợp này, tôi dùng auth session thuần của Laravel vì nó đơn giản và phù hợp nhất với Inertia.js.

**Bạn có muốn chuyển sang JWT hoặc Sanctum API tokens không?** Thông thường với Inertia.js thì session auth là lựa chọn tốt nhất rồi! 👍

```php
// Sử dụng Auth::attempt() - session-based auth
if (Auth::attempt($credentials, $remember)) {
    $request->session()->regenerate();
    return redirect()->intended('/');
}
```

```php
'guards' => [
    'web' => [
        'driver' => 'session',  // ← Dùng session driver
        'provider' => 'users',
    ],
],
```

---

**User**

tôi muốn bạn chuyển sang dùng laravel sanctum chế độ SPA á bạn biết không giúp tôi nhé

---

**Cursor**

Được rồi! Tôi sẽ giúp bạn chuyển sang **Laravel Sanctum SPA mode**. Trước tiên để tôi kiểm tra cấu hình hiện tại:

Tốt, tôi thấy Sanctum đã được cài đặt sẵn. Bây giờ tôi sẽ cấu hình để sử dụng **Sanctum SPA mode**:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

```

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:8000,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : '',
        ',qlcsvc.test,qlcsvc-react.test' // Thêm domain local của bạn nếu cần
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];

```

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $route
```

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

```

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth Routes - Sanctum SPA
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

```

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Xử lý đăng nhập qua API (Sanctum SPA)
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác.',
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Xử lý đăng xuất qua API (Sanctum SPA)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công!',
        ]);
    }
}

```

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes - Guest only (hiển thị trang đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
});

// Protected Routes - Require Authentication
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý Cơ sở
    Route::resource('co-so', CoSoController::class);

    // Quản lý Khu nhà
    Route::resource('khu-nha', KhuNhaController::class);

    // Quản lý Phòng
    Route::resource('phong', PhongController::class);

    // Quản lý Thiết bị
    Route::get('thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
    Route::get('thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    Route::resource('thiet-bi', ThietBiController::class);

    // Quản lý Lịch sử Bảo dưỡng
    Route::resource('lich-su-bao-duong', LichSuBaoDuongController::class);
    Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
});

```

```javascriptreact
import React, { useState, useEffect } from 'react';
import { usePage, Head, router } from '@inertiajs/react';
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
import { 
    UserOutlined, 
    LockOutlined, 
    LoginOutlined 
} from '@ant-design/icons';
import axios from 'axios';

const { Title, Text } = Typography;

// Cấu hình axios cho Sanctum
axios.defaults.withCredentials = true;

const Login = () => {
    const { flash } = usePage().props;
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
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

    const handleChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        setError(null); // Clear error khi user nhập
    };

    const handleSubmit = async () => {
        setLoading(true);
        setError(null);

        try {
            // Bước 1: Lấy CSRF cookie từ Sanctum
            await axios.get('/sanctum/csrf-cookie');

            // Bước 2: Gọi API login
            const response = await axios.post('/api/login', formData);

            if (response.data.success) {
                message.success(response.data.message);
                // Redirect về trang dashboard
                router.visit('/');
            }
        } catch (err) {
            if (err.response) {
                if (err.response.status === 401) {
                    setError(err.response.data.message || 'Thông tin đăng nhập không chính xác.');
                } else if (err.response.status === 422) {
                    // Validation errors
                    const errors = err.response.data.errors;
                    const firstError = Object.values(errors)[0];
                    setError(Array.isArray(firstError) ? firstError[0] : firstError);
                } else {
                    setError('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            } else {
                setError('Không thể kết nối đến server.');
            }
        } finally {
            setLoading(false);
        }
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
                    <div style={{ textAlign: 'center', marginBottom: 32 }}>
                        <div
                            style={{
                                width: 72,
                                height: 72,
                                borderRadius: '50%',
                                background: 'linear-gradient(135deg, #244380 0%, #3d6cb8 100%)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                margin: '0 auto 20px',
                                boxShadow: '0 8px 24px rgba(36, 67, 128, 0.35)',
                            }}
                        >
                            <LoginOutlined style={{ fontSize: 32, color: '#fff' }} />
                        </div>
                        <Title level={2} style={{ margin: 0, color: '#1a365d', fontWeight: 700 }}>
                            QLCSVC CTUT
                        </Title>
                        <Text type="secondary" style={{ fontSize: 15 }}>
                            Hệ thống Quản lý Cơ sở Vật chất
                        </Text>
                    </div>

                    {error && (
                        <Alert
                            message={error}
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
                            style={{ marginBottom: 20 }}
                        >
                            <Input
                                prefix={<UserOutlined style={{ color: '#244380' }} />}
                                placeholder="Nhập email của bạn"
                                value={formData.email}
                                onChange={(e) => handleChange('email', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 10,
                                    fontSize: 15,
                                }}
                            />
                        </Form.Item>

                        <Form.Item
                            label={<span style={{ fontWeight: 500 }}>Mật khẩu</span>}
                            style={{ marginBottom: 20 }}
                        >
                            <Input.Password
                                prefix={<LockOutlined style={{ color: '#244380' }} />}
                                placeholder="Nhập mật khẩu"
                                value={formData.password}
                                onChange={(e) => handleChange('password', e.target.value)}
                                style={{ 
                                    height: 48, 
                                    borderRadius: 10,
                                    fontSize: 15,
                                }}
                            />
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 24 }}>
                            <Checkbox
                                checked={formData.remember}
                                onChange={(e) => handleChange('remember', e.target.checked)}
                            >
                                Ghi nhớ đăng nhập
                            </Checkbox>
                        </Form.Item>

                        <Form.Item style={{ marginBottom: 0 }}>
                            <Button
                                type="primary"
                                htmlType="submit"
                                loading={loading}
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
                            marginTop: 32,
                            paddingTop: 24,
                            borderTop: '1px solid #f0f0f0',
                        }}
                    >
                        <Text type="secondary" style={{ fontSize: 13 }}>
                            © 2024 Trường Cao đẳng Kỹ thuật Cần Thơ
                        </Text>
                    </div>
                </Card>
            </div>
        </>
    );
};

export default Login;

```

```javascriptreact
import React, { useState, useEffect } from 'react';
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
} from '@ant-design/icons';
import axios from 'axios';

const { Header, Sider, Content } = Layout;
const { confirm } = Modal;

// Cấu hình axios cho Sanctum
axios.defaults.withCredentials = true;

const MainLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);
    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();
    
    const { url, props } = usePage();
    const { auth } = props;
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
            async onOk() {
                try {
                    const response = await axios.post('/api/logout');
                    if (response.data.success) {
                        message.success(response.data.message);
                        router.visit('/login');
                    }
                } catch (error) {
                    message.error('Có lỗi xảy ra khi đăng xuất.');
                    // Vẫn redirect về login
                    router.visit('/login');
                }
            },
        });
    };

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
            onClick: handleLogout,
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

```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     *
     * @return \Inertia\Response
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }
}

```

Bây giờ kiểm tra lỗi và build lại assets:

Hoàn thành! Tôi đã chuyển sang **Laravel Sanctum SPA mode**. Dưới đây là tóm tắt:

## ✅ Đã chuyển đổi sang Sanctum SPA Mode

### 📁 Các file đã cập nhật/tạo mới:

| File | Thay đổi |
|------|----------|
| `config/cors.php` | Thêm `supports_credentials: true`, thêm paths |
| `config/sanctum.php` | Cấu hình stateful domains, guards |
| `app/Http/Kernel.php` | Bật `EnsureFrontendRequestsAreStateful` middleware |
| `routes/api.php` | Thêm API routes: `/api/login`, `/api/logout` |
| `app/Http/Controllers/Api/AuthController.php` | **MỚI** - Controller xử lý auth API |
| `app/Http/Controllers/AuthController.php` | Giữ lại chỉ `showLogin()` |
| `routes/web.php` | Sử dụng `auth:sanctum` middleware |
| `resources/js/components/Auth/Login.jsx` | Gọi CSRF cookie + API login |
| `resources/js/components/Layout/MainLayout.jsx` | Gọi API logout |

### 🔄 Flow đăng nhập Sanctum SPA:

```
1. User truy cập /login
   ↓
2. Nhập email/password, click "Đăng nhập"
   ↓
3. Frontend gọi GET /sanctum/csrf-cookie (lấy CSRF token)
   ↓
4. Frontend gọi POST /api/login với credentials
   ↓
5. Server xác thực, tạo session, trả về JSON response
   ↓
6. Frontend redirect về / (Dashboard)
```

### 🔐 Đặc điểm Sanctum SPA Mode:

- ✅ **Session-based** nhưng qua API endpoints
- ✅ **CSRF Protection** - phải gọi `/sanctum/csrf-cookie` trước khi login
- ✅ **Stateful** - session được lưu server-side
- ✅ **SPA friendly** - response trả về JSON
- ✅ **Secure** - cookie HttpOnly, same-site
- ✅ **Middleware** `EnsureFrontendRequestsAreStateful` xử lý session cho API

### 📋 API Endpoints:

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/sanctum/csrf-cookie` | Lấy CSRF token |
| POST | `/api/login` | Đăng nhập |
| POST | `/api/logout` | Đăng xuất |
| GET | `/api/user` | Lấy thông tin user hiện tại |

### 🔐 Tài khoản test:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@ctut.edu.vn` | `password` |
| **User** | `user@ctut.edu.vn` | `password` |

Bây giờ hệ thống đã sử dụng **Laravel Sanctum SPA mode** hoàn chỉnh! 🎉

```plaintext
1. User truy cập /login
   ↓
2. Nhập email/password, click "Đăng nhập"
   ↓
3. Frontend gọi GET /sanctum/csrf-cookie (lấy CSRF token)
   ↓
4. Frontend gọi POST /api/login với credentials
   ↓
5. Server xác thực, tạo session, trả về JSON response
   ↓
6. Frontend redirect về / (Dashboard)
```

---

**User**

POST	/api/login	Đăng nhập
POST	/api/logout tại sao lại dùng là api bạn trong khi dự án của tôi hoạt động trên 1 domain mà do giao tiếp giữa react và laravel bằng inertiajs ,mà

---

**Cursor**

Bạn hỏi rất đúng! Đây là điểm quan trọng mà tôi cần giải thích:

## 🤔 Vấn đề với cách tiếp cận `/api/*` cho Inertia.js

Bạn hoàn toàn đúng! Với **Inertia.js**, việc sử dụng `/api/login` và `/api/logout` là **KHÔNG CẦN THIẾT** và thậm chí là **phức tạp hóa vấn đề** vì:

### Inertia.js ≠ SPA thuần túy

| Đặc điểm | SPA thuần túy | Inertia.js |
|----------|---------------|------------|
| Frontend/Backend | Khác domain/port | **Cùng domain** |
| Routing | Client-side (React Router) | **Server-side (Laravel routes)** |
| Data fetching | Gọi API endpoints | **Props từ Controller** |
| CSRF | Cần lấy thủ công | **Tự động xử lý** |
| Session | Cần Sanctum SPA mode | **Web middleware đã xử lý** |

### Sanctum SPA mode dành cho:
- ✅ React/Vue chạy riêng trên `localhost:3000`
- ✅ Frontend và backend khác domain
- ✅ Mobile apps

### Với Inertia.js, bạn chỉ cần:
- ✅ **Web routes** (`routes/web.php`)
- ✅ **Session auth thông thường** (`Auth::attempt()`)
- ✅ **Middleware `auth`** (không cần `auth:sanctum`)
- ✅ CSRF được Inertia xử lý tự động

---

## ✅ Cách đúng cho Inertia.js

Cách tiếp cận **ban đầu** tôi làm cho bạn (trước khi chuyển sang Sanctum SPA) thực sự là **đúng và đơn giản hơn**:

```php
// routes/web.php - Đây là cách ĐÚNG cho Inertia.js
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
```

```jsx
// Login.jsx - Dùng Inertia form, KHÔNG cần axios
import { useForm } from '@inertiajs/react';

const { post } = useForm({ email: '', password: '' });
post('/login'); // Inertia tự xử lý CSRF, redirect, flash messages
```

---

## 🔄 Bạn muốn quay lại cách đúng không?

Nếu bạn muốn, tôi sẽ **revert về cách session auth thông thường** qua web routes - đây là cách được **Inertia.js khuyến nghị** và phù hợp với dự án của bạn.

Hãy chuyển sang **Agent mode** để tôi sửa lại cho bạn nhé!

```php
// routes/web.php - Đây là cách ĐÚNG cho Inertia.js
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
```

```javascriptreact
// Login.jsx - Dùng Inertia form, KHÔNG cần axios
import { useForm } from '@inertiajs/react';

const { post } = useForm({ email: '', password: '' });
post('/login'); // Inertia tự xử lý CSRF, redirect, flash messages
```

---

**User**

ủa vậy cách cũ bạn làm là dùng token hay cookies có csrf token chưa

---

**Cursor**

Câu hỏi rất hay! Để tôi giải thích rõ cách cũ tôi làm:

## 🔐 Cách cũ (Session Auth cho Inertia.js)

### ✅ Có sử dụng CSRF Token - **TỰ ĐỘNG**

```
┌─────────────────────────────────────────────────────────────┐
│                    CÁCH CŨ ĐÃ LÀM                          │
├─────────────────────────────────────────────────────────────┤
│  • Authentication: Session-based (cookies)                  │
│  • Token type: KHÔNG dùng JWT hay API token                │
│  • CSRF: ✅ Có - tự động bởi Laravel + Inertia             │
│  • Storage: Session ID lưu trong cookie                     │
└─────────────────────────────────────────────────────────────┘
```

### Flow hoạt động:

```
1. User truy cập /login
   ↓
2. Laravel render trang qua Inertia, kèm CSRF token trong cookie "XSRF-TOKEN"
   ↓
3. User submit form (Inertia's useForm)
   ↓
4. Inertia TỰ ĐỘNG gửi CSRF token trong header "X-XSRF-TOKEN"
   ↓
5. Laravel middleware VerifyCsrfToken kiểm tra → OK
   ↓
6. Auth::attempt() xác thực → Tạo session
   ↓
7. Session ID lưu vào cookie "laravel_session"
   ↓
8. Các request tiếp theo: Cookie session tự động gửi kèm
```

### Middleware đã xử lý CSRF:

```php
// app/Http/Kernel.php - web middleware group
'web' => [
    \App\Http\Middleware\EncryptCookies::class,
    // ...
    \App\Http\Middleware\VerifyCsrfToken::class,  // ← CSRF protection
    // ...
],
```

### Inertia tự động xử lý CSRF:

```jsx
// Cách cũ - dùng Inertia's useForm
import { useForm } from '@inertiajs/react';

const { data, setData, post } = useForm({
    email: '',
    password: '',
});

// Khi gọi post(), Inertia TỰ ĐỘNG:
// 1. Đọc CSRF token từ cookie XSRF-TOKEN
// 2. Gửi kèm header X-XSRF-TOKEN
post('/login');
```

---

## 📊 So sánh 2 cách:

| Tiêu chí | Cách cũ (Session Auth) | Cách mới (Sanctum SPA) |
|----------|------------------------|------------------------|
| **CSRF** | ✅ Tự động (Inertia) | ✅ Phải gọi `/sanctum/csrf-cookie` thủ công |
| **Token** | Session cookie | Session cookie |
| **Routes** | Web routes | API routes |
| **Phù hợp** | ✅ **Inertia.js** | SPA thuần túy |
| **Độ phức tạp** | Đơn giản | Phức tạp hơn |

---

## 🎯 Kết luận

**Cách cũ đã có:**
- ✅ **CSRF protection** - tự động bởi Laravel middleware + Inertia
- ✅ **Session-based auth** - an toàn, server-side
- ✅ **Cookies** - session ID + CSRF token

**Cách mới (Sanctum SPA)** thực sự **không cần thiết** cho Inertia.js vì:
- Phải gọi CSRF cookie thủ công
- Dùng API routes thay vì web routes
- Phức tạp hóa không cần thiết

**Bạn có muốn tôi revert về cách cũ không?** Nó đơn giản và phù hợp hơn cho Inertia.js! Chuyển sang Agent mode để tôi sửa nhé.

```plaintext
┌─────────────────────────────────────────────────────────────┐
│                    CÁCH CŨ ĐÃ LÀM                          │
├─────────────────────────────────────────────────────────────┤
│  • Authentication: Session-based (cookies)                  │
│  • Token type: KHÔNG dùng JWT hay API token                │
│  • CSRF: ✅ Có - tự động bởi Laravel + Inertia             │
│  • Storage: Session ID lưu trong cookie                     │
└─────────────────────────────────────────────────────────────┘
```

```plaintext
1. User truy cập /login
   ↓
2. Laravel render trang qua Inertia, kèm CSRF token trong cookie "XSRF-TOKEN"
   ↓
3. User submit form (Inertia's useForm)
   ↓
4. Inertia TỰ ĐỘNG gửi CSRF token trong header "X-XSRF-TOKEN"
   ↓
5. Laravel middleware VerifyCsrfToken kiểm tra → OK
   ↓
6. Auth::attempt() xác thực → Tạo session
   ↓
7. Session ID lưu vào cookie "laravel_session"
   ↓
8. Các request tiếp theo: Cookie session tự động gửi kèm
```

```php
// app/Http/Kernel.php - web middleware group
'web' => [
    \App\Http\Middleware\EncryptCookies::class,
    // ...
    \App\Http\Middleware\VerifyCsrfToken::class,  // ← CSRF protection
    // ...
],
```

```javascriptreact
// Cách cũ - dùng Inertia's useForm
import { useForm } from '@inertiajs/react';

const { data, setData, post } = useForm({
    email: '',
    password: '',
});

// Khi gọi post(), Inertia TỰ ĐỘNG:
// 1. Đọc CSRF token từ cookie XSRF-TOKEN
// 2. Gửi kèm header X-XSRF-TOKEN
post('/login');
```

---

**User**

ok vậy phiền bạn chuyển về cách cũ giúp tôi nhé

---

**Cursor**

Được rồi! Tôi sẽ revert về cách Session Auth thông thường cho Inertia.js:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     *
     * @return \Inertia\Response
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Xử lý đăng nhập
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }
}

```

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes - Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout - Auth only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý Cơ sở
    Route::resource('co-so', CoSoController::class);

    // Quản lý Khu nhà
    Route::resource('khu-nha', KhuNhaController::class);

    // Quản lý Phòng
    Route::resource('phong', PhongController::class);

    // Quản lý Thiết bị
    Route::get('thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
    Route::get('thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    Route::resource('thiet-bi', ThietBiController::class);

    // Quản lý Lịch sử Bảo dưỡng
    Route::resource('lich-su-bao-duong', LichSuBaoDuongController::class);
    Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
});

```

```javascriptreact
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
import { 
    UserOutlined, 
    LockOutlined, 
    LoginOutlined 
} from '@ant-design/icons';

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
                    <div style={{ textAlign: 'center', marginBottom: 32 }}>
                        <div
                            style={{
                                width: 72,
                                height: 72,
                                borderRadius: '50%',
                                background: 'linear-gradient(135deg, #244380 0%, #3d6cb8 100%)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                margin: '0 auto 20px',
                                boxShadow: '0 8px 24px rgba(36, 67, 128, 0.35)',
                            }}
                        >
                            <LoginOutlined style={{ fontSize: 32, color: '#fff' }} />
                        </div>
                        <Title level={2} style={{ margin: 0, color: '#1a365d', fontWeight: 700 }}>
                            QLCSVC CTUT
                        </Title>
                        <Text type="secondary" style={{ fontSize: 15 }}>
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
                            marginTop: 32,
                            paddingTop: 24,
                            borderTop: '1px solid #f0f0f0',
                        }}
                    >
                        <Text type="secondary" style={{ fontSize: 13 }}>
                            © 2024 Trường Cao đẳng Kỹ thuật Cần Thơ
                        </Text>
                    </div>
                </Card>
            </div>
        </>
    );
};

export default Login;

```

```javascriptreact
import React, { useState, useEffect } from 'react';
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
} from '@ant-design/icons';

const { Header, Sider, Content } = Layout;
const { confirm } = Modal;

const MainLayout = ({ children }) => {
    const [collapsed, setCollapsed] = useState(false);
    const {
        token: { colorBgContainer, borderRadiusLG },
    } = theme.useToken();
    
    const { url, props } = usePage();
    const { auth } = props;
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
            onClick: handleLogout,
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

```

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

```

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

```

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

```

Bây giờ build lại assets và clear cache:

Hoàn thành! Đã revert về **Session Auth thông thường** cho Inertia.js. 

## ✅ Đã chuyển về cách đúng cho Inertia.js

### 📁 Các thay đổi:

| File | Thay đổi |
|------|----------|
| `app/Http/Controllers/AuthController.php` | Thêm lại `login()` và `logout()` methods |
| `routes/web.php` | Web routes: `POST /login`, `POST /logout` |
| `resources/js/components/Auth/Login.jsx` | Dùng Inertia `useForm` (CSRF tự động) |
| `resources/js/components/Layout/MainLayout.jsx` | Dùng `router.post('/logout')` |
| `routes/api.php` | Xóa auth routes, giữ nguyên mặc định |
| `app/Http/Controllers/Api/AuthController.php` | **ĐÃ XÓA** |
| `config/cors.php` | Revert về mặc định |
| `app/Http/Kernel.php` | Comment lại Sanctum SPA middleware |

### 🔄 Flow đăng nhập hiện tại:

```
1. User truy cập /login → Laravel render Login.jsx qua Inertia
   ↓
2. User submit form → Inertia useForm.post('/login')
   ↓
3. Inertia TỰ ĐỘNG gửi CSRF token (không cần code gì thêm)
   ↓
4. AuthController::login() → Auth::attempt()
   ↓
5. Thành công → Session được tạo, redirect về /
   ↓
6. Flash message "Đăng nhập thành công!"
```

### ✅ Ưu điểm cách này:

- **Đơn giản** - Không cần cấu hình thêm
- **CSRF tự động** - Inertia xử lý hết
- **Flash messages** - Hoạt động tự nhiên với Inertia
- **Redirects** - `redirect()->intended()` hoạt động đúng
- **Validation errors** - Tự động gửi về frontend qua `$page.props.errors`

### 🔐 Tài khoản test:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@ctut.edu.vn` | `password` |
| **User** | `user@ctut.edu.vn` | `password` |

Bây giờ hệ thống đã sử dụng **Session Auth chuẩn cho Inertia.js** - đơn giản và phù hợp nhất! 🎉

```plaintext
1. User truy cập /login → Laravel render Login.jsx qua Inertia
   ↓
2. User submit form → Inertia useForm.post('/login')
   ↓
3. Inertia TỰ ĐỘNG gửi CSRF token (không cần code gì thêm)
   ↓
4. AuthController::login() → Auth::attempt()
   ↓
5. Thành công → Session được tạo, redirect về /
   ↓
6. Flash message "Đăng nhập thành công!"
```

---

**User**

bạn giải thích cho tôi hiểu luông hoạt động chức năng đăng nhập bạn làm đi từ lúc gửi request là tài khoản mật khẩu và sinh ra cookie như nào lưu ở đâu lấy ra xài như thế nào

---

**Cursor**

Tôi sẽ giải thích chi tiết luồng hoạt động của chức năng đăng nhập cho bạn:

## 🔄 Luồng Đăng nhập Chi Tiết

### **BƯỚC 1: User nhập thông tin và submit form**

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (React)                         │
├─────────────────────────────────────────────────────────────────┤
│  Login.jsx                                                      │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  const { data, post } = useForm({                        │   │
│  │      email: 'admin@ctut.edu.vn',                        │   │
│  │      password: 'password',                               │   │
│  │      remember: true                                      │   │
│  │  });                                                     │   │
│  │                                                          │   │
│  │  post('/login');  // Gửi POST request                   │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
              ┌───────────────────────────────┐
              │     HTTP POST Request         │
              │  URL: /login                  │
              │  Body: {                      │
              │    email: "admin@ctut..."     │
              │    password: "password"       │
              │    remember: true             │
              │  }                            │
              │  Headers: {                   │
              │    X-XSRF-TOKEN: "abc123..."  │  ← CSRF Token (Inertia tự thêm)
              │    X-Inertia: true            │
              │  }                            │
              └───────────────────────────────┘
```

### **BƯỚC 2: Laravel nhận request và xử lý qua Middleware**

```
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL MIDDLEWARE PIPELINE                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. EncryptCookies        → Giải mã cookies từ request         │
│  2. StartSession          → Khởi tạo/load session từ storage   │
│  3. VerifyCsrfToken       → Kiểm tra CSRF token hợp lệ        │
│  4. HandleInertiaRequests → Xử lý Inertia headers              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
```

### **BƯỚC 3: AuthController xử lý đăng nhập**

```php
// app/Http/Controllers/AuthController.php

public function login(LoginRequest $request)
{
    // 1. Lấy credentials từ request
    $credentials = [
        'email' => 'admin@ctut.edu.vn',
        'password' => 'password'
    ];
    
    // 2. Auth::attempt() thực hiện:
    //    - Query database: SELECT * FROM users WHERE email = ?
    //    - So sánh password với Hash::check($password, $user->password)
    if (Auth::attempt($credentials, $remember = true)) {
        
        // 3. Nếu đúng, Laravel tạo session:
        //    - Sinh session_id ngẫu nhiên (ví dụ: "abc123xyz...")
        //    - Lưu user_id vào session data
        $request->session()->regenerate();
        
        // 4. Redirect về trang chủ
        return redirect()->intended('/');
    }
}
```

### **BƯỚC 4: Session được tạo và lưu trữ**

```
┌─────────────────────────────────────────────────────────────────┐
│                     SESSION STORAGE                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Vị trí lưu: storage/framework/sessions/                       │
│                                                                 │
│  File: abc123xyz789...  (tên file = session_id)                │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  {                                                       │   │
│  │    "_token": "csrf_token_value",                        │   │
│  │    "_previous": {"url": "/login"},                      │   │
│  │    "_flash": {"new": [], "old": []},                    │   │
│  │    "login_web_abc123": 1,        ← USER ID = 1          │   │
│  │    "PHPSESSID": "abc123xyz789"                          │   │
│  │  }                                                       │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  Cấu hình trong config/session.php:                            │
│  - driver: 'file' (mặc định)                                   │
│  - lifetime: 120 (phút)                                        │
│  - path: storage/framework/sessions                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### **BƯỚC 5: Cookie được gửi về Browser**

```
┌─────────────────────────────────────────────────────────────────┐
│                   HTTP Response Headers                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Set-Cookie: laravel_session=eyJpdiI6Ik...encrypted...;        │
│              path=/;                                            │
│              httponly;          ← JavaScript không đọc được    │
│              samesite=lax;      ← Bảo vệ CSRF                  │
│              expires=Sat, 13-Dec-2025 12:00:00 GMT             │
│                                                                 │
│  Set-Cookie: XSRF-TOKEN=eyJpdiI6Ik...new_csrf_token...;        │
│              path=/;                                            │
│              samesite=lax                                       │
│                                                                 │
│  (Nếu remember=true)                                           │
│  Set-Cookie: remember_web_abc123=eyJpdiI6Ik...;                │
│              path=/;                                            │
│              httponly;                                          │
│              expires=Sat, 13-Jan-2026 12:00:00 GMT (5 năm)     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### **BƯỚC 6: Browser lưu Cookies**

```
┌─────────────────────────────────────────────────────────────────┐
│              BROWSER STORAGE (DevTools > Application)           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Cookies for localhost:8000                                     │
│  ┌──────────────────┬─────────────────────────────────────┐    │
│  │ Name             │ Value                               │    │
│  ├──────────────────┼─────────────────────────────────────┤    │
│  │ laravel_session  │ eyJpdiI6Ik5KbVFZ... (encrypted)    │    │
│  │ XSRF-TOKEN       │ eyJpdiI6IkNFa0... (CSRF token)     │    │
│  │ remember_web_... │ eyJpdiI6IkFB... (nếu có)           │    │
│  └──────────────────┴─────────────────────────────────────┘    │
│                                                                 │
│  Lưu ý:                                                        │
│  - Cookies được MÃ HÓA bởi EncryptCookies middleware           │
│  - Browser tự động gửi cookies trong mỗi request              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### **BƯỚC 7: Các request tiếp theo (đã đăng nhập)**

```
┌─────────────────────────────────────────────────────────────────┐
│            MỖI REQUEST SAU KHI ĐÃ ĐĂNG NHẬP                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  User truy cập: GET /co-so                                     │
│                                                                 │
│  Browser TỰ ĐỘNG gửi kèm cookies:                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Cookie: laravel_session=eyJpdiI6Ik...;                 │   │
│  │          XSRF-TOKEN=eyJpdiI6IkNF...                      │   │
│  └─────────────────────────────────────────────────────────┘   │
│                              │                                  │
│                              ▼                                  │
│  LARAVEL XỬ LÝ:                                                │
│                                                                 │
│  1. EncryptCookies → Giải mã laravel_session                   │
│     → Được session_id: "abc123xyz789"                          │
│                                                                 │
│  2. StartSession → Đọc file session từ storage                 │
│     → File: storage/framework/sessions/abc123xyz789            │
│     → Lấy được: login_web_abc123 = 1 (user_id)                │
│                                                                 │
│  3. Auth Middleware kiểm tra:                                  │
│     → Auth::check() = true                                     │
│     → Auth::user() = User với id=1                             │
│                                                                 │
│  4. Controller xử lý request bình thường                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📊 Tóm tắt: Cookie & Session hoạt động như thế nào

```
┌─────────────────────────────────────────────────────────────────┐
│                        TÓM TẮT                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  COOKIE (Browser)              SESSION (Server)                 │
│  ┌─────────────────┐          ┌─────────────────┐              │
│  │ laravel_session │ ───────► │ Session File    │              │
│  │ = session_id    │   key    │ ┌─────────────┐ │              │
│  │   (encrypted)   │          │ │ user_id: 1  │ │              │
│  └─────────────────┘          │ │ csrf_token  │ │              │
│                               │ │ flash_data  │ │              │
│                               │ └─────────────┘ │              │
│                               └─────────────────┘              │
│                                                                 │
│  Cookie = CHÌA KHÓA (lưu ở browser)                            │
│  Session = DỮ LIỆU (lưu ở server)                              │
│                                                                 │
│  → Cookie chỉ chứa session_id (đã mã hóa)                      │
│  → Dữ liệu nhạy cảm (user_id) lưu ở server                     │
│  → Hacker lấy được cookie cũng không thấy user_id              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔐 Bảo mật

| Cơ chế | Mục đích |
|--------|----------|
| **Cookie HttpOnly** | JavaScript không đọc được → chống XSS |
| **Cookie Encrypted** | Giá trị cookie bị mã hóa → không giả mạo được |
| **CSRF Token** | Mỗi form cần token → chống CSRF attack |
| **Session trên Server** | User ID lưu server → client không sửa được |
| **SameSite=Lax** | Cookie không gửi từ site khác → chống CSRF |

---

## 🗂️ Vị trí lưu trữ

| Loại | Vị trí |
|------|--------|
| **Session files** | `storage/framework/sessions/` |
| **Session config** | `config/session.php` |
| **Cookies** | Browser (DevTools → Application → Cookies) |

Bạn có muốn tôi giải thích thêm phần nào không? 😊

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (React)                         │
├─────────────────────────────────────────────────────────────────┤
│  Login.jsx                                                      │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  const { data, post } = useForm({                        │   │
│  │      email: 'admin@ctut.edu.vn',                        │   │
│  │      password: 'password',                               │   │
│  │      remember: true                                      │   │
│  │  });                                                     │   │
│  │                                                          │   │
│  │  post('/login');  // Gửi POST request                   │   │
│  └─────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
              ┌───────────────────────────────┐
              │     HTTP POST Request         │
              │  URL: /login                  │
              │  Body: {                      │
              │    email: "admin@ctut..."     │
              │    password: "password"       │
              │    remember: true             │
              │  }                            │
              │  Headers: {                   │
              │    X-XSRF-TOKEN: "abc123..."  │  ← CSRF Token (Inertia tự thêm)
              │    X-Inertia: true            │
              │  }                            │
              └───────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL MIDDLEWARE PIPELINE                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. EncryptCookies        → Giải mã cookies từ request         │
│  2. StartSession          → Khởi tạo/load session từ storage   │
│  3. VerifyCsrfToken       → Kiểm tra CSRF token hợp lệ        │
│  4. HandleInertiaRequests → Xử lý Inertia headers              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
```

```php
// app/Http/Controllers/AuthController.php

public function login(LoginRequest $request)
{
    // 1. Lấy credentials từ request
    $credentials = [
        'email' => 'admin@ctut.edu.vn',
        'password' => 'password'
    ];
    
    // 2. Auth::attempt() thực hiện:
    //    - Query database: SELECT * FROM users WHERE email = ?
    //    - So sánh password với Hash::check($password, $user->password)
    if (Auth::attempt($credentials, $remember = true)) {
        
        // 3. Nếu đúng, Laravel tạo session:
        //    - Sinh session_id ngẫu nhiên (ví dụ: "abc123xyz...")
        //    - Lưu user_id vào session data
        $request->session()->regenerate();
        
        // 4. Redirect về trang chủ
        return redirect()->intended('/');
    }
}
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│                     SESSION STORAGE                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Vị trí lưu: storage/framework/sessions/                       │
│                                                                 │
│  File: abc123xyz789...  (tên file = session_id)                │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  {                                                       │   │
│  │    "_token": "csrf_token_value",                        │   │
│  │    "_previous": {"url": "/login"},                      │   │
│  │    "_flash": {"new": [], "old": []},                    │   │
│  │    "login_web_abc123": 1,        ← USER ID = 1          │   │
│  │    "PHPSESSID": "abc123xyz789"                          │   │
│  │  }                                                       │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  Cấu hình trong config/session.php:                            │
│  - driver: 'file' (mặc định)                                   │
│  - lifetime: 120 (phút)                                        │
│  - path: storage/framework/sessions                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│                   HTTP Response Headers                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Set-Cookie: laravel_session=eyJpdiI6Ik...encrypted...;        │
│              path=/;                                            │
│              httponly;          ← JavaScript không đọc được    │
│              samesite=lax;      ← Bảo vệ CSRF                  │
│              expires=Sat, 13-Dec-2025 12:00:00 GMT             │
│                                                                 │
│  Set-Cookie: XSRF-TOKEN=eyJpdiI6Ik...new_csrf_token...;        │
│              path=/;                                            │
│              samesite=lax                                       │
│                                                                 │
│  (Nếu remember=true)                                           │
│  Set-Cookie: remember_web_abc123=eyJpdiI6Ik...;                │
│              path=/;                                            │
│              httponly;                                          │
│              expires=Sat, 13-Jan-2026 12:00:00 GMT (5 năm)     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│              BROWSER STORAGE (DevTools > Application)           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Cookies for localhost:8000                                     │
│  ┌──────────────────┬─────────────────────────────────────┐    │
│  │ Name             │ Value                               │    │
│  ├──────────────────┼─────────────────────────────────────┤    │
│  │ laravel_session  │ eyJpdiI6Ik5KbVFZ... (encrypted)    │    │
│  │ XSRF-TOKEN       │ eyJpdiI6IkNFa0... (CSRF token)     │    │
│  │ remember_web_... │ eyJpdiI6IkFB... (nếu có)           │    │
│  └──────────────────┴─────────────────────────────────────┘    │
│                                                                 │
│  Lưu ý:                                                        │
│  - Cookies được MÃ HÓA bởi EncryptCookies middleware           │
│  - Browser tự động gửi cookies trong mỗi request              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│            MỖI REQUEST SAU KHI ĐÃ ĐĂNG NHẬP                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  User truy cập: GET /co-so                                     │
│                                                                 │
│  Browser TỰ ĐỘNG gửi kèm cookies:                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  Cookie: laravel_session=eyJpdiI6Ik...;                 │   │
│  │          XSRF-TOKEN=eyJpdiI6IkNF...                      │   │
│  └─────────────────────────────────────────────────────────┘   │
│                              │                                  │
│                              ▼                                  │
│  LARAVEL XỬ LÝ:                                                │
│                                                                 │
│  1. EncryptCookies → Giải mã laravel_session                   │
│     → Được session_id: "abc123xyz789"                          │
│                                                                 │
│  2. StartSession → Đọc file session từ storage                 │
│     → File: storage/framework/sessions/abc123xyz789            │
│     → Lấy được: login_web_abc123 = 1 (user_id)                │
│                                                                 │
│  3. Auth Middleware kiểm tra:                                  │
│     → Auth::check() = true                                     │
│     → Auth::user() = User với id=1                             │
│                                                                 │
│  4. Controller xử lý request bình thường                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────┐
│                        TÓM TẮT                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  COOKIE (Browser)              SESSION (Server)                 │
│  ┌─────────────────┐          ┌─────────────────┐              │
│  │ laravel_session │ ───────► │ Session File    │              │
│  │ = session_id    │   key    │ ┌─────────────┐ │              │
│  │   (encrypted)   │          │ │ user_id: 1  │ │              │
│  └─────────────────┘          │ │ csrf_token  │ │              │
│                               │ │ flash_data  │ │              │
│                               │ └─────────────┘ │              │
│                               └─────────────────┘              │
│                                                                 │
│  Cookie = CHÌA KHÓA (lưu ở browser)                            │
│  Session = DỮ LIỆU (lưu ở server)                              │
│                                                                 │
│  → Cookie chỉ chứa session_id (đã mã hóa)                      │
│  → Dữ liệu nhạy cảm (user_id) lưu ở server                     │
│  → Hacker lấy được cookie cũng không thấy user_id              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

**User**

tôi muốn biết cái chức năng remember me bạn làm có tác dụng không sao tôi k thấy thông tin khi tích vào thì thời gian hết hạn session là bao lâu vậy chỉ thấy được SESSION_LIFETIME=120 đây là thời gian hết hạn của 1 session bình thường thôi đúng không

---

**Cursor**

Câu hỏi rất hay! Để tôi kiểm tra xem chức năng "Remember Me" đã được implement đúng chưa:

Bạn đúng rồi! Để tôi giải thích rõ:

## ✅ Chức năng Remember Me đã hoạt động

Code đã đúng:
```php
$remember = $request->boolean('remember');
Auth::attempt($credentials, $remember);  // ← Truyền $remember vào
```

## 📊 Sự khác biệt: Session vs Remember Me

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SESSION vs REMEMBER ME                                   │
├───────────────────────────────┬─────────────────────────────────────────────┤
│         SESSION               │           REMEMBER ME                       │
├───────────────────────────────┼─────────────────────────────────────────────┤
│ Cookie: laravel_session       │ Cookie: remember_web_xxxxx                  │
│                               │                                             │
│ Thời hạn: 120 phút (2 giờ)    │ Thời hạn: 5 NĂM (mặc định Laravel)         │
│ (SESSION_LIFETIME=120)        │ (Hardcoded trong Laravel core)              │
│                               │                                             │
│ Hết hạn khi:                  │ Hết hạn khi:                                │
│ - Không hoạt động 2 giờ       │ - Sau 5 năm                                 │
│ - Đóng browser (nếu config)   │ - User logout                               │
│                               │ - Admin xóa remember_token                  │
│                               │                                             │
│ Lưu trữ:                      │ Lưu trữ:                                    │
│ - Cookie: session_id          │ - Cookie: user_id + token                   │
│ - Server: session data        │ - Database: remember_token column           │
└───────────────────────────────┴─────────────────────────────────────────────┘
```

## 🔄 Flow khi có Remember Me

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ĐĂNG NHẬP VỚI REMEMBER ME                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. User login với remember = true                                          │
│     ↓                                                                       │
│  2. Laravel tạo 2 cookies:                                                  │
│     • laravel_session (2 giờ)                                              │
│     • remember_web_xxxxx (5 năm)                                           │
│     ↓                                                                       │
│  3. Laravel lưu remember_token vào database:                               │
│     UPDATE users SET remember_token = 'random_token' WHERE id = 1          │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│            SAU 2 GIỜ (Session hết hạn, Remember còn)                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. User truy cập lại website                                              │
│     ↓                                                                       │
│  2. laravel_session đã hết hạn → User chưa đăng nhập                       │
│     ↓                                                                       │
│  3. Laravel kiểm tra remember_web_xxxxx cookie                             │
│     ↓                                                                       │
│  4. Lấy user_id và token từ cookie                                         │
│     ↓                                                                       │
│  5. So sánh với remember_token trong database                              │
│     ↓                                                                       │
│  6. Nếu khớp → TỰ ĐỘNG đăng nhập lại (tạo session mới)                    │
│     ↓                                                                       │
│  7. User vào dashboard mà KHÔNG cần nhập lại password                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## ⏱️ Thời hạn Remember Me (5 năm - Hardcoded)

Thời hạn 5 năm được định nghĩa trong Laravel core:

```php
// vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php

protected function createRememberTokenIfDoesntExist(AuthenticatableContract $user)
{
    // ...
}

// Cookie lifetime: 2628000 phút = 5 năm
protected function getCookieExpirationMinutes()
{
    return 2628000; // 5 years in minutes
}
```

## ❓ Bạn muốn tùy chỉnh thời hạn Remember Me?

Nếu bạn muốn thay đổi thời hạn (ví dụ: 30 ngày thay vì 5 năm), bạn cần tạo custom guard hoặc override `SessionGuard`. 

**Chuyển sang Agent mode** nếu bạn muốn tôi:
1. Tạo config cho remember me lifetime
2. Override SessionGuard để đọc config đó

---

## 📋 Tóm tắt

| Cấu hình | Giá trị | Ảnh hưởng |
|----------|---------|-----------|
| `SESSION_LIFETIME=120` | 120 phút (2 giờ) | Session bình thường |
| Remember Me (mặc định) | 2,628,000 phút (5 năm) | Cookie remember_web_xxx |

**Kết luận:** Chức năng Remember Me **ĐÃ HOẠT ĐỘNG** đúng. Khi user tích "Ghi nhớ đăng nhập", họ sẽ được tự động đăng nhập lại trong 5 năm (hoặc cho đến khi logout).

```php
$remember = $request->boolean('remember');
Auth::attempt($credentials, $remember);  // ← Truyền $remember vào
```

```plaintext
┌─────────────────────────────────────────────────────────────────────────────┐
│                    SESSION vs REMEMBER ME                                   │
├───────────────────────────────┬─────────────────────────────────────────────┤
│         SESSION               │           REMEMBER ME                       │
├───────────────────────────────┼─────────────────────────────────────────────┤
│ Cookie: laravel_session       │ Cookie: remember_web_xxxxx                  │
│                               │                                             │
│ Thời hạn: 120 phút (2 giờ)    │ Thời hạn: 5 NĂM (mặc định Laravel)         │
│ (SESSION_LIFETIME=120)        │ (Hardcoded trong Laravel core)              │
│                               │                                             │
│ Hết hạn khi:                  │ Hết hạn khi:                                │
│ - Không hoạt động 2 giờ       │ - Sau 5 năm                                 │
│ - Đóng browser (nếu config)   │ - User logout                               │
│                               │ - Admin xóa remember_token                  │
│                               │                                             │
│ Lưu trữ:                      │ Lưu trữ:                                    │
│ - Cookie: session_id          │ - Cookie: user_id + token                   │
│ - Server: session data        │ - Database: remember_token column           │
└───────────────────────────────┴─────────────────────────────────────────────┘
```

```plaintext
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ĐĂNG NHẬP VỚI REMEMBER ME                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. User login với remember = true                                          │
│     ↓                                                                       │
│  2. Laravel tạo 2 cookies:                                                  │
│     • laravel_session (2 giờ)                                              │
│     • remember_web_xxxxx (5 năm)                                           │
│     ↓                                                                       │
│  3. Laravel lưu remember_token vào database:                               │
│     UPDATE users SET remember_token = 'random_token' WHERE id = 1          │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│            SAU 2 GIỜ (Session hết hạn, Remember còn)                       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. User truy cập lại website                                              │
│     ↓                                                                       │
│  2. laravel_session đã hết hạn → User chưa đăng nhập                       │
│     ↓                                                                       │
│  3. Laravel kiểm tra remember_web_xxxxx cookie                             │
│     ↓                                                                       │
│  4. Lấy user_id và token từ cookie                                         │
│     ↓                                                                       │
│  5. So sánh với remember_token trong database                              │
│     ↓                                                                       │
│  6. Nếu khớp → TỰ ĐỘNG đăng nhập lại (tạo session mới)                    │
│     ↓                                                                       │
│  7. User vào dashboard mà KHÔNG cần nhập lại password                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

```php
// vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php

protected function createRememberTokenIfDoesntExist(AuthenticatableContract $user)
{
    // ...
}

// Cookie lifetime: 2628000 phút = 5 năm
protected function getCookieExpirationMinutes()
{
    return 2628000; // 5 years in minutes
}
```

---

**User**

à có nghĩa chức năng remember me là mặc định hả tôi tưởng cái đó mình code ra ko à

---

**Cursor**

Đúng rồi! Để tôi giải thích rõ hơn:

## 🎯 Remember Me = Laravel có sẵn, nhưng mình phải "bật" nó

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    LARAVEL CÓ SẴN (không cần code)                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ✅ Cơ chế tạo remember_token                                              │
│  ✅ Cơ chế tạo cookie remember_web_xxx                                     │
│  ✅ Cơ chế tự động đăng nhập lại khi session hết hạn                       │
│  ✅ Cột remember_token trong migration users (mặc định)                    │
│  ✅ Thời hạn 5 năm                                                         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    MÌNH PHẢI CODE (để kích hoạt)                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ✍️ Checkbox "Ghi nhớ đăng nhập" ở frontend                                │
│  ✍️ Gửi giá trị remember từ form                                           │
│  ✍️ Truyền $remember vào Auth::attempt()                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 📝 Những gì MÌNH đã code:

### 1. Frontend - Checkbox
```jsx
// Login.jsx
<Checkbox
    checked={data.remember}
    onChange={(e) => setData('remember', e.target.checked)}
>
    Ghi nhớ đăng nhập
</Checkbox>
```

### 2. Backend - Truyền tham số
```php
// AuthController.php
$remember = $request->boolean('remember');  // ← Lấy giá trị từ form

Auth::attempt($credentials, $remember);     // ← Truyền vào đây
//                         ↑
//            Tham số thứ 2 này KÍCH HOẠT Remember Me
```

### 3. Database - Cột có sẵn
```php
// Migration users (Laravel tạo sẵn)
$table->rememberToken();  // ← Cột này có sẵn từ đầu
```

## 🔄 So sánh: Có vs Không truyền $remember

```php
// ❌ KHÔNG có Remember Me
Auth::attempt($credentials);
// hoặc
Auth::attempt($credentials, false);

// ✅ CÓ Remember Me  
Auth::attempt($credentials, true);
```

## 📊 Tóm tắt

| Phần | Ai làm? | Mô tả |
|------|---------|-------|
| Cơ chế Remember | **Laravel** | Tự động xử lý cookie, token, auto-login |
| Migration `remember_token` | **Laravel** | Có sẵn trong migration users |
| Checkbox UI | **Mình** | Tạo checkbox cho user tích |
| Truyền `$remember` vào `Auth::attempt()` | **Mình** | 1 dòng code để "bật" tính năng |

**Kết luận:** Laravel làm 90% công việc, mình chỉ cần thêm checkbox và truyền 1 tham số `true/false` vào `Auth::attempt()` là xong! 🎉

```plaintext
┌─────────────────────────────────────────────────────────────────────────────┐
│                    LARAVEL CÓ SẴN (không cần code)                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ✅ Cơ chế tạo remember_token                                              │
│  ✅ Cơ chế tạo cookie remember_web_xxx                                     │
│  ✅ Cơ chế tự động đăng nhập lại khi session hết hạn                       │
│  ✅ Cột remember_token trong migration users (mặc định)                    │
│  ✅ Thời hạn 5 năm                                                         │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                    MÌNH PHẢI CODE (để kích hoạt)                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ✍️ Checkbox "Ghi nhớ đăng nhập" ở frontend                                │
│  ✍️ Gửi giá trị remember từ form                                           │
│  ✍️ Truyền $remember vào Auth::attempt()                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

```javascriptreact
// Login.jsx
<Checkbox
    checked={data.remember}
    onChange={(e) => setData('remember', e.target.checked)}
>
    Ghi nhớ đăng nhập
</Checkbox>
```

```php
// AuthController.php
$remember = $request->boolean('remember');  // ← Lấy giá trị từ form

Auth::attempt($credentials, $remember);     // ← Truyền vào đây
//                         ↑
//            Tham số thứ 2 này KÍCH HOẠT Remember Me
```

```php
// Migration users (Laravel tạo sẵn)
$table->rememberToken();  // ← Cột này có sẵn từ đầu
```

```php
// ❌ KHÔNG có Remember Me
Auth::attempt($credentials);
// hoặc
Auth::attempt($credentials, false);

// ✅ CÓ Remember Me  
Auth::attempt($credentials, true);
```

---

**User**

ok tốt rồi giờ bạn giúp tôi làm chức năng phân quyền cho user theo màn hình nha bạn. tôi gợi ý cách làm là có thể dùng đệ quy nếu bạn có cách tốt hơn thì cứ làm. tôi có ảnh gợi ý cho bạn về giao diện phân quyền nè bạn k cần làm giống đâu tôi gửi để bạn định hướng làm đúng á 

---

**Cursor**

Tôi hiểu yêu cầu của bạn! Dựa trên hình ảnh, tôi sẽ thiết kế hệ thống phân quyền theo màn hình với cấu trúc đệ quy. Trước tiên, để tôi phân tích dự án hiện tại:

Tôi sẽ thiết kế hệ thống phân quyền hoàn chỉnh với cấu trúc đệ quy. Bắt đầu thôi!

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Tên màn hình
            $table->string('code')->unique();                // Mã màn hình (dùng để check quyền)
            $table->string('route')->nullable();             // Route path (VD: /co-so, /khu-nha)
            $table->string('icon')->nullable();              // Icon class name
            $table->unsignedBigInteger('parent_id')->nullable(); // Parent ID cho đệ quy
            $table->integer('order')->default(0);            // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);     // Trạng thái hoạt động
            $table->boolean('is_menu')->default(true);       // Có hiển thị trên menu không
            $table->timestamps();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('screens')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('screens');
    }
}

```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('screen_id');
            $table->boolean('can_view')->default(false);     // Quyền xem
            $table->boolean('can_create')->default(false);   // Quyền thêm
            $table->boolean('can_edit')->default(false);     // Quyền sửa
            $table->boolean('can_delete')->default(false);   // Quyền xóa
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('screen_id')
                  ->references('id')
                  ->on('screens')
                  ->onDelete('cascade');

            // Mỗi user chỉ có 1 bản ghi permission cho mỗi screen
            $table->unique(['user_id', 'screen_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_permissions');
    }
}

```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'route',
        'icon',
        'parent_id',
        'order',
        'is_active',
        'is_menu',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_menu' => 'boolean',
    ];

    /**
     * Parent screen (đệ quy)
     */
    public function parent()
    {
        return $this->belongsTo(Screen::class, 'parent_id');
    }

    /**
     * Children screens (đệ quy)
     */
    public function children()
    {
        return $this->hasMany(Screen::class, 'parent_id')->orderBy('order');
    }

    /**
     * Đệ quy lấy tất cả children và children của children
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    /**
     * User permissions cho screen này
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Lấy tất cả screens dạng tree
     */
    public static function getTree()
    {
        return self::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with('allChildren')
            ->get();
    }

    /**
     * Lấy tất cả screens dạng flat (phẳng) với level
     */
    public static function getFlatTree($parentId = null, $level = 0)
    {
        $result = [];
        $screens = self::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        foreach ($screens as $screen) {
            $screen->level = $level;
            $result[] = $screen;
            $result = array_merge($result, self::getFlatTree($screen->id, $level + 1));
        }

        return $result;
    }

    /**
     * Lấy tất cả parent IDs (đệ quy lên trên)
     */
    public function getParentIds()
    {
        $parentIds = [];
        $parent = $this->parent;
        
        while ($parent) {
            $parentIds[] = $parent->id;
            $parent = $parent->parent;
        }
        
        return $parentIds;
    }

    /**
     * Lấy tất cả children IDs (đệ quy xuống dưới)
     */
    public function getChildrenIds()
    {
        $childrenIds = [];
        
        foreach ($this->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $child->getChildrenIds());
        }
        
        return $childrenIds;
    }
}

```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'screen_id',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Screen
     */
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Kiểm tra có bất kỳ quyền nào không
     */
    public function hasAnyPermission()
    {
        return $this->can_view || $this->can_create || $this->can_edit || $this->can_delete;
    }
}

```

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * User permissions
     */
    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Lấy permission cho một screen cụ thể
     */
    public function getPermissionForScreen($screenCode)
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
            ];
        }

        $screen = Screen::where('code', $screenCode)->first();
        
        if (!$screen) {
            return [
                'can_view' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
            ];
        }

        $permission = $this->permissions()
            ->where('screen_id', $screen->id)
            ->first();

        if (!$permission) {
            return [
                'can_view' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false,
            ];
        }

        return [
            'can_view' => $permission->can_view,
            'can_create' => $permission->can_create,
            'can_edit' => $permission->can_edit,
            'can_delete' => $permission->can_delete,
        ];
    }

    /**
     * Kiểm tra quyền cụ thể trên một screen
     */
    public function hasPermission($screenCode, $permission = 'can_view')
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return true;
        }

        $screen = Screen::where('code', $screenCode)->first();
        
        if (!$screen) {
            return false;
        }

        $userPermission = $this->permissions()
            ->where('screen_id', $screen->id)
            ->first();

        return $userPermission && $userPermission->{$permission};
    }

    /**
     * Kiểm tra có quyền xem màn hình không
     */
    public function canView($screenCode)
    {
        return $this->hasPermission($screenCode, 'can_view');
    }

    /**
     * Kiểm tra có quyền thêm không
     */
    public function canCreate($screenCode)
    {
        return $this->hasPermission($screenCode, 'can_create');
    }

    /**
     * Kiểm tra có quyền sửa không
     */
    public function canEdit($screenCode)
    {
        return $this->hasPermission($screenCode, 'can_edit');
    }

    /**
     * Kiểm tra có quyền xóa không
     */
    public function canDelete($screenCode)
    {
        return $this->hasPermission($screenCode, 'can_delete');
    }

    /**
     * Lấy danh sách các screen_id mà user có quyền xem
     */
    public function getViewableScreenIds()
    {
        if ($this->isAdmin()) {
            return Screen::where('is_active', true)->pluck('id')->toArray();
        }

        return $this->permissions()
            ->where('can_view', true)
            ->pluck('screen_id')
            ->toArray();
    }

    /**
     * Lấy tất cả permissions của user dạng array
     */
    public function getAllPermissions()
    {
        $permissions = [];
        
        foreach ($this->permissions as $permission) {
            $permissions[$permission->screen_id] = [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        }
        
        return $permissions;
    }
}

```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Xóa dữ liệu cũ
        Screen::truncate();

        // 1. Dashboard
        $dashboard = Screen::create([
            'name' => 'Dashboard',
            'code' => 'dashboard',
            'route' => '/',
            'icon' => 'DashboardOutlined',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 2. Quản lý Cơ sở hạ tầng
        $coSoGroup = Screen::create([
            'name' => 'QL Cơ sở hạ tầng',
            'code' => 'co-so-group',
            'route' => null,
            'icon' => 'BankOutlined',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách cơ sở',
            'code' => 'co-so',
            'route' => '/co-so',
            'icon' => null,
            'parent_id' => $coSoGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 3. Quản lý Khu nhà
        $khuNhaGroup = Screen::create([
            'name' => 'QL Khu nhà, Chức năng',
            'code' => 'khu-nha-group',
            'route' => null,
            'icon' => 'HomeOutlined',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách khu nhà',
            'code' => 'khu-nha',
            'route' => '/khu-nha',
            'icon' => null,
            'parent_id' => $khuNhaGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 4. Quản lý Phòng
        $phongGroup = Screen::create([
            'name' => 'QL Phòng',
            'code' => 'phong-group',
            'route' => null,
            'icon' => 'AppstoreOutlined',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách phòng',
            'code' => 'phong',
            'route' => '/phong',
            'icon' => null,
            'parent_id' => $phongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 5. Quản lý Thiết bị
        $thietBiGroup = Screen::create([
            'name' => 'QL Thiết bị',
            'code' => 'thiet-bi-group',
            'route' => null,
            'icon' => 'ToolOutlined',
            'parent_id' => null,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách thiết bị',
            'code' => 'thiet-bi',
            'route' => '/thiet-bi',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Lịch sử bảo dưỡng',
            'code' => 'lich-su-bao-duong',
            'route' => '/lich-su-bao-duong',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 6. Quản lý Hệ thống
        $heThongGroup = Screen::create([
            'name' => 'Quản lý Hệ thống',
            'code' => 'he-thong-group',
            'route' => null,
            'icon' => 'SettingOutlined',
            'parent_id' => null,
            'order' => 6,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách người dùng',
            'code' => 'nguoi-dung',
            'route' => '/nguoi-dung',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Phân quyền',
            'code' => 'phan-quyen',
            'route' => '/phan-quyen',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        $this->command->info('Đã tạo ' . Screen::count() . ' màn hình');
    }
}

```

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Screen;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Hiển thị trang phân quyền
     */
    public function index()
    {
        $users = User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $screens = $this->getScreensTree();

        return Inertia::render('Permission/Index', [
            'users' => $users,
            'screens' => $screens,
        ]);
    }

    /**
     * Lấy quyền của một user
     */
    public function getUserPermissions(User $user)
    {
        $permissions = $user->permissions()
            ->get()
            ->keyBy('screen_id')
            ->map(function ($permission) {
                return [
                    'can_view' => $permission->can_view,
                    'can_create' => $permission->can_create,
                    'can_edit' => $permission->can_edit,
                    'can_delete' => $permission->can_delete,
                ];
            });

        return response()->json($permissions);
    }

    /**
     * Cập nhật quyền của một user
     */
    public function updateUserPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.screen_id' => 'required|exists:screens,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_edit' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
        ]);

        // Xóa tất cả quyền cũ của user
        $user->permissions()->delete();

        // Thêm quyền mới
        foreach ($request->permissions as $permission) {
            // Chỉ tạo bản ghi nếu có ít nhất 1 quyền
            if ($permission['can_view'] || $permission['can_create'] || 
                $permission['can_edit'] || $permission['can_delete']) {
                UserPermission::create([
                    'user_id' => $user->id,
                    'screen_id' => $permission['screen_id'],
                    'can_view' => $permission['can_view'] ?? false,
                    'can_create' => $permission['can_create'] ?? false,
                    'can_edit' => $permission['can_edit'] ?? false,
                    'can_delete' => $permission['can_delete'] ?? false,
                ]);
            }
        }

        return back()->with('success', 'Cập nhật phân quyền thành công!');
    }

    /**
     * Lấy danh sách screens dạng tree
     */
    private function getScreensTree()
    {
        return Screen::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order')
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->orderBy('order');
                    }]);
            }])
            ->get();
    }

    /**
     * Lấy danh sách screens dạng flat để hiển thị dạng table
     */
    public function getScreensFlat()
    {
        $screens = Screen::getFlatTree();
        return response()->json($screens);
    }
}

```

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Hiển thị form tạo người dùng mới
     */
    public function create()
    {
        return Inertia::render('User/Create');
    }

    /**
     * Lưu người dùng mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('nguoi-dung.index')->with('success', 'Thêm người dùng thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa người dùng
     */
    public function edit(User $nguoi_dung)
    {
        return Inertia::render('User/Edit', [
            'user' => $nguoi_dung,
        ]);
    }

    /**
     * Cập nhật người dùng
     */
    public function update(Request $request, User $nguoi_dung)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($nguoi_dung->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $nguoi_dung->update($data);

        return redirect()->route('nguoi-dung.index')->with('success', 'Cập nhật người dùng thành công!');
    }

    /**
     * Xóa người dùng
     */
    public function destroy(User $nguoi_dung)
    {
        // Không cho xóa chính mình
        if ($nguoi_dung->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính mình!');
        }

        $nguoi_dung->delete();

        return redirect()->route('nguoi-dung.index')->with('success', 'Xóa người dùng thành công!');
    }
}

```

```javascriptreact
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
    Tooltip,
} from 'antd';
import {
    SaveOutlined,
    ReloadOutlined,
    UserOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
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

    // Chuyển screens tree thành flat array để hiển thị trong table
    const flattenScreens = (items, level = 0, result = []) => {
        items.forEach((item) => {
            result.push({
                ...item,
                level,
                key: item.id,
            });
            if (item.children && item.children.length > 0) {
                flattenScreens(item.children, level + 1, result);
            }
        });
        return result;
    };

    const flatScreens = useMemo(() => flattenScreens(screens), [screens]);

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

    // Xử lý thay đổi checkbox
    const handlePermissionChange = (screenId, permissionType, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            if (!newPermissions[screenId]) {
                newPermissions[screenId] = {
                    can_view: false,
                    can_create: false,
                    can_edit: false,
                    can_delete: false,
                };
            }
            newPermissions[screenId][permissionType] = checked;

            // Nếu bỏ tích can_view, tự động bỏ các quyền khác
            if (permissionType === 'can_view' && !checked) {
                newPermissions[screenId].can_create = false;
                newPermissions[screenId].can_edit = false;
                newPermissions[screenId].can_delete = false;
            }

            // Nếu tích quyền khác, tự động tích can_view
            if (permissionType !== 'can_view' && checked) {
                newPermissions[screenId].can_view = true;
            }

            return newPermissions;
        });
    };

    // Xử lý tích/bỏ tích cả dòng
    const handleRowCheckAll = (screenId, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            newPermissions[screenId] = {
                can_view: checked,
                can_create: checked,
                can_edit: checked,
                can_delete: checked,
            };
            return newPermissions;
        });
    };

    // Xử lý tích/bỏ tích cả cột
    const handleColumnCheckAll = (permissionType, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            flatScreens.forEach((screen) => {
                if (!newPermissions[screen.id]) {
                    newPermissions[screen.id] = {
                        can_view: false,
                        can_create: false,
                        can_edit: false,
                        can_delete: false,
                    };
                }
                
                if (permissionType === 'can_view') {
                    newPermissions[screen.id].can_view = checked;
                    if (!checked) {
                        newPermissions[screen.id].can_create = false;
                        newPermissions[screen.id].can_edit = false;
                        newPermissions[screen.id].can_delete = false;
                    }
                } else {
                    newPermissions[screen.id][permissionType] = checked;
                    if (checked) {
                        newPermissions[screen.id].can_view = true;
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
        return flatScreens.every((screen) => permissions[screen.id]?.[permissionType]);
    };

    const isColumnIndeterminate = (permissionType) => {
        const checkedCount = flatScreens.filter((screen) => permissions[screen.id]?.[permissionType]).length;
        return checkedCount > 0 && checkedCount < flatScreens.length;
    };

    // Columns cho table
    const columns = [
        {
            title: 'Tên chức năng',
            dataIndex: 'name',
            key: 'name',
            width: 300,
            fixed: 'left',
            render: (text, record) => (
                <div style={{ paddingLeft: record.level * 24 }}>
                    {record.level > 0 && (
                        <span style={{ color: '#999', marginRight: 8 }}>└</span>
                    )}
                    <Text strong={record.level === 0}>{text}</Text>
                    {record.route && (
                        <Tag color="blue" style={{ marginLeft: 8, fontSize: 10 }}>
                            {record.route}
                        </Tag>
                    )}
                </div>
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
            width: 80,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={permissions[record.id]?.can_view || false}
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
            width: 80,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={permissions[record.id]?.can_create || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_create', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
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
            width: 80,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={permissions[record.id]?.can_edit || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_edit', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
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
            width: 80,
            align: 'center',
            render: (_, record) => (
                <Checkbox
                    checked={permissions[record.id]?.can_delete || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_delete', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
                />
            ),
        },
        {
            title: 'Tất cả',
            key: 'all',
            width: 80,
            align: 'center',
            render: (_, record) => {
                const perms = permissions[record.id] || {};
                const allChecked = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
                const someChecked = perms.can_view || perms.can_create || perms.can_edit || perms.can_delete;
                
                return (
                    <Checkbox
                        checked={allChecked}
                        indeterminate={someChecked && !allChecked}
                        onChange={(e) => handleRowCheckAll(record.id, e.target.checked)}
                        disabled={!selectedUserId}
                    />
                );
            },
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
                        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
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

                        {/* Bảng phân quyền */}
                        <Spin spinning={loading}>
                            {selectedUserId ? (
                                <Table
                                    columns={columns}
                                    dataSource={flatScreens}
                                    pagination={false}
                                    bordered
                                    size="middle"
                                    scroll={{ x: 800 }}
                                    rowClassName={(record) => 
                                        record.level === 0 ? 'parent-row' : ''
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
                                        <Text type="secondary">Lưu ý: Admin có tất cả quyền mặc định, không cần phân quyền</Text>
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
                }
                .parent-row:hover > td {
                    background-color: #f0f0f0 !important;
                }
            `}</style>
        </MainLayout>
    );
};

export default PermissionIndex;

```

```javascriptreact
import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Table,
    Button,
    Input,
    Select,
    Space,
    Tag,
    Popconfirm,
    Typography,
    Avatar,
} from 'antd';
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    SearchOutlined,
    UserOutlined,
    KeyOutlined,
} from '@ant-design/icons';

const { Title } = Typography;
const { Option } = Select;

const UserIndex = ({ users, filters }) => {
    const [search, setSearch] = useState(filters.search || '');
    const [role, setRole] = useState(filters.role || '');

    const handleSearch = () => {
        router.get('/nguoi-dung', { search, role }, { preserveState: true });
    };

    const handleDelete = (id) => {
        router.delete(`/nguoi-dung/${id}`);
    };

    const columns = [
        {
            title: 'ID',
            dataIndex: 'id',
            key: 'id',
            width: 70,
        },
        {
            title: 'Người dùng',
            key: 'user',
            render: (_, record) => (
                <Space>
                    <Avatar 
                        style={{ 
                            backgroundColor: record.role === 'admin' ? '#f5222d' : '#1890ff' 
                        }}
                        icon={<UserOutlined />}
                    />
                    <div>
                        <div style={{ fontWeight: 500 }}>{record.name}</div>
                        <div style={{ fontSize: 12, color: '#666' }}>{record.email}</div>
                    </div>
                </Space>
            ),
        },
        {
            title: 'Vai trò',
            dataIndex: 'role',
            key: 'role',
            width: 150,
            render: (role) => (
                <Tag color={role === 'admin' ? 'red' : 'blue'}>
                    {role === 'admin' ? 'Quản trị viên' : 'Người dùng'}
                </Tag>
            ),
        },
        {
            title: 'Ngày tạo',
            dataIndex: 'created_at',
            key: 'created_at',
            width: 150,
            render: (date) => new Date(date).toLocaleDateString('vi-VN'),
        },
        {
            title: 'Thao tác',
            key: 'action',
            width: 200,
            render: (_, record) => (
                <Space>
                    <Link href={`/nguoi-dung/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    {record.role !== 'admin' && (
                        <Link href={`/phan-quyen?user=${record.id}`}>
                            <Button size="small" icon={<KeyOutlined />}>
                                Phân quyền
                            </Button>
                        </Link>
                    )}
                    <Popconfirm
                        title="Xác nhận xóa"
                        description="Bạn có chắc chắn muốn xóa người dùng này?"
                        onConfirm={() => handleDelete(record.id)}
                        okText="Xóa"
                        cancelText="Hủy"
                        okButtonProps={{ danger: true }}
                    >
                        <Button danger size="small" icon={<DeleteOutlined />}>
                            Xóa
                        </Button>
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>Quản lý người dùng</Title>
                    <Link href="/nguoi-dung/create">
                        <Button type="primary" icon={<PlusOutlined />}>
                            Thêm người dùng
                        </Button>
                    </Link>
                </div>

                <Card>
                    <Space style={{ marginBottom: 16 }}>
                        <Input
                            placeholder="Tìm kiếm theo tên, email..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onPressEnter={handleSearch}
                            style={{ width: 250 }}
                            prefix={<SearchOutlined />}
                        />
                        <Select
                            placeholder="Lọc theo vai trò"
                            value={role || undefined}
                            onChange={(value) => setRole(value)}
                            style={{ width: 150 }}
                            allowClear
                        >
                            <Option value="admin">Quản trị viên</Option>
                            <Option value="user">Người dùng</Option>
                        </Select>
                        <Button type="primary" onClick={handleSearch}>
                            Tìm kiếm
                        </Button>
                    </Space>

                    <Table
                        columns={columns}
                        dataSource={users.data}
                        rowKey="id"
                        pagination={{
                            current: users.current_page,
                            total: users.total,
                            pageSize: users.per_page,
                            showSizeChanger: false,
                            showTotal: (total) => `Tổng ${total} người dùng`,
                            onChange: (page) => {
                                router.get('/nguoi-dung', { ...filters, page }, { preserveState: true });
                            },
                        }}
                    />
                </Card>
            </Space>
        </MainLayout>
    );
};

export default UserIndex;

```

```javascriptreact
import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Form,
    Input,
    Select,
    Button,
    Space,
    Typography,
} from 'antd';
import { SaveOutlined, ArrowLeftOutlined } from '@ant-design/icons';

const { Title } = Typography;
const { Option } = Select;

const UserCreate = () => {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'user',
    });

    const handleSubmit = () => {
        post('/nguoi-dung');
    };

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>Thêm người dùng mới</Title>
                    <Link href="/nguoi-dung">
                        <Button icon={<ArrowLeftOutlined />}>Quay lại</Button>
                    </Link>
                </div>

                <Card style={{ maxWidth: 600 }}>
                    <Form layout="vertical" onFinish={handleSubmit}>
                        <Form.Item
                            label="Họ và tên"
                            validateStatus={errors.name ? 'error' : ''}
                            help={errors.name}
                            required
                        >
                            <Input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Nhập họ và tên"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Email"
                            validateStatus={errors.email ? 'error' : ''}
                            help={errors.email}
                            required
                        >
                            <Input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="Nhập email"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Mật khẩu"
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                            required
                        >
                            <Input.Password
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Nhập mật khẩu (ít nhất 6 ký tự)"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Xác nhận mật khẩu"
                            validateStatus={errors.password_confirmation ? 'error' : ''}
                            help={errors.password_confirmation}
                            required
                        >
                            <Input.Password
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Nhập lại mật khẩu"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Vai trò"
                            validateStatus={errors.role ? 'error' : ''}
                            help={errors.role}
                            required
                        >
                            <Select
                                value={data.role}
                                onChange={(value) => setData('role', value)}
                            >
                                <Option value="user">Người dùng</Option>
                                <Option value="admin">Quản trị viên</Option>
                            </Select>
                        </Form.Item>

                        <Form.Item>
                            <Space>
                                <Button
                                    type="primary"
                                    htmlType="submit"
                                    loading={processing}
                                    icon={<SaveOutlined />}
                                >
                                    Lưu người dùng
                                </Button>
                                <Link href="/nguoi-dung">
                                    <Button>Hủy</Button>
                                </Link>
                            </Space>
                        </Form.Item>
                    </Form>
                </Card>
            </Space>
        </MainLayout>
    );
};

export default UserCreate;

```

```javascriptreact
import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Form,
    Input,
    Select,
    Button,
    Space,
    Typography,
    Alert,
} from 'antd';
import { SaveOutlined, ArrowLeftOutlined } from '@ant-design/icons';

const { Title, Text } = Typography;
const { Option } = Select;

const UserEdit = ({ user }) => {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        role: user.role || 'user',
    });

    const handleSubmit = () => {
        put(`/nguoi-dung/${user.id}`);
    };

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>Chỉnh sửa người dùng</Title>
                    <Link href="/nguoi-dung">
                        <Button icon={<ArrowLeftOutlined />}>Quay lại</Button>
                    </Link>
                </div>

                <Card style={{ maxWidth: 600 }}>
                    <Form layout="vertical" onFinish={handleSubmit}>
                        <Form.Item
                            label="Họ và tên"
                            validateStatus={errors.name ? 'error' : ''}
                            help={errors.name}
                            required
                        >
                            <Input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Nhập họ và tên"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Email"
                            validateStatus={errors.email ? 'error' : ''}
                            help={errors.email}
                            required
                        >
                            <Input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="Nhập email"
                            />
                        </Form.Item>

                        <Alert
                            message="Để trống nếu không muốn thay đổi mật khẩu"
                            type="info"
                            showIcon
                            style={{ marginBottom: 16 }}
                        />

                        <Form.Item
                            label="Mật khẩu mới"
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                        >
                            <Input.Password
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Nhập mật khẩu mới (để trống nếu không đổi)"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Xác nhận mật khẩu mới"
                            validateStatus={errors.password_confirmation ? 'error' : ''}
                            help={errors.password_confirmation}
                        >
                            <Input.Password
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Nhập lại mật khẩu mới"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Vai trò"
                            validateStatus={errors.role ? 'error' : ''}
                            help={errors.role}
                            required
                        >
                            <Select
                                value={data.role}
                                onChange={(value) => setData('role', value)}
                            >
                                <Option value="user">Người dùng</Option>
                                <Option value="admin">Quản trị viên</Option>
                            </Select>
                        </Form.Item>

                        <Form.Item>
                            <Space>
                                <Button
                                    type="primary"
                                    htmlType="submit"
                                    loading={processing}
                                    icon={<SaveOutlined />}
                                >
                                    Cập nhật
                                </Button>
                                <Link href="/nguoi-dung">
                                    <Button>Hủy</Button>
                                </Link>
                            </Space>
                        </Form.Item>
                    </Form>
                </Card>
            </Space>
        </MainLayout>
    );
};

export default UserEdit;

```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $screenCode
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $screenCode, $permission = 'can_view')
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra quyền
        if (!$user->hasPermission($screenCode, $permission)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            abort(403, 'Bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}

```

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ];
}

```

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth Routes - Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout - Auth only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Quản lý Cơ sở
    Route::middleware('permission:co-so,can_view')->group(function () {
        Route::get('/co-so', [CoSoController::class, 'index'])->name('co-so.index');
        Route::get('/co-so/{co_so}', [CoSoController::class, 'show'])->name('co-so.show');
    });
    Route::middleware('permission:co-so,can_create')->group(function () {
        Route::get('/co-so/create', [CoSoController::class, 'create'])->name('co-so.create');
        Route::post('/co-so', [CoSoController::class, 'store'])->name('co-so.store');
    });
    Route::middleware('permission:co-so,can_edit')->group(function () {
        Route::get('/co-so/{co_so}/edit', [CoSoController::class, 'edit'])->name('co-so.edit');
        Route::put('/co-so/{co_so}', [CoSoController::class, 'update'])->name('co-so.update');
    });
    Route::middleware('permission:co-so,can_delete')->group(function () {
        Route::delete('/co-so/{co_so}', [CoSoController::class, 'destroy'])->name('co-so.destroy');
    });

    // Quản lý Khu nhà
    Route::middleware('permission:khu-nha,can_view')->group(function () {
        Route::get('/khu-nha', [KhuNhaController::class, 'index'])->name('khu-nha.index');
        Route::get('/khu-nha/{khu_nha}', [KhuNhaController::class, 'show'])->name('khu-nha.show');
    });
    Route::middleware('permission:khu-nha,can_create')->group(function () {
        Route::get('/khu-nha/create', [KhuNhaController::class, 'create'])->name('khu-nha.create');
        Route::post('/khu-nha', [KhuNhaController::class, 'store'])->name('khu-nha.store');
    });
    Route::middleware('permission:khu-nha,can_edit')->group(function () {
        Route::get('/khu-nha/{khu_nha}/edit', [KhuNhaController::class, 'edit'])->name('khu-nha.edit');
        Route::put('/khu-nha/{khu_nha}', [KhuNhaController::class, 'update'])->name('khu-nha.update');
    });
    Route::middleware('permission:khu-nha,can_delete')->group(function () {
        Route::delete('/khu-nha/{khu_nha}', [KhuNhaController::class, 'destroy'])->name('khu-nha.destroy');
    });

    // Quản lý Phòng
    Route::middleware('permission:phong,can_view')->group(function () {
        Route::get('/phong', [PhongController::class, 'index'])->name('phong.index');
        Route::get('/phong/{phong}', [PhongController::class, 'show'])->name('phong.show');
    });
    Route::middleware('permission:phong,can_create')->group(function () {
        Route::get('/phong/create', [PhongController::class, 'create'])->name('phong.create');
        Route::post('/phong', [PhongController::class, 'store'])->name('phong.store');
    });
    Route::middleware('permission:phong,can_edit')->group(function () {
        Route::get('/phong/{phong}/edit', [PhongController::class, 'edit'])->name('phong.edit');
        Route::put('/phong/{phong}', [PhongController::class, 'update'])->name('phong.update');
    });
    Route::middleware('permission:phong,can_delete')->group(function () {
        Route::delete('/phong/{phong}', [PhongController::class, 'destroy'])->name('phong.destroy');
    });

    // Quản lý Thiết bị
    Route::middleware('permission:thiet-bi,can_view')->group(function () {
        Route::get('/thiet-bi', [ThietBiController::class, 'index'])->name('thiet-bi.index');
        Route::get('/thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
        Route::get('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'show'])->name('thiet-bi.show');
    });
    Route::middleware('permission:thiet-bi,can_create')->group(function () {
        Route::get('/thiet-bi/create', [ThietBiController::class, 'create'])->name('thiet-bi.create');
        Route::post('/thiet-bi', [ThietBiController::class, 'store'])->name('thiet-bi.store');
        Route::get('/thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    });
    Route::middleware('permission:thiet-bi,can_edit')->group(function () {
        Route::get('/thiet-bi/{thiet_bi}/edit', [ThietBiController::class, 'edit'])->name('thiet-bi.edit');
        Route::put('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'update'])->name('thiet-bi.update');
    });
    Route::middleware('permission:thiet-bi,can_delete')->group(function () {
        Route::delete('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'destroy'])->name('thiet-bi.destroy');
    });

    // Quản lý Lịch sử Bảo dưỡng
    Route::middleware('permission:lich-su-bao-duong,can_view')->group(function () {
        Route::get('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'index'])->name('lich-su-bao-duong.index');
        Route::get('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'show'])->name('lich-su-bao-duong.show');
        Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
    });
    Route::middleware('permission:lich-su-bao-duong,can_create')->group(function () {
        Route::get('/lich-su-bao-duong/create', [LichSuBaoDuongController::class, 'create'])->name('lich-su-bao-duong.create');
        Route::post('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'store'])->name('lich-su-bao-duong.store');
    });
    Route::middleware('permission:lich-su-bao-duong,can_edit')->group(function () {
        Route::get('/lich-su-bao-duong/{lich_su_bao_duong}/edit', [LichSuBaoDuongController::class, 'edit'])->name('lich-su-bao-duong.edit');
        Route::put('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'update'])->name('lich-su-bao-duong.update');
    });
    Route::middleware('permission:lich-su-bao-duong,can_delete')->group(function () {
        Route::delete('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'destroy'])->name('lich-su-bao-duong.destroy');
    });

    // Quản lý Người dùng (Chỉ Admin)
    Route::middleware('permission:nguoi-dung,can_view')->group(function () {
        Route::get('/nguoi-dung', [UserController::class, 'index'])->name('nguoi-dung.index');
    });
    Route::middleware('permission:nguoi-dung,can_create')->group(function () {
        Route::get('/nguoi-dung/create', [UserController::class, 'create'])->name('nguoi-dung.create');
        Route::post('/nguoi-dung', [UserController::class, 'store'])->name('nguoi-dung.store');
    });
    Route::middleware('permission:nguoi-dung,can_edit')->group(function () {
        Route::get('/nguoi-dung/{nguoi_dung}/edit', [UserController::class, 'edit'])->name('nguoi-dung.edit');
        Route::put('/nguoi-dung/{nguoi_dung}', [UserController::class, 'update'])->name('nguoi-dung.update');
    });
    Route::middleware('permission:nguoi-dung,can_delete')->group(function () {
        Route::delete('/nguoi-dung/{nguoi_dung}', [UserController::class, 'destroy'])->name('nguoi-dung.destroy');
    });

    // Phân quyền (Chỉ Admin)
    Route::middleware('permission:phan-quyen,can_view')->group(function () {
        Route::get('/phan-quyen', [PermissionController::class, 'index'])->name('phan-quyen.index');
        Route::get('/phan-quyen/{user}/permissions', [PermissionController::class, 'getUserPermissions'])->name('phan-quyen.get');
    });
    Route::middleware('permission:phan-quyen,can_edit')->group(function () {
        Route::post('/phan-quyen/{user}/permissions', [PermissionController::class, 'updateUserPermissions'])->name('phan-quyen.update');
    });
});

```

```php
<?php

namespace App\Http\Middleware;

use App\Models\Screen;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
            'userPermissions' => $user ? $this->getUserPermissions($user) : [],
            'menuScreens' => $user ? $this->getMenuScreens($user) : [],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Lấy permissions của user hiện tại
     */
    private function getUserPermissions($user)
    {
        if ($user->isAdmin()) {
            // Admin có tất cả quyền
            $screens = Screen::where('is_active', true)->get();
            $permissions = [];
            foreach ($screens as $screen) {
                $permissions[$screen->code] = [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                ];
            }
            return $permissions;
        }

        // User thường - lấy từ database
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $screen = $permission->screen;
            if ($screen) {
                $permissions[$screen->code] = [
                    'can_view' => $permission->can_view,
                    'can_create' => $permission->can_create,
                    'can_edit' => $permission->can_edit,
                    'can_delete' => $permission->can_delete,
                ];
            }
        }
        return $permissions;
    }

    /**
     * Lấy menu screens mà user có quyền xem
     */
    private function getMenuScreens($user)
    {
        $viewableScreenCodes = [];

        if ($user->isAdmin()) {
            // Admin xem được tất cả
            $viewableScreenCodes = Screen::where('is_active', true)
                ->pluck('code')
                ->toArray();
        } else {
            // User thường - chỉ những màn hình có quyền view
            $viewableScreenCodes = $user->permissions()
                ->where('can_view', true)
                ->with('screen')
                ->get()
                ->pluck('screen.code')
                ->filter()
                ->toArray();
        }

        // Lấy tree screens và filter theo quyền
        $screens = Screen::whereNull('parent_id')
            ->where('is_active', true)
            ->where('is_menu', true)
            ->orderBy('order')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->where('is_menu', true)
                    ->orderBy('order');
            }])
            ->get();

        return $this->filterScreensByPermission($screens, $viewableScreenCodes);
    }

    /**
     * Filter screens theo quyền của user
     */
    private function filterScreensByPermission($screens, $viewableScreenCodes)
    {
        $result = [];

        foreach ($screens as $screen) {
            $filteredChildren = [];
            
            if ($screen->children && $screen->children->count() > 0) {
                foreach ($screen->children as $child) {
                    if (in_array($child->code, $viewableScreenCodes)) {
                        $filteredChildren[] = [
                            'id' => $child->id,
                            'name' => $child->name,
                            'code' => $child->code,
                            'route' => $child->route,
                            'icon' => $child->icon,
                        ];
                    }
                }
            }

            // Chỉ thêm parent nếu có children hoặc parent có route và có quyền
            if (count($filteredChildren) > 0 || 
                ($screen->route && in_array($screen->code, $viewableScreenCodes))) {
                $result[] = [
                    'id' => $screen->id,
                    'name' => $screen->name,
                    'code' => $screen->code,
                    'route' => $screen->route,
                    'icon' => $screen->icon,
                    'children' => $filteredChildren,
                ];
            }
        }

        return $result;
    }
}

```

```javascriptreact
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

            return {
                key: screen.route || screen.code,
                icon: icon,
                label: screen.route ? <Link href={screen.route}>{screen.name}</Link> : screen.name,
            };
        });
    }, [menuScreens]);

    // Lấy default open keys
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

```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        
        // Seed dữ liệu
        $this->call([
            UserSeeder::class,
            ScreenSeeder::class,
            QLCSVCSeeder::class,
        ]);
    }
}

```

Bây giờ chạy migration, seeder và build assets:

Database chưa chạy, nhưng tôi sẽ build assets trước. Bạn cần khởi động MySQL/Laragon để chạy migration sau:

Hoàn thành! Dưới đây là tóm tắt hệ thống phân quyền đã tạo:

## ✅ Đã hoàn thành hệ thống Phân quyền theo màn hình

### 📁 Các file đã tạo mới:

| File | Mô tả |
|------|-------|
| `database/migrations/2025_12_14_000001_create_screens_table.php` | Bảng màn hình (đệ quy) |
| `database/migrations/2025_12_14_000002_create_user_permissions_table.php` | Bảng quyền user |
| `app/Models/Screen.php` | Model màn hình với đệ quy |
| `app/Models/UserPermission.php` | Model quyền user |
| `app/Http/Controllers/PermissionController.php` | Controller phân quyền |
| `app/Http/Controllers/UserController.php` | Controller quản lý user |
| `app/Http/Middleware/CheckPermission.php` | Middleware kiểm tra quyền |
| `database/seeders/ScreenSeeder.php` | Seeder màn hình mặc định |
| `resources/js/components/Permission/Index.jsx` | UI phân quyền |
| `resources/js/components/User/Index.jsx` | UI danh sách user |
| `resources/js/components/User/Create.jsx` | UI thêm user |
| `resources/js/components/User/Edit.jsx` | UI sửa user |

### 📁 Các file đã cập nhật:

| File | Thay đổi |
|------|----------|
| `app/Models/User.php` | Thêm methods kiểm tra quyền |
| `app/Http/Kernel.php` | Đăng ký middleware `permission` |
| `app/Http/Middleware/HandleInertiaRequests.php` | Share permissions & menu động |
| `routes/web.php` | Thêm routes + middleware permission |
| `resources/js/components/Layout/MainLayout.jsx` | Menu động theo quyền |
| `database/seeders/DatabaseSeeder.php` | Thêm ScreenSeeder |

### 🏗️ Cấu trúc Database:

```
┌─────────────────────────────────────────────────────────────┐
│  screens (Màn hình - Đệ quy)                                │
├─────────────────────────────────────────────────────────────┤
│  id | name | code | route | parent_id | order | is_active  │
│  ───┼──────┼──────┼───────┼───────────┼───────┼──────────── │
│  1  | Dashboard        | dashboard    | /     | NULL | 1   │
│  2  | QL Cơ sở hạ tầng | co-so-group  | NULL  | NULL | 2   │
│  3  | Danh sách cơ sở  | co-so        | /co-so| 2    | 1   │
│  ...                                                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  user_permissions (Quyền user)                              │
├─────────────────────────────────────────────────────────────┤
│  user_id | screen_id | can_view | can_create | can_edit | can_delete │
└─────────────────────────────────────────────────────────────┘
```

### 🎯 Tính năng:

- ✅ **Cấu trúc màn hình đệ quy** - Parent/Children
- ✅ **4 loại quyền**: Xem, Thêm, Sửa, Xóa
- ✅ **Checkbox hàng loạt** - Tích cả cột, cả dòng
- ✅ **Menu động** - Hiển thị theo quyền user
- ✅ **Middleware bảo vệ routes** - Kiểm tra quyền trước khi truy cập
- ✅ **Admin có tất cả quyền** mặc định
- ✅ **Quản lý người dùng** CRUD

---

## 🚀 Để hoàn tất, bạn cần chạy các lệnh sau:

Khi Laragon/MySQL đã chạy:

```bash
# 1. Chạy migration
php artisan migrate

# 2. Chạy seeder tạo màn hình
php artisan db:seed --class=ScreenSeeder

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
```

### 🔐 Menu mới sẽ có:

- **Dashboard**
- **QL Cơ sở hạ tầng** → Danh sách cơ sở
- **QL Khu nhà** → Danh sách khu nhà
- **QL Phòng** → Danh sách phòng
- **QL Thiết bị** → Danh sách thiết bị, Lịch sử bảo dưỡng
- **Quản lý Hệ thống** → Danh sách người dùng, Phân quyền

### 📋 Cách sử dụng:

1. Đăng nhập với admin (`admin@ctut.edu.vn`)
2. Vào **Quản lý Hệ thống** → **Phân quyền**
3. Chọn người dùng từ dropdown
4. Tích checkbox quyền cho từng màn hình
5. Nhấn **Lưu phân quyền**

User sẽ chỉ thấy menu và truy cập được những màn hình đã được phân quyền! 🎉

```plaintext
┌─────────────────────────────────────────────────────────────┐
│  screens (Màn hình - Đệ quy)                                │
├─────────────────────────────────────────────────────────────┤
│  id | name | code | route | parent_id | order | is_active  │
│  ───┼──────┼──────┼───────┼───────────┼───────┼──────────── │
│  1  | Dashboard        | dashboard    | /     | NULL | 1   │
│  2  | QL Cơ sở hạ tầng | co-so-group  | NULL  | NULL | 2   │
│  3  | Danh sách cơ sở  | co-so        | /co-so| 2    | 1   │
│  ...                                                        │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  user_permissions (Quyền user)                              │
├─────────────────────────────────────────────────────────────┤
│  user_id | screen_id | can_view | can_create | can_edit | can_delete │
└─────────────────────────────────────────────────────────────┘
```

```shellscript
# 1. Chạy migration
php artisan migrate

# 2. Chạy seeder tạo màn hình
php artisan db:seed --class=ScreenSeeder

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
```

---

**User**

@powershell (6-38) tôi chạy lệnh seeder giống bạn bị lỗi nè

---

**Cursor**

Lỗi này do foreign key constraint. Tôi sẽ sửa ScreenSeeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use Illuminate\Support\Facades\DB;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tắt foreign key checks để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('user_permissions')->truncate();
        DB::table('screens')->truncate();
        
        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Dashboard
        $dashboard = Screen::create([
            'name' => 'Dashboard',
            'code' => 'dashboard',
            'route' => '/',
            'icon' => 'DashboardOutlined',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 2. Quản lý Cơ sở hạ tầng
        $coSoGroup = Screen::create([
            'name' => 'QL Cơ sở hạ tầng',
            'code' => 'co-so-group',
            'route' => null,
            'icon' => 'BankOutlined',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách cơ sở',
            'code' => 'co-so',
            'route' => '/co-so',
            'icon' => null,
            'parent_id' => $coSoGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 3. Quản lý Khu nhà
        $khuNhaGroup = Screen::create([
            'name' => 'QL Khu nhà, Chức năng',
            'code' => 'khu-nha-group',
            'route' => null,
            'icon' => 'HomeOutlined',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách khu nhà',
            'code' => 'khu-nha',
            'route' => '/khu-nha',
            'icon' => null,
            'parent_id' => $khuNhaGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 4. Quản lý Phòng
        $phongGroup = Screen::create([
            'name' => 'QL Phòng',
            'code' => 'phong-group',
            'route' => null,
            'icon' => 'AppstoreOutlined',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách phòng',
            'code' => 'phong',
            'route' => '/phong',
            'icon' => null,
            'parent_id' => $phongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 5. Quản lý Thiết bị
        $thietBiGroup = Screen::create([
            'name' => 'QL Thiết bị',
            'code' => 'thiet-bi-group',
            'route' => null,
            'icon' => 'ToolOutlined',
            'parent_id' => null,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách thiết bị',
            'code' => 'thiet-bi',
            'route' => '/thiet-bi',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Lịch sử bảo dưỡng',
            'code' => 'lich-su-bao-duong',
            'route' => '/lich-su-bao-duong',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 6. Quản lý Hệ thống
        $heThongGroup = Screen::create([
            'name' => 'Quản lý Hệ thống',
            'code' => 'he-thong-group',
            'route' => null,
            'icon' => 'SettingOutlined',
            'parent_id' => null,
            'order' => 6,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách người dùng',
            'code' => 'nguoi-dung',
            'route' => '/nguoi-dung',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Phân quyền',
            'code' => 'phan-quyen',
            'route' => '/phan-quyen',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        $this->command->info('Đã tạo ' . Screen::count() . ' màn hình');
    }
}

```

Đã sửa! Bây giờ bạn chạy lại lệnh:

```bash
php artisan db:seed --class=ScreenSeeder
```

Tôi đã thêm `SET FOREIGN_KEY_CHECKS=0` để tắt kiểm tra foreign key trước khi xóa dữ liệu, sau đó bật lại.

```shellscript
php artisan db:seed --class=ScreenSeeder
```

---

**User**

bạn xem kỹ cấu trúc dự án tôi làm lại nhé chức năng phân quyền có lỗi rồi. thứ nhất tên chức năng con trong chức năng lớn bạn làm rất lộn  xộn. bên trong QL cơ sở có 1 danh sách cơ sở đúng rồi nhưng bên ngoài ngang hàng với QL cơ sở bạn lại làm 1 danh sách cơ sở nữa vô dụng gây ra 1 số xung đột. thứ 2 lỗi chức năng thêm mới sau khi kể cả cấp quyền thêm hoặc không đều báo lỗi như hình 

---

**Cursor**

Tôi hiểu vấn đề. Để tôi kiểm tra và sửa lại:

Tôi thấy 2 vấn đề:

1. **Thứ tự routes sai**: Route `/co-so/create` nằm sau `/co-so/{co_so}` nên Laravel nhầm "create" là ID
2. **Controller không có method `show()`** nhưng routes lại định nghĩa

Tôi sẽ sửa lại cả routes và ScreenSeeder:

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes - Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout - Auth only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== Quản lý Cơ sở ====================
    // Routes create phải đặt TRƯỚC routes có parameter {id}
    Route::middleware('permission:co-so,can_create')->group(function () {
        Route::get('/co-so/create', [CoSoController::class, 'create'])->name('co-so.create');
        Route::post('/co-so', [CoSoController::class, 'store'])->name('co-so.store');
    });
    Route::middleware('permission:co-so,can_view')->group(function () {
        Route::get('/co-so', [CoSoController::class, 'index'])->name('co-so.index');
    });
    Route::middleware('permission:co-so,can_edit')->group(function () {
        Route::get('/co-so/{co_so}/edit', [CoSoController::class, 'edit'])->name('co-so.edit');
        Route::put('/co-so/{co_so}', [CoSoController::class, 'update'])->name('co-so.update');
    });
    Route::middleware('permission:co-so,can_delete')->group(function () {
        Route::delete('/co-so/{co_so}', [CoSoController::class, 'destroy'])->name('co-so.destroy');
    });

    // ==================== Quản lý Khu nhà ====================
    Route::middleware('permission:khu-nha,can_create')->group(function () {
        Route::get('/khu-nha/create', [KhuNhaController::class, 'create'])->name('khu-nha.create');
        Route::post('/khu-nha', [KhuNhaController::class, 'store'])->name('khu-nha.store');
    });
    Route::middleware('permission:khu-nha,can_view')->group(function () {
        Route::get('/khu-nha', [KhuNhaController::class, 'index'])->name('khu-nha.index');
    });
    Route::middleware('permission:khu-nha,can_edit')->group(function () {
        Route::get('/khu-nha/{khu_nha}/edit', [KhuNhaController::class, 'edit'])->name('khu-nha.edit');
        Route::put('/khu-nha/{khu_nha}', [KhuNhaController::class, 'update'])->name('khu-nha.update');
    });
    Route::middleware('permission:khu-nha,can_delete')->group(function () {
        Route::delete('/khu-nha/{khu_nha}', [KhuNhaController::class, 'destroy'])->name('khu-nha.destroy');
    });

    // ==================== Quản lý Phòng ====================
    Route::middleware('permission:phong,can_create')->group(function () {
        Route::get('/phong/create', [PhongController::class, 'create'])->name('phong.create');
        Route::post('/phong', [PhongController::class, 'store'])->name('phong.store');
    });
    Route::middleware('permission:phong,can_view')->group(function () {
        Route::get('/phong', [PhongController::class, 'index'])->name('phong.index');
    });
    Route::middleware('permission:phong,can_edit')->group(function () {
        Route::get('/phong/{phong}/edit', [PhongController::class, 'edit'])->name('phong.edit');
        Route::put('/phong/{phong}', [PhongController::class, 'update'])->name('phong.update');
    });
    Route::middleware('permission:phong,can_delete')->group(function () {
        Route::delete('/phong/{phong}', [PhongController::class, 'destroy'])->name('phong.destroy');
    });

    // ==================== Quản lý Thiết bị ====================
    Route::middleware('permission:thiet-bi,can_create')->group(function () {
        Route::get('/thiet-bi/create', [ThietBiController::class, 'create'])->name('thiet-bi.create');
        Route::post('/thiet-bi', [ThietBiController::class, 'store'])->name('thiet-bi.store');
        Route::get('/thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    });
    Route::middleware('permission:thiet-bi,can_view')->group(function () {
        Route::get('/thiet-bi', [ThietBiController::class, 'index'])->name('thiet-bi.index');
        Route::get('/thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
    });
    Route::middleware('permission:thiet-bi,can_edit')->group(function () {
        Route::get('/thiet-bi/{thiet_bi}/edit', [ThietBiController::class, 'edit'])->name('thiet-bi.edit');
        Route::put('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'update'])->name('thiet-bi.update');
    });
    Route::middleware('permission:thiet-bi,can_delete')->group(function () {
        Route::delete('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'destroy'])->name('thiet-bi.destroy');
    });

    // ==================== Quản lý Lịch sử Bảo dưỡng ====================
    Route::middleware('permission:lich-su-bao-duong,can_create')->group(function () {
        Route::get('/lich-su-bao-duong/create', [LichSuBaoDuongController::class, 'create'])->name('lich-su-bao-duong.create');
        Route::post('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'store'])->name('lich-su-bao-duong.store');
    });
    Route::middleware('permission:lich-su-bao-duong,can_view')->group(function () {
        Route::get('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'index'])->name('lich-su-bao-duong.index');
        Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
    });
    Route::middleware('permission:lich-su-bao-duong,can_edit')->group(function () {
        Route::get('/lich-su-bao-duong/{lich_su_bao_duong}/edit', [LichSuBaoDuongController::class, 'edit'])->name('lich-su-bao-duong.edit');
        Route::put('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'update'])->name('lich-su-bao-duong.update');
    });
    Route::middleware('permission:lich-su-bao-duong,can_delete')->group(function () {
        Route::delete('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'destroy'])->name('lich-su-bao-duong.destroy');
    });

    // ==================== Quản lý Người dùng ====================
    Route::middleware('permission:nguoi-dung,can_create')->group(function () {
        Route::get('/nguoi-dung/create', [UserController::class, 'create'])->name('nguoi-dung.create');
        Route::post('/nguoi-dung', [UserController::class, 'store'])->name('nguoi-dung.store');
    });
    Route::middleware('permission:nguoi-dung,can_view')->group(function () {
        Route::get('/nguoi-dung', [UserController::class, 'index'])->name('nguoi-dung.index');
    });
    Route::middleware('permission:nguoi-dung,can_edit')->group(function () {
        Route::get('/nguoi-dung/{nguoi_dung}/edit', [UserController::class, 'edit'])->name('nguoi-dung.edit');
        Route::put('/nguoi-dung/{nguoi_dung}', [UserController::class, 'update'])->name('nguoi-dung.update');
    });
    Route::middleware('permission:nguoi-dung,can_delete')->group(function () {
        Route::delete('/nguoi-dung/{nguoi_dung}', [UserController::class, 'destroy'])->name('nguoi-dung.destroy');
    });

    // ==================== Phân quyền ====================
    Route::middleware('permission:phan-quyen,can_view')->group(function () {
        Route::get('/phan-quyen', [PermissionController::class, 'index'])->name('phan-quyen.index');
        Route::get('/phan-quyen/{user}/permissions', [PermissionController::class, 'getUserPermissions'])->name('phan-quyen.get');
    });
    Route::middleware('permission:phan-quyen,can_edit')->group(function () {
        Route::post('/phan-quyen/{user}/permissions', [PermissionController::class, 'updateUserPermissions'])->name('phan-quyen.update');
    });
});

```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use Illuminate\Support\Facades\DB;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tắt foreign key checks để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('user_permissions')->truncate();
        DB::table('screens')->truncate();
        
        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Dashboard
        Screen::create([
            'name' => 'Dashboard',
            'code' => 'dashboard',
            'route' => '/',
            'icon' => 'DashboardOutlined',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 2. Quản lý Cơ sở (màn hình đơn, không có children)
        Screen::create([
            'name' => 'Quản lý Cơ sở',
            'code' => 'co-so',
            'route' => '/co-so',
            'icon' => 'BankOutlined',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 3. Quản lý Khu nhà (màn hình đơn, không có children)
        Screen::create([
            'name' => 'Quản lý Khu nhà',
            'code' => 'khu-nha',
            'route' => '/khu-nha',
            'icon' => 'HomeOutlined',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 4. Quản lý Phòng (màn hình đơn, không có children)
        Screen::create([
            'name' => 'Quản lý Phòng',
            'code' => 'phong',
            'route' => '/phong',
            'icon' => 'AppstoreOutlined',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 5. Quản lý Thiết bị (có 2 children: Danh sách thiết bị, Lịch sử bảo dưỡng)
        $thietBiGroup = Screen::create([
            'name' => 'Quản lý Thiết bị',
            'code' => 'thiet-bi-group',
            'route' => null,
            'icon' => 'ToolOutlined',
            'parent_id' => null,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách thiết bị',
            'code' => 'thiet-bi',
            'route' => '/thiet-bi',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Lịch sử bảo dưỡng',
            'code' => 'lich-su-bao-duong',
            'route' => '/lich-su-bao-duong',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 6. Quản lý Hệ thống (có 2 children: Người dùng, Phân quyền)
        $heThongGroup = Screen::create([
            'name' => 'Quản lý Hệ thống',
            'code' => 'he-thong-group',
            'route' => null,
            'icon' => 'SettingOutlined',
            'parent_id' => null,
            'order' => 6,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Quản lý Người dùng',
            'code' => 'nguoi-dung',
            'route' => '/nguoi-dung',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Phân quyền',
            'code' => 'phan-quyen',
            'route' => '/phan-quyen',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        $this->command->info('Đã tạo ' . Screen::count() . ' màn hình:');
        $this->command->info('- Dashboard');
        $this->command->info('- Quản lý Cơ sở');
        $this->command->info('- Quản lý Khu nhà');
        $this->command->info('- Quản lý Phòng');
        $this->command->info('- Quản lý Thiết bị (Danh sách thiết bị, Lịch sử bảo dưỡng)');
        $this->command->info('- Quản lý Hệ thống (Người dùng, Phân quyền)');
    }
}

```

```php
<?php

namespace App\Http\Middleware;

use App\Models\Screen;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ] : null,
            ],
            'userPermissions' => $user ? $this->getUserPermissions($user) : [],
            'menuScreens' => $user ? $this->getMenuScreens($user) : [],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    /**
     * Lấy permissions của user hiện tại
     */
    private function getUserPermissions($user)
    {
        if ($user->isAdmin()) {
            // Admin có tất cả quyền
            $screens = Screen::where('is_active', true)->get();
            $permissions = [];
            foreach ($screens as $screen) {
                $permissions[$screen->code] = [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                ];
            }
            return $permissions;
        }

        // User thường - lấy từ database
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $screen = $permission->screen;
            if ($screen) {
                $permissions[$screen->code] = [
                    'can_view' => $permission->can_view,
                    'can_create' => $permission->can_create,
                    'can_edit' => $permission->can_edit,
                    'can_delete' => $permission->can_delete,
                ];
            }
        }
        return $permissions;
    }

    /**
     * Lấy menu screens mà user có quyền xem
     */
    private function getMenuScreens($user)
    {
        $viewableScreenCodes = [];

        if ($user->isAdmin()) {
            // Admin xem được tất cả
            $viewableScreenCodes = Screen::where('is_active', true)
                ->pluck('code')
                ->toArray();
        } else {
            // User thường - chỉ những màn hình có quyền view
            $viewableScreenCodes = $user->permissions()
                ->where('can_view', true)
                ->with('screen')
                ->get()
                ->pluck('screen.code')
                ->filter()
                ->toArray();
        }

        // Lấy tree screens và filter theo quyền
        $screens = Screen::whereNull('parent_id')
            ->where('is_active', true)
            ->where('is_menu', true)
            ->orderBy('order')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->where('is_menu', true)
                    ->orderBy('order');
            }])
            ->get();

        return $this->filterScreensByPermission($screens, $viewableScreenCodes);
    }

    /**
     * Filter screens theo quyền của user
     */
    private function filterScreensByPermission($screens, $viewableScreenCodes)
    {
        $result = [];

        foreach ($screens as $screen) {
            // Nếu màn hình có children
            if ($screen->children && $screen->children->count() > 0) {
                $filteredChildren = [];
                
                foreach ($screen->children as $child) {
                    if (in_array($child->code, $viewableScreenCodes)) {
                        $filteredChildren[] = [
                            'id' => $child->id,
                            'name' => $child->name,
                            'code' => $child->code,
                            'route' => $child->route,
                            'icon' => $child->icon,
                        ];
                    }
                }

                // Chỉ thêm parent nếu có ít nhất 1 child có quyền
                if (count($filteredChildren) > 0) {
                    $result[] = [
                        'id' => $screen->id,
                        'name' => $screen->name,
                        'code' => $screen->code,
                        'route' => $screen->route,
                        'icon' => $screen->icon,
                        'children' => $filteredChildren,
                    ];
                }
            } else {
                // Màn hình đơn (không có children) - kiểm tra quyền trực tiếp
                if (in_array($screen->code, $viewableScreenCodes)) {
                    $result[] = [
                        'id' => $screen->id,
                        'name' => $screen->name,
                        'code' => $screen->code,
                        'route' => $screen->route,
                        'icon' => $screen->icon,
                        'children' => [],
                    ];
                }
            }
        }

        return $result;
    }
}

```

```javascriptreact
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

```

Bây giờ chạy lại seeder và build assets:

Đã sửa xong! Tóm tắt các thay đổi:

## ✅ Đã sửa các lỗi:

### 1. **Lỗi routes** - Route `/co-so/create` bị nhầm với `/co-so/{id}`
- Đặt routes `/create` TRƯỚC routes có parameter `{id}`
- Xóa route `show` (controller không có method này)

### 2. **Cấu trúc menu đơn giản hơn:**

```
Cũ (lộn xộn):                      Mới (gọn gàng):
├── QL Cơ sở hạ tầng               ├── Dashboard
│   └── Danh sách cơ sở            ├── Quản lý Cơ sở
├── QL Khu nhà                     ├── Quản lý Khu nhà
│   └── Danh sách khu nhà          ├── Quản lý Phòng
...                                ├── Quản lý Thiết bị
                                   │   ├── Danh sách thiết bị
                                   │   └── Lịch sử bảo dưỡng
                                   └── Quản lý Hệ thống
                                       ├── Quản lý Người dùng
                                       └── Phân quyền
```

### 3. **10 màn hình để phân quyền:**

| STT | Màn hình | Code | Route |
|-----|----------|------|-------|
| 1 | Dashboard | `dashboard` | `/` |
| 2 | Quản lý Cơ sở | `co-so` | `/co-so` |
| 3 | Quản lý Khu nhà | `khu-nha` | `/khu-nha` |
| 4 | Quản lý Phòng | `phong` | `/phong` |
| 5 | Quản lý Thiết bị (group) | `thiet-bi-group` | - |
| 6 | Danh sách thiết bị | `thiet-bi` | `/thiet-bi` |
| 7 | Lịch sử bảo dưỡng | `lich-su-bao-duong` | `/lich-su-bao-duong` |
| 8 | Quản lý Hệ thống (group) | `he-thong-group` | - |
| 9 | Quản lý Người dùng | `nguoi-dung` | `/nguoi-dung` |
| 10 | Phân quyền | `phan-quyen` | `/phan-quyen` |

Bây giờ bạn thử lại chức năng thêm mới cơ sở xem còn lỗi không nhé!

```plaintext
Cũ (lộn xộn):                      Mới (gọn gàng):
├── QL Cơ sở hạ tầng               ├── Dashboard
│   └── Danh sách cơ sở            ├── Quản lý Cơ sở
├── QL Khu nhà                     ├── Quản lý Khu nhà
│   └── Danh sách khu nhà          ├── Quản lý Phòng
...                                ├── Quản lý Thiết bị
                                   │   ├── Danh sách thiết bị
                                   │   └── Lịch sử bảo dưỡng
                                   └── Quản lý Hệ thống
                                       ├── Quản lý Người dùng
                                       └── Phân quyền
```

---

**User**

ok phần thêm mới đã được fix nhưng giao diện phân quyền như hình chưa được fix. ví dụ như chức năng quản lý hệ thống có 2 chức năng nhỏ ở trong là quản lý người dùng và phân quyền thì tôi muốn là khi bấm vào mở rộng chức năng quản lý hệ thống mới hiện 2 chức năng đó và tất cả các chức năng còn lại cũng làm tương tự như vậy

---

**Cursor**

Tôi hiểu! Bạn muốn giao diện phân quyền có thể mở rộng/thu gọn (expand/collapse) các nhóm chức năng. Để tôi sửa lại:

```javascriptreact
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

    // Xử lý thay đổi checkbox
    const handlePermissionChange = (screenId, permissionType, checked) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            if (!newPermissions[screenId]) {
                newPermissions[screenId] = {
                    can_view: false,
                    can_create: false,
                    can_edit: false,
                    can_delete: false,
                };
            }
            newPermissions[screenId][permissionType] = checked;

            // Nếu bỏ tích can_view, tự động bỏ các quyền khác
            if (permissionType === 'can_view' && !checked) {
                newPermissions[screenId].can_create = false;
                newPermissions[screenId].can_edit = false;
                newPermissions[screenId].can_delete = false;
            }

            // Nếu tích quyền khác, tự động tích can_view
            if (permissionType !== 'can_view' && checked) {
                newPermissions[screenId].can_view = true;
            }

            return newPermissions;
        });
    };

    // Xử lý tích/bỏ tích cả dòng
    const handleRowCheckAll = (screenId, checked, record) => {
        setPermissions((prev) => {
            const newPermissions = { ...prev };
            
            // Cập nhật cho chính nó
            newPermissions[screenId] = {
                can_view: checked,
                can_create: checked,
                can_edit: checked,
                can_delete: checked,
            };

            // Nếu là parent, cập nhật cho tất cả children
            if (record.children && record.children.length > 0) {
                record.children.forEach((child) => {
                    newPermissions[child.id] = {
                        can_view: checked,
                        can_create: checked,
                        can_edit: checked,
                        can_delete: checked,
                    };
                });
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

    // Kiểm tra row có tích hết không
    const isRowAllChecked = (record) => {
        const perms = permissions[record.id] || {};
        let allChecked = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        
        // Nếu là parent, kiểm tra cả children
        if (record.children && record.children.length > 0) {
            record.children.forEach((child) => {
                const childPerms = permissions[child.id] || {};
                if (!(childPerms.can_view && childPerms.can_create && childPerms.can_edit && childPerms.can_delete)) {
                    allChecked = false;
                }
            });
        }
        
        return allChecked;
    };

    const isRowIndeterminate = (record) => {
        const ids = [record.id];
        if (record.children) {
            record.children.forEach((child) => ids.push(child.id));
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        ids.forEach((id) => {
            const perms = permissions[id] || {};
            if (perms.can_view || perms.can_create || perms.can_edit || perms.can_delete) {
                hasChecked = true;
            }
            if (!perms.can_view || !perms.can_create || !perms.can_edit || !perms.can_delete) {
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
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
                    checked={permissions[record.id]?.can_view || false}
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
                    checked={permissions[record.id]?.can_create || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_create', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
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
                    checked={permissions[record.id]?.can_edit || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_edit', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
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
                    checked={permissions[record.id]?.can_delete || false}
                    onChange={(e) => handlePermissionChange(record.id, 'can_delete', e.target.checked)}
                    disabled={!selectedUserId || !permissions[record.id]?.can_view}
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
                                        <Text type="secondary">Lưu ý: Admin có tất cả quyền mặc định, không cần phân quyền</Text>
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

```

Đã sửa xong! Giao diện phân quyền mới có các tính năng:

## ✅ Cải tiến giao diện phân quyền:

### 1. **Mở rộng/Thu gọn nhóm chức năng**
- Các nhóm có icon `+`/`-` để mở rộng/thu gọn
- Click vào icon sẽ hiển thị/ẩn các chức năng con bên trong

### 2. **Nút điều khiển nhanh**
- **"Mở rộng tất cả"**: Hiển thị tất cả chức năng con
- **"Thu gọn tất cả"**: Ẩn tất cả chức năng con

### 3. **Tích checkbox thông minh**
- Tích "Tất cả" ở parent → Tự động tích cho tất cả children
- Bỏ tích "Xem" → Tự động bỏ các quyền Thêm/Sửa/Xóa
- Tích Thêm/Sửa/Xóa → Tự động tích "Xem"

### Cấu trúc hiển thị:

```
📊 Dashboard                        [route: /]
📁 Quản lý Cơ sở                    [route: /co-so]
📁 Quản lý Khu nhà                  [route: /khu-nha]
📁 Quản lý Phòng                    [route: /phong]
▶ Quản lý Thiết bị                  (click để mở rộng)
   ├── Danh sách thiết bị           [route: /thiet-bi]
   └── Lịch sử bảo dưỡng            [route: /lich-su-bao-duong]
▶ Quản lý Hệ thống                  (click để mở rộng)
   ├── Quản lý Người dùng           [route: /nguoi-dung]
   └── Phân quyền                   [route: /phan-quyen]
```

Bây giờ bạn refresh trang phân quyền và thử xem nhé!

```plaintext
📊 Dashboard                        [route: /]
📁 Quản lý Cơ sở                    [route: /co-so]
📁 Quản lý Khu nhà                  [route: /khu-nha]
📁 Quản lý Phòng                    [route: /phong]
▶ Quản lý Thiết bị                  (click để mở rộng)
   ├── Danh sách thiết bị           [route: /thiet-bi]
   └── Lịch sử bảo dưỡng            [route: /lich-su-bao-duong]
▶ Quản lý Hệ thống                  (click để mở rộng)
   ├── Quản lý Người dùng           [route: /nguoi-dung]
   └── Phân quyền                   [route: /phan-quyen]
```

---

**User**

cái phần quản lý cơ sở quản lí phòng với quản lý khu nhà lúc nãy bạn làm lại sai của tôi rồi trả lại lúc đầu đi nó mất cái collapse với expand những chức năng đó rồi

---

**Cursor**

Tôi hiểu rồi! Tôi cần khôi phục lại cấu trúc parent-child cho tất cả các chức năng. Để tôi sửa lại ScreenSeeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Screen;
use Illuminate\Support\Facades\DB;

class ScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tắt foreign key checks để có thể truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Xóa dữ liệu cũ
        DB::table('user_permissions')->truncate();
        DB::table('screens')->truncate();
        
        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Dashboard
        Screen::create([
            'name' => 'Dashboard',
            'code' => 'dashboard',
            'route' => '/',
            'icon' => 'DashboardOutlined',
            'parent_id' => null,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 2. Quản lý Cơ sở hạ tầng
        $coSoGroup = Screen::create([
            'name' => 'QL Cơ sở hạ tầng',
            'code' => 'co-so-group',
            'route' => null,
            'icon' => 'BankOutlined',
            'parent_id' => null,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách cơ sở',
            'code' => 'co-so',
            'route' => '/co-so',
            'icon' => null,
            'parent_id' => $coSoGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 3. Quản lý Khu nhà
        $khuNhaGroup = Screen::create([
            'name' => 'QL Khu nhà, Chức năng',
            'code' => 'khu-nha-group',
            'route' => null,
            'icon' => 'HomeOutlined',
            'parent_id' => null,
            'order' => 3,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách khu nhà',
            'code' => 'khu-nha',
            'route' => '/khu-nha',
            'icon' => null,
            'parent_id' => $khuNhaGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 4. Quản lý Phòng
        $phongGroup = Screen::create([
            'name' => 'QL Phòng',
            'code' => 'phong-group',
            'route' => null,
            'icon' => 'AppstoreOutlined',
            'parent_id' => null,
            'order' => 4,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách phòng',
            'code' => 'phong',
            'route' => '/phong',
            'icon' => null,
            'parent_id' => $phongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 5. Quản lý Thiết bị
        $thietBiGroup = Screen::create([
            'name' => 'QL Thiết bị',
            'code' => 'thiet-bi-group',
            'route' => null,
            'icon' => 'ToolOutlined',
            'parent_id' => null,
            'order' => 5,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Danh sách thiết bị',
            'code' => 'thiet-bi',
            'route' => '/thiet-bi',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Lịch sử bảo dưỡng',
            'code' => 'lich-su-bao-duong',
            'route' => '/lich-su-bao-duong',
            'icon' => null,
            'parent_id' => $thietBiGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        // 6. Quản lý Hệ thống
        $heThongGroup = Screen::create([
            'name' => 'Quản lý Hệ thống',
            'code' => 'he-thong-group',
            'route' => null,
            'icon' => 'SettingOutlined',
            'parent_id' => null,
            'order' => 6,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Quản lý Người dùng',
            'code' => 'nguoi-dung',
            'route' => '/nguoi-dung',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 1,
            'is_active' => true,
            'is_menu' => true,
        ]);

        Screen::create([
            'name' => 'Phân quyền',
            'code' => 'phan-quyen',
            'route' => '/phan-quyen',
            'icon' => null,
            'parent_id' => $heThongGroup->id,
            'order' => 2,
            'is_active' => true,
            'is_menu' => true,
        ]);

        $this->command->info('Đã tạo ' . Screen::count() . ' màn hình');
    }
}

```

Đã khôi phục lại cấu trúc với 13 màn hình. Bây giờ cấu trúc là:

```
▶ Dashboard
▶ QL Cơ sở hạ tầng
   └── Danh sách cơ sở
▶ QL Khu nhà, Chức năng
   └── Danh sách khu nhà
▶ QL Phòng
   └── Danh sách phòng
▶ QL Thiết bị
   ├── Danh sách thiết bị
   └── Lịch sử bảo dưỡng
▶ Quản lý Hệ thống
   ├── Quản lý Người dùng
   └── Phân quyền
```

Bây giờ bạn refresh trang phân quyền và thử xem - tất cả các nhóm đều có thể expand/collapse!

```plaintext
▶ Dashboard
▶ QL Cơ sở hạ tầng
   └── Danh sách cơ sở
▶ QL Khu nhà, Chức năng
   └── Danh sách khu nhà
▶ QL Phòng
   └── Danh sách phòng
▶ QL Thiết bị
   ├── Danh sách thiết bị
   └── Lịch sử bảo dưỡng
▶ Quản lý Hệ thống
   ├── Quản lý Người dùng
   └── Phân quyền
```

---

**User**

bạn ơi như ảnh thì phân quyền xem cho cả 1 chức năng lớn là quản lý thiết bị luôn bao gồm 2 cái bên trong nhưng không khi tôi tick như hình và kiểm tra thì không có gì trừ khi giờ tôi phải tích thêm 1 chức năng bên trong nó mới hiển thị thì sai logic rồi

---

**Cursor**

Bạn đúng rồi! Khi tích quyền cho parent thì phải tự động tích cho tất cả children. Để tôi sửa lại logic:

```javascriptreact
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

            // Nếu là child và đang tích -> cũng tích cho parent
            if (screen && screen.parentId && checked) {
                setPermission(screen.parentId, permissionType, true);
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

            // Nếu là child và đang tích -> cũng tích cho parent
            if (record.parentId && checked) {
                setAllPermissions(record.parentId, true);
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
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        return idsToCheck.every((id) => {
            const perms = permissions[id] || {};
            return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        });
    };

    const isRowIndeterminate = (record) => {
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        idsToCheck.forEach((id) => {
            const perms = permissions[id] || {};
            const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
            
            if (allPerms) hasChecked = true;
            if (noPerms) hasUnchecked = true;
            if (!allPerms && !noPerms) {
                hasChecked = true;
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
    };

    // Kiểm tra permission của 1 cell có checked không (bao gồm logic parent-child)
    const isCellChecked = (record, permissionType) => {
        return permissions[record.id]?.[permissionType] || false;
    };

    // Kiểm tra cell có indeterminate không (chỉ cho parent)
    const isCellIndeterminate = (record, permissionType) => {
        if (!record.isParent || !record.childIds) return false;
        
        const parentChecked = permissions[record.id]?.[permissionType] || false;
        const childrenChecked = record.childIds.filter(
            (id) => permissions[id]?.[permissionType]
        ).length;
        
        // Indeterminate khi parent checked nhưng không phải tất cả children checked
        // hoặc có một số children checked
        if (parentChecked && childrenChecked < record.childIds.length) return true;
        if (!parentChecked && childrenChecked > 0 && childrenChecked < record.childIds.length) return true;
        
        return false;
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

```

Tôi đã sửa lại logic phân quyền. Giờ khi bạn tích vào checkbox của **nhóm chức năng lớn** (như "QL Thiết bị"), nó sẽ **tự động tích cho tất cả chức năng con** bên trong.

**Các thay đổi logic:**

1. **Cascade xuống children**: Khi tích/bỏ tích quyền cho parent → tự động áp dụng cho tất cả children
2. **Cascade lên parent**: Khi tích quyền cho child → tự động tích cho parent (vì phải có quyền parent mới vào được child)
3. **Checkbox indeterminate**: Hiển thị trạng thái "-" (một phần) khi chỉ một số children được tích
4. **Tích "Tất cả" ở hàng**: Sẽ tích tất cả quyền cho cả parent lẫn children

Bạn refresh lại trang phân quyền và thử tích vào "Xem" cho "QL Thiết bị" - lúc này "Danh sách thiết bị" và "Lịch sử bảo dưỡng" cũng sẽ được tự động tích theo.

---

**User**

ok bạn làm tốt rồi nhưng còn 1 chút hở đây bạn fix lại nhé là khi tôi tích lần lượt 2 children xong bỏ tích cuối cùng là k chọn nhưng ô ở parent vẫn hiện ô vuông

---

**Cursor**

Tôi hiểu rồi! Khi bỏ tích hết tất cả children thì parent cũng phải tự động bỏ tích. Để tôi fix:

Tôi thấy vấn đề rồi. Khi bỏ tích hết children, cần tự động bỏ tích parent. Và logic indeterminate cũng cần sửa lại:

```javascriptreact
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

            // Nếu là child và đang tích -> cũng tích cho parent
            if (record.parentId && checked) {
                setAllPermissions(record.parentId, true);
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
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        return idsToCheck.every((id) => {
            const perms = permissions[id] || {};
            return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        });
    };

    const isRowIndeterminate = (record) => {
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        idsToCheck.forEach((id) => {
            const perms = permissions[id] || {};
            const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
            
            if (allPerms) hasChecked = true;
            if (noPerms) hasUnchecked = true;
            if (!allPerms && !noPerms) {
                hasChecked = true;
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
    };

    // Kiểm tra permission của 1 cell có checked không (bao gồm logic parent-child)
    const isCellChecked = (record, permissionType) => {
        return permissions[record.id]?.[permissionType] || false;
    };

    // Kiểm tra cell có indeterminate không (chỉ cho parent)
    const isCellIndeterminate = (record, permissionType) => {
        if (!record.isParent || !record.childIds) return false;
        
        const parentChecked = permissions[record.id]?.[permissionType] || false;
        const childrenChecked = record.childIds.filter(
            (id) => permissions[id]?.[permissionType]
        ).length;
        
        // Indeterminate khi parent checked nhưng không phải tất cả children checked
        // hoặc có một số children checked
        if (parentChecked && childrenChecked < record.childIds.length) return true;
        if (!parentChecked && childrenChecked > 0 && childrenChecked < record.childIds.length) return true;
        
        return false;
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

```

```javascriptreact
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
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        return idsToCheck.every((id) => {
            const perms = permissions[id] || {};
            return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        });
    };

    const isRowIndeterminate = (record) => {
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        idsToCheck.forEach((id) => {
            const perms = permissions[id] || {};
            const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
            
            if (allPerms) hasChecked = true;
            if (noPerms) hasUnchecked = true;
            if (!allPerms && !noPerms) {
                hasChecked = true;
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
    };

    // Kiểm tra permission của 1 cell có checked không (bao gồm logic parent-child)
    const isCellChecked = (record, permissionType) => {
        return permissions[record.id]?.[permissionType] || false;
    };

    // Kiểm tra cell có indeterminate không (chỉ cho parent)
    const isCellIndeterminate = (record, permissionType) => {
        if (!record.isParent || !record.childIds) return false;
        
        const parentChecked = permissions[record.id]?.[permissionType] || false;
        const childrenChecked = record.childIds.filter(
            (id) => permissions[id]?.[permissionType]
        ).length;
        
        // Indeterminate khi parent checked nhưng không phải tất cả children checked
        // hoặc có một số children checked
        if (parentChecked && childrenChecked < record.childIds.length) return true;
        if (!parentChecked && childrenChecked > 0 && childrenChecked < record.childIds.length) return true;
        
        return false;
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

```

```javascriptreact
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
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        return idsToCheck.every((id) => {
            const perms = permissions[id] || {};
            return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        });
    };

    const isRowIndeterminate = (record) => {
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        idsToCheck.forEach((id) => {
            const perms = permissions[id] || {};
            const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
            
            if (allPerms) hasChecked = true;
            if (noPerms) hasUnchecked = true;
            if (!allPerms && !noPerms) {
                hasChecked = true;
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
    };

    // Kiểm tra permission của 1 cell có checked không (bao gồm logic parent-child)
    const isCellChecked = (record, permissionType) => {
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

```

Tôi cũng cần sửa lại logic `isCellChecked` cho parent để nó hiển thị đúng khi tất cả children được tích:

```javascriptreact
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
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        return idsToCheck.every((id) => {
            const perms = permissions[id] || {};
            return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
        });
    };

    const isRowIndeterminate = (record) => {
        const idsToCheck = [record.id];
        if (record.isParent && record.childIds) {
            idsToCheck.push(...record.childIds);
        }
        
        let hasChecked = false;
        let hasUnchecked = false;
        
        idsToCheck.forEach((id) => {
            const perms = permissions[id] || {};
            const allPerms = perms.can_view && perms.can_create && perms.can_edit && perms.can_delete;
            const noPerms = !perms.can_view && !perms.can_create && !perms.can_edit && !perms.can_delete;
            
            if (allPerms) hasChecked = true;
            if (noPerms) hasUnchecked = true;
            if (!allPerms && !noPerms) {
                hasChecked = true;
                hasUnchecked = true;
            }
        });
        
        return hasChecked && hasUnchecked;
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

```

```javascriptreact
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

```

Đã fix! Giờ logic sẽ hoạt động như sau:

**Checkbox của parent (nhóm chức năng):**
- ✓ **Checked**: Khi TẤT CẢ children đều được tích
- **-** **Indeterminate**: Khi MỘT SỐ children được tích (không phải tất cả)
- □ **Unchecked**: Khi KHÔNG CÓ child nào được tích

**Khi thao tác:**
- Tích parent → tích TẤT CẢ children
- Bỏ tích parent → bỏ tích TẤT CẢ children
- Tích child → tích parent (nếu chưa tích)
- Bỏ tích hết children → tự động bỏ tích parent

Bạn refresh lại trang và thử:
1. Tích lần lượt 2 children của "QL Thiết bị" → parent hiện ✓
2. Bỏ tích 1 child → parent hiện **-** (indeterminate)
3. Bỏ tích child cuối cùng → parent hiện □ (unchecked)

---

**User**

bạn ơi chổ này tôi thấy chưa đúng logic lắm admin mà có thể thay đổi vai trò cho chính mình thì hơi ảo nếu sau khi thay đổi vai trò cho mình thành user xong thì khi load lại trang lỗi 403 à bạn có sáng kiến gì đúng logic và giống những hệ thống khác đang vận hành thành công không

---

**Cursor**

Bạn nói rất đúng! Đây là vấn đề bảo mật quan trọng. Các hệ thống thực tế thường áp dụng các quy tắc sau:

1. **Không cho phép admin tự thay đổi vai trò của chính mình**
2. **Không cho phép admin tự xóa chính mình**
3. **Phải có ít nhất 1 admin trong hệ thống** (không cho xóa/hạ cấp admin cuối cùng)

Để tôi xem code hiện tại và fix:

Tôi sẽ fix cả backend và frontend để đảm bảo logic đúng:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Hiển thị form tạo người dùng mới
     */
    public function create()
    {
        return Inertia::render('User/Create');
    }

    /**
     * Lưu người dùng mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('nguoi-dung.index')->with('success', 'Thêm người dùng thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa người dùng
     */
    public function edit(User $nguoi_dung)
    {
        $isSelf = $nguoi_dung->id === auth()->id();
        $adminCount = User::where('role', 'admin')->count();
        
        return Inertia::render('User/Edit', [
            'user' => $nguoi_dung,
            'isSelf' => $isSelf,
            'isLastAdmin' => $nguoi_dung->role === 'admin' && $adminCount <= 1,
        ]);
    }

    /**
     * Cập nhật người dùng
     */
    public function update(Request $request, User $nguoi_dung)
    {
        $isSelf = $nguoi_dung->id === auth()->id();
        $adminCount = User::where('role', 'admin')->count();
        $isLastAdmin = $nguoi_dung->role === 'admin' && $adminCount <= 1;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($nguoi_dung->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Vui lòng nhập tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        // Không cho phép admin tự thay đổi vai trò của chính mình
        if ($isSelf && $request->role !== $nguoi_dung->role) {
            return back()->with('error', 'Không thể thay đổi vai trò của chính mình!');
        }

        // Không cho hạ cấp admin cuối cùng
        if ($isLastAdmin && $request->role !== 'admin') {
            return back()->with('error', 'Không thể hạ cấp admin cuối cùng trong hệ thống!');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Chỉ cập nhật role nếu không phải chính mình
        if (!$isSelf) {
            $data['role'] = $request->role;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $nguoi_dung->update($data);

        return redirect()->route('nguoi-dung.index')->with('success', 'Cập nhật người dùng thành công!');
    }

    /**
     * Xóa người dùng
     */
    public function destroy(User $nguoi_dung)
    {
        // Không cho xóa chính mình
        if ($nguoi_dung->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính mình!');
        }

        // Không cho xóa admin cuối cùng
        if ($nguoi_dung->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Không thể xóa admin cuối cùng trong hệ thống!');
            }
        }

        $nguoi_dung->delete();

        return redirect()->route('nguoi-dung.index')->with('success', 'Xóa người dùng thành công!');
    }
}



```

```javascriptreact
import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Form,
    Input,
    Select,
    Button,
    Space,
    Typography,
    Alert,
    Tooltip,
} from 'antd';
import { SaveOutlined, ArrowLeftOutlined, LockOutlined } from '@ant-design/icons';

const { Title, Text } = Typography;
const { Option } = Select;

const UserEdit = ({ user, isSelf = false, isLastAdmin = false }) => {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        role: user.role || 'user',
    });

    const handleSubmit = () => {
        put(`/nguoi-dung/${user.id}`);
    };

    // Không cho thay đổi role nếu:
    // 1. Đang sửa chính mình
    // 2. Là admin cuối cùng trong hệ thống
    const canChangeRole = !isSelf && !isLastAdmin;

    const getRoleDisabledReason = () => {
        if (isSelf) {
            return 'Không thể thay đổi vai trò của chính mình';
        }
        if (isLastAdmin) {
            return 'Không thể thay đổi vai trò của admin cuối cùng trong hệ thống';
        }
        return '';
    };

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>
                        Chỉnh sửa người dùng
                        {isSelf && <Text type="secondary" style={{ fontSize: 14, marginLeft: 8 }}>(Tài khoản của bạn)</Text>}
                    </Title>
                    <Link href="/nguoi-dung">
                        <Button icon={<ArrowLeftOutlined />}>Quay lại</Button>
                    </Link>
                </div>

                <Card style={{ maxWidth: 600 }}>
                    {isSelf && (
                        <Alert
                            message="Bạn đang chỉnh sửa tài khoản của chính mình"
                            description="Bạn có thể thay đổi thông tin cá nhân và mật khẩu, nhưng không thể thay đổi vai trò của mình."
                            type="warning"
                            showIcon
                            style={{ marginBottom: 24 }}
                        />
                    )}

                    {isLastAdmin && !isSelf && (
                        <Alert
                            message="Đây là admin cuối cùng trong hệ thống"
                            description="Không thể thay đổi vai trò của admin này. Hệ thống cần ít nhất 1 admin."
                            type="warning"
                            showIcon
                            style={{ marginBottom: 24 }}
                        />
                    )}

                    <Form layout="vertical" onFinish={handleSubmit}>
                        <Form.Item
                            label="Họ và tên"
                            validateStatus={errors.name ? 'error' : ''}
                            help={errors.name}
                            required
                        >
                            <Input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                placeholder="Nhập họ và tên"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Email"
                            validateStatus={errors.email ? 'error' : ''}
                            help={errors.email}
                            required
                        >
                            <Input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="Nhập email"
                            />
                        </Form.Item>

                        <Alert
                            message="Để trống nếu không muốn thay đổi mật khẩu"
                            type="info"
                            showIcon
                            style={{ marginBottom: 16 }}
                        />

                        <Form.Item
                            label="Mật khẩu mới"
                            validateStatus={errors.password ? 'error' : ''}
                            help={errors.password}
                        >
                            <Input.Password
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Nhập mật khẩu mới (để trống nếu không đổi)"
                            />
                        </Form.Item>

                        <Form.Item
                            label="Xác nhận mật khẩu mới"
                            validateStatus={errors.password_confirmation ? 'error' : ''}
                            help={errors.password_confirmation}
                        >
                            <Input.Password
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                placeholder="Nhập lại mật khẩu mới"
                            />
                        </Form.Item>

                        <Form.Item
                            label={
                                <Space>
                                    <span>Vai trò</span>
                                    {!canChangeRole && (
                                        <Tooltip title={getRoleDisabledReason()}>
                                            <LockOutlined style={{ color: '#faad14' }} />
                                        </Tooltip>
                                    )}
                                </Space>
                            }
                            validateStatus={errors.role ? 'error' : ''}
                            help={errors.role || (!canChangeRole ? getRoleDisabledReason() : '')}
                            required
                        >
                            <Select
                                value={data.role}
                                onChange={(value) => setData('role', value)}
                                disabled={!canChangeRole}
                            >
                                <Option value="user">Người dùng</Option>
                                <Option value="admin">Quản trị viên</Option>
                            </Select>
                        </Form.Item>

                        <Form.Item>
                            <Space>
                                <Button
                                    type="primary"
                                    htmlType="submit"
                                    loading={processing}
                                    icon={<SaveOutlined />}
                                >
                                    Cập nhật
                                </Button>
                                <Link href="/nguoi-dung">
                                    <Button>Hủy</Button>
                                </Link>
                            </Space>
                        </Form.Item>
                    </Form>
                </Card>
            </Space>
        </MainLayout>
    );
};

export default UserEdit;



```

Bây giờ cập nhật danh sách người dùng để ẩn nút xóa khi cần:

```javascriptreact
import React, { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import MainLayout from '../Layout/MainLayout';
import {
    Card,
    Table,
    Button,
    Input,
    Select,
    Space,
    Tag,
    Popconfirm,
    Typography,
    Avatar,
    Tooltip,
} from 'antd';
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    SearchOutlined,
    UserOutlined,
    KeyOutlined,
} from '@ant-design/icons';

const { Title, Text } = Typography;
const { Option } = Select;

const UserIndex = ({ users, filters }) => {
    const { auth } = usePage().props;
    const currentUserId = auth?.user?.id;
    
    const [search, setSearch] = useState(filters.search || '');
    const [role, setRole] = useState(filters.role || '');

    // Đếm số admin trong danh sách hiện tại (có thể không chính xác nếu phân trang)
    const adminCount = users.data.filter(u => u.role === 'admin').length;

    const handleSearch = () => {
        router.get('/nguoi-dung', { search, role }, { preserveState: true });
    };

    const handleDelete = (id) => {
        router.delete(`/nguoi-dung/${id}`);
    };

    // Kiểm tra có thể xóa user không
    const canDeleteUser = (record) => {
        // Không thể xóa chính mình
        if (record.id === currentUserId) return false;
        
        return true;
    };

    // Lý do không thể xóa
    const getDeleteDisabledReason = (record) => {
        if (record.id === currentUserId) {
            return 'Không thể xóa tài khoản của chính mình';
        }
        return '';
    };

    const columns = [
        {
            title: 'ID',
            dataIndex: 'id',
            key: 'id',
            width: 70,
        },
        {
            title: 'Người dùng',
            key: 'user',
            render: (_, record) => (
                <Space>
                    <Avatar 
                        style={{ 
                            backgroundColor: record.role === 'admin' ? '#f5222d' : '#1890ff' 
                        }}
                        icon={<UserOutlined />}
                    />
                    <div>
                        <div style={{ fontWeight: 500 }}>
                            {record.name}
                            {record.id === currentUserId && (
                                <Tag color="green" style={{ marginLeft: 8, fontSize: 10 }}>Bạn</Tag>
                            )}
                        </div>
                        <div style={{ fontSize: 12, color: '#666' }}>{record.email}</div>
                    </div>
                </Space>
            ),
        },
        {
            title: 'Vai trò',
            dataIndex: 'role',
            key: 'role',
            width: 150,
            render: (role) => (
                <Tag color={role === 'admin' ? 'red' : 'blue'}>
                    {role === 'admin' ? 'Quản trị viên' : 'Người dùng'}
                </Tag>
            ),
        },
        {
            title: 'Ngày tạo',
            dataIndex: 'created_at',
            key: 'created_at',
            width: 150,
            render: (date) => new Date(date).toLocaleDateString('vi-VN'),
        },
        {
            title: 'Thao tác',
            key: 'action',
            width: 280,
            render: (_, record) => (
                <Space>
                    <Link href={`/nguoi-dung/${record.id}/edit`}>
                        <Button type="primary" size="small" icon={<EditOutlined />}>
                            Sửa
                        </Button>
                    </Link>
                    {record.role !== 'admin' && (
                        <Link href={`/phan-quyen?user=${record.id}`}>
                            <Button size="small" icon={<KeyOutlined />}>
                                Phân quyền
                            </Button>
                        </Link>
                    )}
                    {canDeleteUser(record) ? (
                        <Popconfirm
                            title="Xác nhận xóa"
                            description="Bạn có chắc chắn muốn xóa người dùng này?"
                            onConfirm={() => handleDelete(record.id)}
                            okText="Xóa"
                            cancelText="Hủy"
                            okButtonProps={{ danger: true }}
                        >
                            <Button danger size="small" icon={<DeleteOutlined />}>
                                Xóa
                            </Button>
                        </Popconfirm>
                    ) : (
                        <Tooltip title={getDeleteDisabledReason(record)}>
                            <Button danger size="small" icon={<DeleteOutlined />} disabled>
                                Xóa
                            </Button>
                        </Tooltip>
                    )}
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Space direction="vertical" size="large" style={{ width: '100%' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <Title level={2} style={{ margin: 0 }}>Quản lý người dùng</Title>
                    <Link href="/nguoi-dung/create">
                        <Button type="primary" icon={<PlusOutlined />}>
                            Thêm người dùng
                        </Button>
                    </Link>
                </div>

                <Card>
                    <Space style={{ marginBottom: 16 }}>
                        <Input
                            placeholder="Tìm kiếm theo tên, email..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onPressEnter={handleSearch}
                            style={{ width: 250 }}
                            prefix={<SearchOutlined />}
                        />
                        <Select
                            placeholder="Lọc theo vai trò"
                            value={role || undefined}
                            onChange={(value) => setRole(value)}
                            style={{ width: 150 }}
                            allowClear
                        >
                            <Option value="admin">Quản trị viên</Option>
                            <Option value="user">Người dùng</Option>
                        </Select>
                        <Button type="primary" onClick={handleSearch}>
                            Tìm kiếm
                        </Button>
                    </Space>

                    <Table
                        columns={columns}
                        dataSource={users.data}
                        rowKey="id"
                        pagination={{
                            current: users.current_page,
                            total: users.total,
                            pageSize: users.per_page,
                            showSizeChanger: false,
                            showTotal: (total) => `Tổng ${total} người dùng`,
                            onChange: (page) => {
                                router.get('/nguoi-dung', { ...filters, page }, { preserveState: true });
                            },
                        }}
                    />
                </Card>
            </Space>
        </MainLayout>
    );
};

export default UserIndex;



```

Đã fix xong! Tôi đã thêm các quy tắc bảo mật như các hệ thống thực tế:

**Backend (UserController):**
1. ✅ Không cho admin tự thay đổi vai trò của chính mình
2. ✅ Không cho xóa chính mình
3. ✅ Không cho xóa/hạ cấp admin cuối cùng trong hệ thống

**Frontend (User/Index.jsx):**
1. ✅ Hiển thị tag "Bạn" bên cạnh tài khoản đang đăng nhập
2. ✅ Nút "Xóa" bị disable nếu là chính mình (có tooltip giải thích)

**Frontend (User/Edit.jsx):**
1. ✅ Hiển thị cảnh báo màu vàng nếu đang sửa chính mình
2. ✅ Dropdown "Vai trò" bị disable nếu:
   - Đang sửa chính mình
   - Là admin cuối cùng trong hệ thống
3. ✅ Có icon 🔒 và tooltip giải thích lý do không thể thay đổi

Bây giờ khi admin sửa tài khoản của chính mình:
- Có thể thay đổi: Tên, Email, Mật khẩu
- **Không thể** thay đổi: Vai trò (dropdown bị khóa)

Bạn refresh lại trang và thử sửa tài khoản Administrator để xem kết quả!

---

**User**

ok mọi thứ trước mắt đã ok giờ bạn hãy chỉ ra những sự chỉnh sữa trong code của tôi từ lúc bắt đầu làm chức năng phân quyền đến giờ và tôi muốn hiểu luồng hoạt động cụ thể của nó là chi tiết code các thứ bạn giải thích đầy đủ nhé

---

**Cursor**

Tôi sẽ đọc lại các file quan trọng và giải thích chi tiết cho bạn:

Tuyệt vời! Dưới đây là **TỔNG HỢP ĐẦY ĐỦ** về hệ thống phân quyền đã xây dựng:

---

# 🔐 HỆ THỐNG PHÂN QUYỀN - GIẢI THÍCH CHI TIẾT

## 📁 1. CẤU TRÚC DATABASE

### 1.1 Bảng `screens` - Lưu danh sách màn hình/chức năng

```1:46:database/migrations/2025_12_14_000001_create_screens_table.php
// ... migration code ...
```

**Giải thích các cột:**
| Cột | Mô tả |
|-----|-------|
| `id` | Khóa chính |
| `name` | Tên hiển thị (VD: "Quản lý Thiết bị") |
| `code` | Mã dùng để check quyền (VD: "thiet-bi") |
| `route` | Đường dẫn URL (VD: "/thiet-bi") |
| `icon` | Icon Ant Design (VD: "ToolOutlined") |
| `parent_id` | ID của màn hình cha (dùng cho cấu trúc đệ quy) |
| `order` | Thứ tự hiển thị trên menu |
| `is_active` | Trạng thái hoạt động |
| `is_menu` | Có hiển thị trên menu không |

**Cấu trúc đệ quy:**
```
├── Dashboard (parent_id = null)
├── QL Cơ sở hạ tầng (parent_id = null)    ← Parent
│   └── Danh sách cơ sở (parent_id = 2)    ← Child
├── QL Thiết bị (parent_id = null)         ← Parent
│   ├── Danh sách thiết bị (parent_id = 5) ← Child
│   └── Lịch sử bảo dưỡng (parent_id = 5)  ← Child
└── Quản lý Hệ thống (parent_id = null)    ← Parent
    ├── Quản lý Người dùng (parent_id = 9) ← Child
    └── Phân quyền (parent_id = 9)         ← Child
```

### 1.2 Bảng `user_permissions` - Lưu quyền của từng user

```1:50:database/migrations/2025_12_14_000002_create_user_permissions_table.php
// ... migration code ...
```

**Giải thích:**
| Cột | Mô tả |
|-----|-------|
| `user_id` | ID người dùng |
| `screen_id` | ID màn hình |
| `can_view` | Quyền xem (truy cập màn hình) |
| `can_create` | Quyền thêm mới |
| `can_edit` | Quyền chỉnh sửa |
| `can_delete` | Quyền xóa |

**Ví dụ dữ liệu:**
```
| user_id | screen_id | can_view | can_create | can_edit | can_delete |
|---------|-----------|----------|------------|----------|------------|
| 2       | 3         | true     | true       | false    | false      |
| 2       | 7         | true     | false      | false    | false      |
```
→ User ID 2 có quyền XEM + THÊM cho screen 3, chỉ XEM cho screen 7

---

## 📦 2. MODELS (Eloquent)

### 2.1 Model `Screen`

```8:122:app/Models/Screen.php
class Screen extends Model
{
    // ... code ...
}
```

**Các quan hệ quan trọng:**

```php
// Quan hệ cha (1 screen có 1 cha hoặc null)
public function parent()
{
    return $this->belongsTo(Screen::class, 'parent_id');
}

// Quan hệ con (1 screen có nhiều con)
public function children()
{
    return $this->hasMany(Screen::class, 'parent_id')->orderBy('order');
}

// Đệ quy lấy tất cả con và con của con
public function allChildren()
{
    return $this->children()->with('allChildren');
}
```

**Các phương thức hữu ích:**
- `getTree()`: Lấy danh sách screens dạng cây
- `getFlatTree()`: Lấy danh sách phẳng với level
- `getParentIds()`: Lấy tất cả ID cha (đệ quy lên)
- `getChildrenIds()`: Lấy tất cả ID con (đệ quy xuống)

### 2.2 Model `UserPermission`

```8:51:app/Models/UserPermission.php
class UserPermission extends Model
{
    // ... code ...
}
```

### 2.3 Model `User` (Bổ sung)

```66:142:app/Models/User.php
// Quan hệ với permissions
public function permissions()
{
    return $this->hasMany(UserPermission::class);
}

// Lấy permission cho một screen cụ thể
public function getPermissionForScreen($screenCode)
{
    // Admin có tất cả quyền
    if ($this->isAdmin()) {
        return ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true];
    }
    // ... tìm permission trong database ...
}

// Kiểm tra quyền
public function hasPermission($screenCode, $permission = 'can_view')
{
    if ($this->isAdmin()) return true;
    // ... kiểm tra trong database ...
}

// Các helper methods
public function canView($screenCode) { return $this->hasPermission($screenCode, 'can_view'); }
public function canCreate($screenCode) { return $this->hasPermission($screenCode, 'can_create'); }
public function canEdit($screenCode) { return $this->hasPermission($screenCode, 'can_edit'); }
public function canDelete($screenCode) { return $this->hasPermission($screenCode, 'can_delete'); }
```

---

## 🛡️ 3. MIDDLEWARE CHECK PERMISSION

```1:44:app/Http/Middleware/CheckPermission.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $screenCode, $permission = 'can_view')
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra quyền
        if (!$user->hasPermission($screenCode, $permission)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            abort(403, 'Bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}
```

**Cách sử dụng trong `routes/web.php`:**

```php
// Áp dụng middleware với 2 tham số: screenCode và permission
Route::middleware('permission:co-so,can_view')->group(function () {
    Route::get('/co-so', [CoSoController::class, 'index']);
});

Route::middleware('permission:co-so,can_create')->group(function () {
    Route::get('/co-so/create', [CoSoController::class, 'create']);
    Route::post('/co-so', [CoSoController::class, 'store']);
});

Route::middleware('permission:co-so,can_edit')->group(function () {
    Route::get('/co-so/{co_so}/edit', [CoSoController::class, 'edit']);
    Route::put('/co-so/{co_so}', [CoSoController::class, 'update']);
});

Route::middleware('permission:co-so,can_delete')->group(function () {
    Route::delete('/co-so/{co_so}', [CoSoController::class, 'destroy']);
});
```

---

## 🔄 4. LUỒNG HOẠT ĐỘNG

### 4.1 Khi người dùng đăng nhập

```
┌─────────────┐    ┌──────────────────────────────┐    ┌─────────────────┐
│   Login     │───▶│  HandleInertiaRequests.php   │───▶│   React App     │
│   (Auth)    │    │  (Share data to frontend)    │    │   (Frontend)    │
└─────────────┘    └──────────────────────────────┘    └─────────────────┘
```

**Trong `HandleInertiaRequests.php`:**

```37:56:app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    $user = $request->user();
    
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $user ? [...] : null,
        ],
        'userPermissions' => $user ? $this->getUserPermissions($user) : [],
        'menuScreens' => $user ? $this->getMenuScreens($user) : [],
        'flash' => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ],
    ]);
}
```

**`getUserPermissions()`** - Lấy quyền của user:
```php
private function getUserPermissions($user)
{
    if ($user->isAdmin()) {
        // Admin có tất cả quyền cho tất cả screens
        $screens = Screen::where('is_active', true)->get();
        $permissions = [];
        foreach ($screens as $screen) {
            $permissions[$screen->code] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
            ];
        }
        return $permissions;
    }

    // User thường - lấy từ database
    $permissions = [];
    foreach ($user->permissions as $permission) {
        $screen = $permission->screen;
        if ($screen) {
            $permissions[$screen->code] = [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        }
    }
    return $permissions;
}
```

**`getMenuScreens()`** - Lấy menu theo quyền:
```php
private function getMenuScreens($user)
{
    // Lấy danh sách screen codes mà user có quyền view
    if ($user->isAdmin()) {
        $viewableScreenCodes = Screen::pluck('code')->toArray();
    } else {
        $viewableScreenCodes = $user->permissions()
            ->where('can_view', true)
            ->pluck('screen.code')
            ->toArray();
    }

    // Lấy tree và filter theo quyền
    // Chỉ hiển thị parent nếu có ít nhất 1 child có quyền
    return $this->filterScreensByPermission($screens, $viewableScreenCodes);
}
```

### 4.2 Khi truy cập một route

```
┌─────────────────┐    ┌─────────────────────┐    ┌──────────────────┐
│  GET /co-so     │───▶│  CheckPermission    │───▶│  CoSoController  │
│  (Request)      │    │  Middleware         │    │  (if allowed)    │
└─────────────────┘    └─────────────────────┘    └──────────────────┘
                              │
                              ▼ (if not allowed)
                       ┌─────────────────────┐
                       │  403 Forbidden      │
                       │  hoặc redirect back │
                       └─────────────────────┘
```

---

## 🖥️ 5. FRONTEND

### 5.1 MainLayout - Dynamic Menu

```69:113:resources/js/components/Layout/MainLayout.jsx
// Tạo menu items từ menuScreens (đã được filter theo quyền)
const menuItems = useMemo(() => {
    if (!menuScreens || menuScreens.length === 0) {
        return [/* fallback menu */];
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

        // Menu item đơn
        return {
            key: screen.route,
            icon: icon,
            label: <Link href={screen.route}>{screen.name}</Link>,
        };
    });
}, [menuScreens]);
```

**Kết quả:** Menu chỉ hiển thị những màn hình user có quyền xem!

### 5.2 Permission/Index.jsx - Giao diện phân quyền

**Logic cascade (tự động tích):**

```132:196:resources/js/components/Permission/Index.jsx
const handlePermissionChange = (screenId, permissionType, checked) => {
    setPermissions((prev) => {
        const newPermissions = { ...prev };
        
        // Set cho chính nó
        setPermission(screenId, permissionType, checked);

        // CASCADE XUỐNG: Nếu là parent -> tích/bỏ tất cả children
        if (screen && screen.isParent && screen.childIds) {
            screen.childIds.forEach((childId) => {
                setPermission(childId, permissionType, checked);
            });
        }

        // CASCADE LÊN: Nếu là child
        if (screen && screen.parentId) {
            if (checked) {
                // Tích child -> tích parent
                setPermission(screen.parentId, permissionType, true);
            } else {
                // Bỏ tích child -> kiểm tra còn child nào không
                const anyChildChecked = parent.childIds.some((childId) => {
                    if (childId === screenId) return false;
                    return newPermissions[childId]?.[permissionType];
                });
                // Không còn child nào -> bỏ tích parent
                if (!anyChildChecked) {
                    setPermission(screen.parentId, permissionType, false);
                }
            }
        }

        return newPermissions;
    });
};
```

**Logic checkbox indeterminate:**

```370:388:resources/js/components/Permission/Index.jsx
const isCellChecked = (record, permissionType) => {
    // Parent checked khi TẤT CẢ children đều được tích
    if (record.isParent && record.childIds.length > 0) {
        return record.childIds.every((id) => permissions[id]?.[permissionType]);
    }
    return permissions[record.id]?.[permissionType] || false;
};

const isCellIndeterminate = (record, permissionType) => {
    if (!record.isParent) return false;
    
    const childrenCheckedCount = record.childIds.filter(
        (id) => permissions[id]?.[permissionType]
    ).length;
    
    // Indeterminate khi MỘT SỐ (không phải tất cả, không phải 0) children được tích
    return childrenCheckedCount > 0 && childrenCheckedCount < record.childIds.length;
};
```

---

## 📝 6. TỔNG KẾT LUỒNG HOẠT ĐỘNG

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           LUỒNG PHÂN QUYỀN                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. ADMIN PHÂN QUYỀN:                                                       │
│     ┌──────────┐    ┌─────────────────┐    ┌──────────────────┐            │
│     │ Chọn    │───▶│ Tích checkbox   │───▶│ Lưu vào DB       │            │
│     │ User    │    │ quyền (cascade) │    │ user_permissions │            │
│     └──────────┘    └─────────────────┘    └──────────────────┘            │
│                                                                             │
│  2. USER ĐĂNG NHẬP:                                                         │
│     ┌──────────┐    ┌─────────────────────┐    ┌───────────────┐           │
│     │ Login   │───▶│ HandleInertiaRequests│───▶│ Share data:   │           │
│     │         │    │ Middleware           │    │ - userPerms   │           │
│     │         │    │                       │    │ - menuScreens │           │
│     └──────────┘    └─────────────────────┘    └───────────────┘           │
│                                                                             │
│  3. USER TRUY CẬP ROUTE:                                                    │
│     ┌──────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│     │ Request     │───▶│ CheckPermission │───▶│ Allowed?        │         │
│     │ GET /co-so  │    │ Middleware      │    │ ├─ Yes: Continue│         │
│     │             │    │                 │    │ └─ No: 403/back │         │
│     └──────────────┘    └─────────────────┘    └─────────────────┘         │
│                                                                             │
│  4. HIỂN THỊ MENU:                                                          │
│     ┌──────────────────┐    ┌─────────────────────────────────┐            │
│     │ MainLayout.jsx  │───▶│ Chỉ render menu items có quyền  │            │
│     │ menuScreens     │    │ (đã filter từ backend)          │            │
│     └──────────────────┘    └─────────────────────────────────┘            │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 📂 7. DANH SÁCH FILE ĐÃ TẠO/SỬA

| File | Loại | Mô tả |
|------|------|-------|
| `database/migrations/2025_12_14_000001_create_screens_table.php` | Tạo mới | Migration tạo bảng screens |
| `database/migrations/2025_12_14_000002_create_user_permissions_table.php` | Tạo mới | Migration tạo bảng user_permissions |
| `app/Models/Screen.php` | Tạo mới | Model Screen với đệ quy |
| `app/Models/UserPermission.php` | Tạo mới | Model UserPermission |
| `app/Models/User.php` | Sửa | Thêm relations và methods kiểm tra quyền |
| `app/Http/Middleware/CheckPermission.php` | Tạo mới | Middleware kiểm tra quyền |
| `app/Http/Kernel.php` | Sửa | Đăng ký middleware 'permission' |
| `app/Http/Middleware/HandleInertiaRequests.php` | Sửa | Share userPermissions và menuScreens |
| `app/Http/Controllers/PermissionController.php` | Tạo mới | Controller quản lý phân quyền |
| `app/Http/Controllers/UserController.php` | Sửa | Thêm logic bảo vệ admin |
| `routes/web.php` | Sửa | Áp dụng middleware cho các routes |
| `database/seeders/ScreenSeeder.php` | Tạo mới | Seeder tạo dữ liệu screens |
| `resources/js/components/Permission/Index.jsx` | Tạo mới | Giao diện phân quyền |
| `resources/js/components/Layout/MainLayout.jsx` | Sửa | Dynamic menu theo quyền |
| `resources/js/components/User/Index.jsx` | Sửa | Ẩn nút xóa cho chính mình |
| `resources/js/components/User/Edit.jsx` | Sửa | Disable vai trò nếu sửa chính mình |

---

Đây là toàn bộ hệ thống phân quyền bạn đã xây dựng! Có câu hỏi nào cần giải thích thêm không?

```php
// ... migration code ...
```

```plaintext
├── Dashboard (parent_id = null)
├── QL Cơ sở hạ tầng (parent_id = null)    ← Parent
│   └── Danh sách cơ sở (parent_id = 2)    ← Child
├── QL Thiết bị (parent_id = null)         ← Parent
│   ├── Danh sách thiết bị (parent_id = 5) ← Child
│   └── Lịch sử bảo dưỡng (parent_id = 5)  ← Child
└── Quản lý Hệ thống (parent_id = null)    ← Parent
    ├── Quản lý Người dùng (parent_id = 9) ← Child
    └── Phân quyền (parent_id = 9)         ← Child
```

```php
// ... migration code ...
```

```plaintext
| user_id | screen_id | can_view | can_create | can_edit | can_delete |
|---------|-----------|----------|------------|----------|------------|
| 2       | 3         | true     | true       | false    | false      |
| 2       | 7         | true     | false      | false    | false      |
```

```php
class Screen extends Model
{
    // ... code ...
}
```

```php
// Quan hệ cha (1 screen có 1 cha hoặc null)
public function parent()
{
    return $this->belongsTo(Screen::class, 'parent_id');
}

// Quan hệ con (1 screen có nhiều con)
public function children()
{
    return $this->hasMany(Screen::class, 'parent_id')->orderBy('order');
}

// Đệ quy lấy tất cả con và con của con
public function allChildren()
{
    return $this->children()->with('allChildren');
}
```

```php
class UserPermission extends Model
{
    // ... code ...
}
```

```php
// Quan hệ với permissions
public function permissions()
{
    return $this->hasMany(UserPermission::class);
}

// Lấy permission cho một screen cụ thể
public function getPermissionForScreen($screenCode)
{
    // Admin có tất cả quyền
    if ($this->isAdmin()) {
        return ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true];
    }
    // ... tìm permission trong database ...
}

// Kiểm tra quyền
public function hasPermission($screenCode, $permission = 'can_view')
{
    if ($this->isAdmin()) return true;
    // ... kiểm tra trong database ...
}

// Các helper methods
public function canView($screenCode) { return $this->hasPermission($screenCode, 'can_view'); }
public function canCreate($screenCode) { return $this->hasPermission($screenCode, 'can_create'); }
public function canEdit($screenCode) { return $this->hasPermission($screenCode, 'can_edit'); }
public function canDelete($screenCode) { return $this->hasPermission($screenCode, 'can_delete'); }
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $screenCode, $permission = 'can_view')
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra quyền
        if (!$user->hasPermission($screenCode, $permission)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            abort(403, 'Bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}
```

```php
// Áp dụng middleware với 2 tham số: screenCode và permission
Route::middleware('permission:co-so,can_view')->group(function () {
    Route::get('/co-so', [CoSoController::class, 'index']);
});

Route::middleware('permission:co-so,can_create')->group(function () {
    Route::get('/co-so/create', [CoSoController::class, 'create']);
    Route::post('/co-so', [CoSoController::class, 'store']);
});

Route::middleware('permission:co-so,can_edit')->group(function () {
    Route::get('/co-so/{co_so}/edit', [CoSoController::class, 'edit']);
    Route::put('/co-so/{co_so}', [CoSoController::class, 'update']);
});

Route::middleware('permission:co-so,can_delete')->group(function () {
    Route::delete('/co-so/{co_so}', [CoSoController::class, 'destroy']);
});
```

```plaintext
┌─────────────┐    ┌──────────────────────────────┐    ┌─────────────────┐
│   Login     │───▶│  HandleInertiaRequests.php   │───▶│   React App     │
│   (Auth)    │    │  (Share data to frontend)    │    │   (Frontend)    │
└─────────────┘    └──────────────────────────────┘    └─────────────────┘
```

```php
public function share(Request $request): array
{
    $user = $request->user();
    
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $user ? [...] : null,
        ],
        'userPermissions' => $user ? $this->getUserPermissions($user) : [],
        'menuScreens' => $user ? $this->getMenuScreens($user) : [],
        'flash' => [
            'success' => $request->session()->get('success'),
            'error' => $request->session()->get('error'),
        ],
    ]);
}
```

```php
private function getUserPermissions($user)
{
    if ($user->isAdmin()) {
        // Admin có tất cả quyền cho tất cả screens
        $screens = Screen::where('is_active', true)->get();
        $permissions = [];
        foreach ($screens as $screen) {
            $permissions[$screen->code] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
            ];
        }
        return $permissions;
    }

    // User thường - lấy từ database
    $permissions = [];
    foreach ($user->permissions as $permission) {
        $screen = $permission->screen;
        if ($screen) {
            $permissions[$screen->code] = [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        }
    }
    return $permissions;
}
```

```php
private function getMenuScreens($user)
{
    // Lấy danh sách screen codes mà user có quyền view
    if ($user->isAdmin()) {
        $viewableScreenCodes = Screen::pluck('code')->toArray();
    } else {
        $viewableScreenCodes = $user->permissions()
            ->where('can_view', true)
            ->pluck('screen.code')
            ->toArray();
    }

    // Lấy tree và filter theo quyền
    // Chỉ hiển thị parent nếu có ít nhất 1 child có quyền
    return $this->filterScreensByPermission($screens, $viewableScreenCodes);
}
```

```plaintext
┌─────────────────┐    ┌─────────────────────┐    ┌──────────────────┐
│  GET /co-so     │───▶│  CheckPermission    │───▶│  CoSoController  │
│  (Request)      │    │  Middleware         │    │  (if allowed)    │
└─────────────────┘    └─────────────────────┘    └──────────────────┘
                              │
                              ▼ (if not allowed)
                       ┌─────────────────────┐
                       │  403 Forbidden      │
                       │  hoặc redirect back │
                       └─────────────────────┘
```

```javascriptreact
// Tạo menu items từ menuScreens (đã được filter theo quyền)
const menuItems = useMemo(() => {
    if (!menuScreens || menuScreens.length === 0) {
        return [/* fallback menu */];
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

        // Menu item đơn
        return {
            key: screen.route,
            icon: icon,
            label: <Link href={screen.route}>{screen.name}</Link>,
        };
    });
}, [menuScreens]);
```

```javascriptreact
const handlePermissionChange = (screenId, permissionType, checked) => {
    setPermissions((prev) => {
        const newPermissions = { ...prev };
        
        // Set cho chính nó
        setPermission(screenId, permissionType, checked);

        // CASCADE XUỐNG: Nếu là parent -> tích/bỏ tất cả children
        if (screen && screen.isParent && screen.childIds) {
            screen.childIds.forEach((childId) => {
                setPermission(childId, permissionType, checked);
            });
        }

        // CASCADE LÊN: Nếu là child
        if (screen && screen.parentId) {
            if (checked) {
                // Tích child -> tích parent
                setPermission(screen.parentId, permissionType, true);
            } else {
                // Bỏ tích child -> kiểm tra còn child nào không
                const anyChildChecked = parent.childIds.some((childId) => {
                    if (childId === screenId) return false;
                    return newPermissions[childId]?.[permissionType];
                });
                // Không còn child nào -> bỏ tích parent
                if (!anyChildChecked) {
                    setPermission(screen.parentId, permissionType, false);
                }
            }
        }

        return newPermissions;
    });
};
```

```javascriptreact
const isCellChecked = (record, permissionType) => {
    // Parent checked khi TẤT CẢ children đều được tích
    if (record.isParent && record.childIds.length > 0) {
        return record.childIds.every((id) => permissions[id]?.[permissionType]);
    }
    return permissions[record.id]?.[permissionType] || false;
};

const isCellIndeterminate = (record, permissionType) => {
    if (!record.isParent) return false;
    
    const childrenCheckedCount = record.childIds.filter(
        (id) => permissions[id]?.[permissionType]
    ).length;
    
    // Indeterminate khi MỘT SỐ (không phải tất cả, không phải 0) children được tích
    return childrenCheckedCount > 0 && childrenCheckedCount < record.childIds.length;
};
```

```plaintext
┌─────────────────────────────────────────────────────────────────────────────┐
│                           LUỒNG PHÂN QUYỀN                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. ADMIN PHÂN QUYỀN:                                                       │
│     ┌──────────┐    ┌─────────────────┐    ┌──────────────────┐            │
│     │ Chọn    │───▶│ Tích checkbox   │───▶│ Lưu vào DB       │            │
│     │ User    │    │ quyền (cascade) │    │ user_permissions │            │
│     └──────────┘    └─────────────────┘    └──────────────────┘            │
│                                                                             │
│  2. USER ĐĂNG NHẬP:                                                         │
│     ┌──────────┐    ┌─────────────────────┐    ┌───────────────┐           │
│     │ Login   │───▶│ HandleInertiaRequests│───▶│ Share data:   │           │
│     │         │    │ Middleware           │    │ - userPerms   │           │
│     │         │    │                       │    │ - menuScreens │           │
│     └──────────┘    └─────────────────────┘    └───────────────┘           │
│                                                                             │
│  3. USER TRUY CẬP ROUTE:                                                    │
│     ┌──────────────┐    ┌─────────────────┐    ┌─────────────────┐         │
│     │ Request     │───▶│ CheckPermission │───▶│ Allowed?        │         │
│     │ GET /co-so  │    │ Middleware      │    │ ├─ Yes: Continue│         │
│     │             │    │                 │    │ └─ No: 403/back │         │
│     └──────────────┘    └─────────────────┘    └─────────────────┘         │
│                                                                             │
│  4. HIỂN THỊ MENU:                                                          │
│     ┌──────────────────┐    ┌─────────────────────────────────┐            │
│     │ MainLayout.jsx  │───▶│ Chỉ render menu items có quyền  │            │
│     │ menuScreens     │    │ (đã filter từ backend)          │            │
│     └──────────────────┘    └─────────────────────────────────┘            │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

**User**

và ví dụ nữa tôi thêm 1 cột quyền nữa thì có dễ dàng không 

---

**Cursor**

Rất dễ dàng! Hệ thống đã được thiết kế theo kiểu mở rộng. Giả sử bạn muốn thêm quyền **"Xuất Excel"** (`can_export`), đây là các bước cần làm:

---

## 📝 CÁC BƯỚC THÊM QUYỀN MỚI

### **Bước 1: Tạo Migration thêm cột**

```php
// database/migrations/2025_12_16_000001_add_can_export_to_user_permissions_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanExportToUserPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->boolean('can_export')->default(false)->after('can_delete');
        });
    }

    public function down()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn('can_export');
        });
    }
}
```

---

### **Bước 2: Cập nhật Model `UserPermission`**

```php
// app/Models/UserPermission.php

protected $fillable = [
    'user_id',
    'screen_id',
    'can_view',
    'can_create',
    'can_edit',
    'can_delete',
    'can_export',  // ← Thêm mới
];

protected $casts = [
    'can_view' => 'boolean',
    'can_create' => 'boolean',
    'can_edit' => 'boolean',
    'can_delete' => 'boolean',
    'can_export' => 'boolean',  // ← Thêm mới
];
```

---

### **Bước 3: Cập nhật Model `User`**

```php
// app/Models/User.php

public function getPermissionForScreen($screenCode)
{
    if ($this->isAdmin()) {
        return [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
            'can_export' => true,  // ← Thêm mới
        ];
    }
    // ... phần còn lại tương tự
}

// Thêm helper method
public function canExport($screenCode)
{
    return $this->hasPermission($screenCode, 'can_export');
}
```

---

### **Bước 4: Cập nhật `HandleInertiaRequests`**

```php
// app/Http/Middleware/HandleInertiaRequests.php

private function getUserPermissions($user)
{
    if ($user->isAdmin()) {
        foreach ($screens as $screen) {
            $permissions[$screen->code] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_export' => true,  // ← Thêm mới
            ];
        }
    }
    // ... phần user thường tương tự, lấy từ DB
}
```

---

### **Bước 5: Cập nhật `PermissionController`**

```php
// app/Http/Controllers/PermissionController.php

public function getUserPermissions(User $user)
{
    $permissions = $user->permissions()->get()->keyBy('screen_id')->map(function ($permission) {
        return [
            'can_view' => $permission->can_view,
            'can_create' => $permission->can_create,
            'can_edit' => $permission->can_edit,
            'can_delete' => $permission->can_delete,
            'can_export' => $permission->can_export,  // ← Thêm mới
        ];
    });
    return response()->json($permissions);
}

public function updateUserPermissions(Request $request, User $user)
{
    $request->validate([
        // ... existing validations ...
        'permissions.*.can_export' => 'boolean',  // ← Thêm mới
    ]);
    
    // Phần tạo record
    UserPermission::create([
        // ... existing fields ...
        'can_export' => $permission['can_export'] ?? false,  // ← Thêm mới
    ]);
}
```

---

### **Bước 6: Cập nhật Frontend `Permission/Index.jsx`**

```jsx
// resources/js/components/Permission/Index.jsx

// 1. Thêm cột trong columns array
{
    title: (
        <Space direction="vertical" align="center" size={0}>
            <Checkbox
                checked={isColumnAllChecked('can_export')}
                indeterminate={isColumnIndeterminate('can_export')}
                onChange={(e) => handleColumnCheckAll('can_export', e.target.checked)}
                disabled={!selectedUserId}
            />
            <Text>Xuất Excel</Text>
        </Space>
    ),
    dataIndex: 'can_export',
    key: 'can_export',
    width: 100,
    align: 'center',
    render: (_, record) => (
        <Checkbox
            checked={isCellChecked(record, 'can_export')}
            indeterminate={isCellIndeterminate(record, 'can_export')}
            onChange={(e) => handlePermissionChange(record.id, 'can_export', e.target.checked)}
            disabled={!selectedUserId}
        />
    ),
},

// 2. Cập nhật các hàm helper khởi tạo permission mặc định
const setPermission = (id, type, value) => {
    if (!newPermissions[id]) {
        newPermissions[id] = {
            can_view: false,
            can_create: false,
            can_edit: false,
            can_delete: false,
            can_export: false,  // ← Thêm mới
        };
    }
    // ...
};

// 3. Cập nhật isRowAllChecked và isRowIndeterminate
const isRowAllChecked = (record) => {
    const perms = permissions[record.id] || {};
    return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete && perms.can_export;
};
```

---

### **Bước 7: Áp dụng trong Routes (nếu cần)**

```php
// routes/web.php

Route::middleware('permission:thiet-bi,can_export')->group(function () {
    Route::get('/thiet-bi/export', [ThietBiController::class, 'export'])->name('thiet-bi.export');
});
```

---

## 📊 TỔNG KẾT

| File cần sửa | Độ phức tạp |
|--------------|-------------|
| Migration (tạo mới) | ⭐ Dễ |
| `UserPermission.php` | ⭐ Dễ (thêm 2 dòng) |
| `User.php` | ⭐ Dễ (thêm vài dòng) |
| `HandleInertiaRequests.php` | ⭐ Dễ (thêm 1 dòng mỗi chỗ) |
| `PermissionController.php` | ⭐ Dễ (thêm vài dòng) |
| `Permission/Index.jsx` | ⭐⭐ Trung bình (copy-paste cột) |
| `routes/web.php` | ⭐ Dễ (chỉ khi cần route mới) |

**Tổng thời gian ước tính:** 10-15 phút

---

## 💡 GỢI Ý CẢI TIẾN

Nếu bạn dự định thêm nhiều quyền trong tương lai, có thể tái cấu trúc theo kiểu **JSON** hoặc **bảng riêng**:

```php
// Thay vì nhiều cột boolean
Schema::table('user_permissions', function ($table) {
    $table->json('permissions')->default('{}');
    // Lưu dạng: {"view": true, "create": true, "export": true, "approve": true}
});
```

Nhưng với cách hiện tại (các cột boolean riêng biệt) vẫn rất tốt cho hệ thống quy mô nhỏ-vừa và dễ query hơn.

---

Bạn có muốn tôi giúp thêm quyền mới cụ thể nào không? Nếu cần, hãy chuyển sang **Agent mode** để tôi thực hiện các thay đổi!

```php
// database/migrations/2025_12_16_000001_add_can_export_to_user_permissions_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanExportToUserPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->boolean('can_export')->default(false)->after('can_delete');
        });
    }

    public function down()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn('can_export');
        });
    }
}
```

```php
// app/Models/UserPermission.php

protected $fillable = [
    'user_id',
    'screen_id',
    'can_view',
    'can_create',
    'can_edit',
    'can_delete',
    'can_export',  // ← Thêm mới
];

protected $casts = [
    'can_view' => 'boolean',
    'can_create' => 'boolean',
    'can_edit' => 'boolean',
    'can_delete' => 'boolean',
    'can_export' => 'boolean',  // ← Thêm mới
];
```

```php
// app/Models/User.php

public function getPermissionForScreen($screenCode)
{
    if ($this->isAdmin()) {
        return [
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
            'can_export' => true,  // ← Thêm mới
        ];
    }
    // ... phần còn lại tương tự
}

// Thêm helper method
public function canExport($screenCode)
{
    return $this->hasPermission($screenCode, 'can_export');
}
```

```php
// app/Http/Middleware/HandleInertiaRequests.php

private function getUserPermissions($user)
{
    if ($user->isAdmin()) {
        foreach ($screens as $screen) {
            $permissions[$screen->code] = [
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'can_export' => true,  // ← Thêm mới
            ];
        }
    }
    // ... phần user thường tương tự, lấy từ DB
}
```

```php
// app/Http/Controllers/PermissionController.php

public function getUserPermissions(User $user)
{
    $permissions = $user->permissions()->get()->keyBy('screen_id')->map(function ($permission) {
        return [
            'can_view' => $permission->can_view,
            'can_create' => $permission->can_create,
            'can_edit' => $permission->can_edit,
            'can_delete' => $permission->can_delete,
            'can_export' => $permission->can_export,  // ← Thêm mới
        ];
    });
    return response()->json($permissions);
}

public function updateUserPermissions(Request $request, User $user)
{
    $request->validate([
        // ... existing validations ...
        'permissions.*.can_export' => 'boolean',  // ← Thêm mới
    ]);
    
    // Phần tạo record
    UserPermission::create([
        // ... existing fields ...
        'can_export' => $permission['can_export'] ?? false,  // ← Thêm mới
    ]);
}
```

```javascriptreact
// resources/js/components/Permission/Index.jsx

// 1. Thêm cột trong columns array
{
    title: (
        <Space direction="vertical" align="center" size={0}>
            <Checkbox
                checked={isColumnAllChecked('can_export')}
                indeterminate={isColumnIndeterminate('can_export')}
                onChange={(e) => handleColumnCheckAll('can_export', e.target.checked)}
                disabled={!selectedUserId}
            />
            <Text>Xuất Excel</Text>
        </Space>
    ),
    dataIndex: 'can_export',
    key: 'can_export',
    width: 100,
    align: 'center',
    render: (_, record) => (
        <Checkbox
            checked={isCellChecked(record, 'can_export')}
            indeterminate={isCellIndeterminate(record, 'can_export')}
            onChange={(e) => handlePermissionChange(record.id, 'can_export', e.target.checked)}
            disabled={!selectedUserId}
        />
    ),
},

// 2. Cập nhật các hàm helper khởi tạo permission mặc định
const setPermission = (id, type, value) => {
    if (!newPermissions[id]) {
        newPermissions[id] = {
            can_view: false,
            can_create: false,
            can_edit: false,
            can_delete: false,
            can_export: false,  // ← Thêm mới
        };
    }
    // ...
};

// 3. Cập nhật isRowAllChecked và isRowIndeterminate
const isRowAllChecked = (record) => {
    const perms = permissions[record.id] || {};
    return perms.can_view && perms.can_create && perms.can_edit && perms.can_delete && perms.can_export;
};
```

```php
// routes/web.php

Route::middleware('permission:thiet-bi,can_export')->group(function () {
    Route::get('/thiet-bi/export', [ThietBiController::class, 'export'])->name('thiet-bi.export');
});
```

```php
// Thay vì nhiều cột boolean
Schema::table('user_permissions', function ($table) {
    $table->json('permissions')->default('{}');
    // Lưu dạng: {"view": true, "create": true, "export": true, "approve": true}
});
```

---

**User**

bạn ơi chức năng phân quyền á bạn làm ổn rồi cho tôi hỏi là sau mỗi tên chức năng ví dụ như danh sách cơ sở , danh sách khu nhà, bạn có thêm cái route /co-so và trong database bạn lưu hẳn 1 cột route đó nó chỉ có tác dụng hiển thị ngay trang đó thôi hả bạn hay còn làm gì khách ờ đâu không nếu không tôi nghĩ nên tối ưu chổ đó lại tại thấy k cần thiết hiển thị như vậy á

---

**Cursor**

Để trả lời chính xác câu hỏi của bạn, tôi cần xem lại code xem cột `route` được sử dụng ở đâu và như thế nào.

Tôi đã kiểm tra và thấy cột `route` có **2 tác dụng** khác nhau:

---

## ✅ TÁC DỤNG QUAN TRỌNG (cần giữ)

Cột `route` được dùng để **tạo menu động** trong `MainLayout.jsx`:

```91:103:c:\laragon\www\QLCSVC\qlcsvc_react - Copy\resources\js\components\Layout\MainLayout.jsx
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
```

Khi user click vào menu "Danh sách cơ sở", nó sẽ dùng `screen.route` (`/co-so`) để điều hướng tới trang đó. **Nếu bỏ cột này thì menu không hoạt động được!**

---

## 🔹 HIỂN THỊ TRONG TRANG PHÂN QUYỀN (có thể bỏ)

Còn phần hiển thị Tag `/co-so` bên cạnh "Danh sách cơ sở" trong trang phân quyền thì **chỉ mang tính tham khảo** - để admin biết chức năng đó ứng với đường dẫn nào:

```413:418:c:\laragon\www\QLCSVC\qlcsvc_react - Copy\resources\js\components\Permission\Index.jsx
                    {record.route && (
                        <Tag color="blue" style={{ fontSize: 11 }}>
                            {record.route}
                        </Tag>
                    )}
```

---

## 💡 KẾT LUẬN

| Thành phần | Cần thiết? | Lý do |
|------------|------------|-------|
| Cột `route` trong DB | ✅ **CẦN** | Dùng để tạo link menu |
| Hiển thị route trong trang phân quyền | ❌ **Tùy chọn** | Chỉ để tham khảo |

**Bạn có muốn tôi bỏ phần hiển thị route (cái Tag `/co-so`) trong trang phân quyền không?** Giao diện sẽ gọn hơn như này:

| Trước | Sau |
|-------|-----|
| `Danh sách cơ sở` `/co-so` | `Danh sách cơ sở` |

Nếu bạn đồng ý tôi sẽ bỏ phần đó đi!

```plaintext
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
```

```plaintext
                    {record.route && (
                        <Tag color="blue" style={{ fontSize: 11 }}>
                            {record.route}
                        </Tag>
                    )}
```

---

**User**

ủa tôi thắc mắc vậy trước khi có chức năng phân quyền thì menu điều hướng kiểu gì bạn cách làm đó có gọn và đơn giản hơn bây giờ không

---

**Cursor**

Câu hỏi hay! Để tôi so sánh cho bạn hiểu:

Rất tốt câu hỏi này! Để tôi so sánh cho bạn:

---

## 🔄 SO SÁNH 2 CÁCH LÀM

### **CÁCH CŨ (Trước phân quyền): HARDCODE**

Nếu không có phân quyền, menu thường được **viết cố định** (hardcode) như này:

```jsx
// MainLayout.jsx - CÁCH CŨ
const menuItems = [
    {
        key: '/',
        icon: <DashboardOutlined />,
        label: <Link href="/">Dashboard</Link>,
    },
    {
        key: 'co-so-group',
        icon: <BankOutlined />,
        label: 'QL Cơ sở hạ tầng',
        children: [
            { key: '/co-so', label: <Link href="/co-so">Danh sách cơ sở</Link> },
        ],
    },
    {
        key: 'khu-nha-group',
        icon: <HomeOutlined />,
        label: 'QL Khu nhà',
        children: [
            { key: '/khu-nha', label: <Link href="/khu-nha">Danh sách khu nhà</Link> },
        ],
    },
    // ... hardcode hết tất cả menu
];
```

**Ưu điểm:**
- ✅ Đơn giản, dễ hiểu
- ✅ Không cần database
- ✅ Ít code hơn

**Nhược điểm:**
- ❌ **TẤT CẢ user đều thấy TẤT CẢ menu** (không phân quyền được)
- ❌ Thêm/sửa menu phải sửa code
- ❌ Không linh hoạt

---

### **CÁCH MỚI (Hiện tại): DYNAMIC + PHÂN QUYỀN**

```jsx
// MainLayout.jsx - CÁCH MỚI
// menuScreens được truyền từ server, ĐÃ LỌC theo quyền của user
const menuItems = useMemo(() => {
    return menuScreens.map((screen) => {
        // Tạo menu động từ data đã được filter
    });
}, [menuScreens]);
```

**Ưu điểm:**
- ✅ **Mỗi user thấy menu khác nhau** tùy quyền
- ✅ Thêm/sửa menu chỉ cần update database (không cần deploy code)
- ✅ Quản lý tập trung

**Nhược điểm:**
- ❌ Phức tạp hơn
- ❌ Cần bảng `screens` + `user_permissions`
- ❌ Code dài hơn

---

## 📊 BẢNG SO SÁNH TỔNG QUAN

| Tiêu chí | Hardcode | Dynamic (hiện tại) |
|----------|----------|-------------------|
| Độ phức tạp | ⭐ Đơn giản | ⭐⭐⭐ Phức tạp |
| Phân quyền menu | ❌ Không | ✅ Có |
| Thay đổi menu | Sửa code | Sửa DB |
| Database cần | 0 bảng | 2 bảng |
| Tính linh hoạt | Thấp | Cao |
| Phù hợp với | Dự án nhỏ, 1-2 role | Dự án enterprise, nhiều role |

---

## 💡 TRƯỜNG HỢP CỦA BẠN

Vì bạn **CẦN phân quyền** (user khác nhau thấy menu khác nhau), nên **bắt buộc phải dùng cách dynamic**. 

Cột `route` trong database là **cần thiết** vì:
1. Server lọc menu theo quyền
2. Gửi danh sách menu đã lọc xuống client
3. Client dùng `route` để tạo link điều hướng

---

## 🤔 NẾU BẠN KHÔNG CẦN PHÂN QUYỀN?

Nếu dự án không cần phân quyền (tất cả user thấy hết menu), bạn có thể đơn giản hóa bằng cách:
1. Xóa bảng `screens`, `user_permissions`
2. Hardcode menu trong `MainLayout.jsx`
3. Bỏ middleware `permission`

Nhưng với yêu cầu phân quyền của bạn thì **cách hiện tại là hợp lý nhất** rồi! 👍

```javascriptreact
// MainLayout.jsx - CÁCH CŨ
const menuItems = [
    {
        key: '/',
        icon: <DashboardOutlined />,
        label: <Link href="/">Dashboard</Link>,
    },
    {
        key: 'co-so-group',
        icon: <BankOutlined />,
        label: 'QL Cơ sở hạ tầng',
        children: [
            { key: '/co-so', label: <Link href="/co-so">Danh sách cơ sở</Link> },
        ],
    },
    {
        key: 'khu-nha-group',
        icon: <HomeOutlined />,
        label: 'QL Khu nhà',
        children: [
            { key: '/khu-nha', label: <Link href="/khu-nha">Danh sách khu nhà</Link> },
        ],
    },
    // ... hardcode hết tất cả menu
];
```

```javascriptreact
// MainLayout.jsx - CÁCH MỚI
// menuScreens được truyền từ server, ĐÃ LỌC theo quyền của user
const menuItems = useMemo(() => {
    return menuScreens.map((screen) => {
        // Tạo menu động từ data đã được filter
    });
}, [menuScreens]);
```

---

**User**

ok bạn vậy còn cột code được sử dụng ở đâu bạn thấy nó cũng tương tự route và hình như cột icon bị thừa thì phải tôi k thấy ở đâu sử dụng

---

**Cursor**

Để tôi kiểm tra xem cột `code` và `icon` được sử dụng ở đâu:

Tôi đã kiểm tra xong. Đây là kết quả:

---

## 📊 CỘT `code` - **RẤT QUAN TRỌNG** ✅

Cột `code` được sử dụng ở **NHIỀU NƠI quan trọng**:

### 1. **Kiểm tra quyền trong Middleware** (`CheckPermission.php`)
```php
// routes/web.php
Route::middleware('permission:co-so,can_view')  // ← "co-so" chính là code
```

### 2. **Model User - kiểm tra permission** (`User.php`)

```124:142:c:\laragon\www\QLCSVC\qlcsvc_react - Copy\app\Models\User.php
    public function hasPermission($screenCode, $permission = 'can_view')
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return true;
        }

        $screen = Screen::where('code', $screenCode)->first();  // ← Dùng code để tìm screen
        
        if (!$screen) {
            return false;
        }
        // ...
    }
```

### 3. **Lưu key cho permissions** (`HandleInertiaRequests.php`)
```php
$permissions[$screen->code] = [  // ← code làm key
    'can_view' => true,
    'can_create' => true,
    // ...
];
```

### 4. **Menu key trong React** (`MainLayout.jsx`)
```jsx
key: screen.code,  // ← Ant Design Menu cần unique key
```

**⚠️ KẾT LUẬN:** Cột `code` **KHÔNG THỂ BỎ** - nó là định danh duy nhất để hệ thống kiểm tra quyền!

---

## 📊 CỘT `icon` - **ĐANG ĐƯỢC SỬ DỤNG** ✅

Bạn nói không thấy nhưng thực ra nó **ĐANG ĐƯỢC DÙNG** để hiển thị icon menu:

```82:82:c:\laragon\www\QLCSVC\qlcsvc_react - Copy\resources\js\components\Layout\MainLayout.jsx
            const icon = iconMap[screen.icon] || <AppstoreOutlined />;
```

**Luồng hoạt động:**
1. DB lưu: `icon = "BankOutlined"` (string)
2. Server gửi xuống client: `{ icon: "BankOutlined", ... }`
3. Client dùng `iconMap` để convert string → component: `iconMap["BankOutlined"]` → `<BankOutlined />`
4. Hiển thị icon trên menu sidebar

---

## 📋 TỔNG KẾT CÁC CỘT

| Cột | Sử dụng | Có thể bỏ? |
|-----|---------|------------|
| `id` | Primary key | ❌ Không |
| `name` | Hiển thị tên menu | ❌ Không |
| **`code`** | **Kiểm tra permission, menu key** | ❌ **KHÔNG** |
| **`route`** | **Tạo link điều hướng** | ❌ **KHÔNG** |
| **`icon`** | **Hiển thị icon menu** | ⚠️ Có thể (nếu muốn tất cả icon giống nhau) |
| `parent_id` | Cấu trúc cây | ❌ Không |
| `order` | Sắp xếp thứ tự | ⚠️ Có thể |
| `is_active` | Ẩn/hiện chức năng | ⚠️ Có thể |
| `is_menu` | Phân biệt menu/chức năng | ⚠️ Có thể |

---

## 💡 GỢI Ý TỐI ƯU

Nếu bạn muốn đơn giản hóa, có thể:

1. **Bỏ cột `icon`** → Tất cả menu dùng icon mặc định `<AppstoreOutlined />`
2. **Gộp `code` và `route`** → Nhưng sẽ phức tạp vì:
   - `code` cần unique cho TẤT CẢ screens (kể cả group không có route)
   - `route` có thể NULL (với group như "QL Cơ sở hạ tầng")

**Khuyến nghị:** Giữ nguyên cấu trúc hiện tại vì nó đã được thiết kế hợp lý! 👍

```php
// routes/web.php
Route::middleware('permission:co-so,can_view')  // ← "co-so" chính là code
```

```plaintext
    public function hasPermission($screenCode, $permission = 'can_view')
    {
        // Admin có tất cả quyền
        if ($this->isAdmin()) {
            return true;
        }

        $screen = Screen::where('code', $screenCode)->first();  // ← Dùng code để tìm screen
        
        if (!$screen) {
            return false;
        }
        // ...
    }
```

```php
$permissions[$screen->code] = [  // ← code làm key
    'can_view' => true,
    'can_create' => true,
    // ...
];
```

```javascriptreact
key: screen.code,  // ← Ant Design Menu cần unique key
```

```plaintext
            const icon = iconMap[screen.icon] || <AppstoreOutlined />;
```

---

**User**

bạn ơi hình như chức năng phân quyền bạn không viết theo kiểu hướng đối tượng giống các chức năng khác tôi đã viết từ trước hả

---

**Cursor**

Để tôi kiểm tra và so sánh cách viết của `PermissionController` với các controller khác trong dự án:

Bạn nói đúng! Tôi thấy sự khác biệt rõ ràng:

---

## 📊 SO SÁNH

### **CoSoController (cách của bạn - Service Pattern)** ✅

```5:26:c:\laragon\www\QLCSVC\qlcsvc_react - Copy\app\Http\Controllers\CoSoController.php
use App\Services\CoSoService;
use App\Http\Requests\CoSo\StoreCoSoRequest;
use App\Http\Requests\CoSo\UpdateCoSoRequest;
// ...
class CoSoController extends Controller
{
    protected $coSoService;

    public function __construct(CoSoService $coSoService)
    {
        $this->coSoService = $coSoService;
    }
    // ...
    public function store(StoreCoSoRequest $request)
    {
        $this->coSoService->create($request->validated());
    }
}
```

**Đặc điểm:**
- ✅ **Service Layer**: `CoSoService` xử lý business logic
- ✅ **Form Request**: `StoreCoSoRequest`, `UpdateCoSoRequest` validate riêng
- ✅ **Dependency Injection**: Inject service qua constructor
- ✅ **Tách biệt rõ ràng**: Controller chỉ điều phối, Service xử lý logic

---

### **PermissionController (cách tôi làm)** ❌

```php
// KHÔNG có Service
// KHÔNG có Form Request
// Logic nằm trực tiếp trong Controller
public function updateUserPermissions(Request $request, User $user)
{
    $request->validate([...]); // Validate trong controller
    $user->permissions()->delete(); // Gọi Model trực tiếp
    UserPermission::create([...]); // Không qua service
}
```

**Thiếu:**
- ❌ Không có `PermissionService`
- ❌ Không có `UpdatePermissionRequest`
- ❌ Logic business nằm trong controller
- ❌ Không nhất quán với các controller khác

---

## 🔧 BẠN MUỐN TÔI SỬA LẠI KHÔNG?

Tôi sẽ tạo:

1. **`app/Services/PermissionService.php`** - Xử lý business logic
2. **`app/Http/Requests/Permission/UpdatePermissionRequest.php`** - Validate request
3. **Sửa `PermissionController.php`** - Inject service, dùng form request

Bạn có muốn tôi refactor lại theo đúng chuẩn hướng đối tượng của bạn không?

```plaintext
use App\Services\CoSoService;
use App\Http\Requests\CoSo\StoreCoSoRequest;
use App\Http\Requests\CoSo\UpdateCoSoRequest;
// ...
class CoSoController extends Controller
{
    protected $coSoService;

    public function __construct(CoSoService $coSoService)
    {
        $this->coSoService = $coSoService;
    }
    // ...
    public function store(StoreCoSoRequest $request)
    {
        $this->coSoService->create($request->validated());
    }
}
```

```php
// KHÔNG có Service
// KHÔNG có Form Request
// Logic nằm trực tiếp trong Controller
public function updateUserPermissions(Request $request, User $user)
{
    $request->validate([...]); // Validate trong controller
    $user->permissions()->delete(); // Gọi Model trực tiếp
    UserPermission::create([...]); // Không qua service
}
```

---

**User**

tôi muốn bạn refactor lại theo hướng đối tượng giống các chức năng khác tôi đã làm á

---

**Cursor**

Tôi sẽ xem thêm cấu trúc Service và Request hiện có để làm theo đúng chuẩn của bạn:

Đã hiểu rõ pattern của bạn! Tôi sẽ refactor theo đúng chuẩn. Bắt đầu tạo các file:

```php
<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    /**
     * Lấy tất cả quyền của một user
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Lấy quyền của user theo screen
     *
     * @param int $userId
     * @param int $screenId
     * @return UserPermission|null
     */
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission;

    /**
     * Tạo quyền mới
     *
     * @param array $data
     * @return UserPermission
     */
    public function create(array $data): UserPermission;

    /**
     * Cập nhật quyền
     *
     * @param int $id
     * @param array $data
     * @return UserPermission
     */
    public function update(int $id, array $data): UserPermission;

    /**
     * Xóa quyền
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Xóa tất cả quyền của user
     *
     * @param int $userId
     * @return bool
     */
    public function deleteByUserId(int $userId): bool;

    /**
     * Cập nhật hàng loạt quyền cho user
     *
     * @param int $userId
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(int $userId, array $permissions): void;
}

```

```php
<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var UserPermission
     */
    protected $model;

    /**
     * PermissionRepository constructor.
     *
     * @param UserPermission $model
     */
    public function __construct(UserPermission $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('screen_id', $screenId)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): UserPermission
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): UserPermission
    {
        $permission = $this->model->findOrFail($id);
        $permission->update($data);
        return $permission->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $permission = $this->model->findOrFail($id);
        return $permission->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByUserId(int $userId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function syncPermissions(int $userId, array $permissions): void
    {
        // Xóa tất cả quyền cũ của user
        $this->model->where('user_id', $userId)->delete();

        // Thêm quyền mới
        foreach ($permissions as $permission) {
            // Chỉ tạo bản ghi nếu có ít nhất 1 quyền
            if ($this->hasAnyPermission($permission)) {
                $this->model->create([
                    'user_id' => $userId,
                    'screen_id' => $permission['screen_id'],
                    'can_view' => $permission['can_view'] ?? false,
                    'can_create' => $permission['can_create'] ?? false,
                    'can_edit' => $permission['can_edit'] ?? false,
                    'can_delete' => $permission['can_delete'] ?? false,
                ]);
            }
        }
    }

    /**
     * Kiểm tra có ít nhất 1 quyền không
     *
     * @param array $permission
     * @return bool
     */
    private function hasAnyPermission(array $permission): bool
    {
        return ($permission['can_view'] ?? false) ||
               ($permission['can_create'] ?? false) ||
               ($permission['can_edit'] ?? false) ||
               ($permission['can_delete'] ?? false);
    }
}

```

```php
<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\User;
use App\Models\Screen;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * @var PermissionRepositoryInterface
     */
    protected $permissionRepository;

    /**
     * PermissionService constructor.
     *
     * @param PermissionRepositoryInterface $permissionRepository
     */
    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Lấy danh sách users không phải admin
     *
     * @return Collection
     */
    public function getNonAdminUsers(): Collection
    {
        return User::where('role', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    /**
     * Lấy danh sách screens dạng tree
     *
     * @return Collection
     */
    public function getScreensTree(): Collection
    {
        return Screen::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with(['children' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order')
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->orderBy('order');
                    }]);
            }])
            ->get();
    }

    /**
     * Lấy danh sách screens dạng flat
     *
     * @return Collection
     */
    public function getScreensFlat(): Collection
    {
        return Screen::getFlatTree();
    }

    /**
     * Lấy quyền của một user theo dạng key-value
     *
     * @param User $user
     * @return array
     */
    public function getUserPermissions(User $user): array
    {
        $permissions = $this->permissionRepository->getByUserId($user->id);

        return $permissions->keyBy('screen_id')->map(function ($permission) {
            return [
                'can_view' => $permission->can_view,
                'can_create' => $permission->can_create,
                'can_edit' => $permission->can_edit,
                'can_delete' => $permission->can_delete,
            ];
        })->toArray();
    }

    /**
     * Cập nhật quyền của user
     *
     * @param User $user
     * @param array $permissions
     * @return void
     */
    public function updateUserPermissions(User $user, array $permissions): void
    {
        $this->permissionRepository->syncPermissions($user->id, $permissions);
    }
}

```

```php
<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'permissions' => 'required|array',
            'permissions.*.screen_id' => 'required|exists:screens,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_edit' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'permissions' => 'Danh sách quyền',
            'permissions.*.screen_id' => 'ID màn hình',
            'permissions.*.can_view' => 'Quyền xem',
            'permissions.*.can_create' => 'Quyền thêm',
            'permissions.*.can_edit' => 'Quyền sửa',
            'permissions.*.can_delete' => 'Quyền xóa',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'permissions.required' => 'Vui lòng cung cấp danh sách quyền.',
            'permissions.array' => 'Danh sách quyền phải là mảng.',
            'permissions.*.screen_id.required' => 'ID màn hình là bắt buộc.',
            'permissions.*.screen_id.exists' => 'Màn hình không tồn tại trong hệ thống.',
        ];
    }
}

```

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PermissionService;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * PermissionController constructor.
     *
     * @param PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Hiển thị trang phân quyền
     */
    public function index()
    {
        try {
            $users = $this->permissionService->getNonAdminUsers();
            $screens = $this->permissionService->getScreensTree();

            return Inertia::render('Permission/Index', [
                'users' => $users,
                'screens' => $screens,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải trang phân quyền: ' . $e->getMessage());
        }
    }

    /**
     * Lấy quyền của một user
     */
    public function getUserPermissions(User $user)
    {
        try {
            $permissions = $this->permissionService->getUserPermissions($user);
            return response()->json($permissions);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Không thể tải phân quyền'], 500);
        }
    }

    /**
     * Cập nhật quyền của một user
     */
    public function updateUserPermissions(UpdatePermissionRequest $request, User $user)
    {
        try {
            $this->permissionService->updateUserPermissions($user, $request->validated()['permissions']);
            return back()->with('success', 'Cập nhật phân quyền thành công!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi cập nhật phân quyền: ' . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách screens dạng flat để hiển thị dạng table
     */
    public function getScreensFlat()
    {
        try {
            $screens = $this->permissionService->getScreensFlat();
            return response()->json($screens);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Không thể tải danh sách màn hình'], 500);
        }
    }
}

```

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Contracts\Repositories\LichSuBaoDuongRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

// Repository Implementations
use App\Repositories\CoSoRepository;
use App\Repositories\KhuNhaRepository;
use App\Repositories\PhongRepository;
use App\Repositories\ThietBiRepository;
use App\Repositories\LichSuBaoDuongRepository;
use App\Repositories\PermissionRepository;

// Services
use App\Services\CoSoService;
use App\Services\KhuNhaService;
use App\Services\PhongService;
use App\Services\ThietBiService;
use App\Services\LichSuBaoDuongService;
use App\Services\DashboardService;
use App\Services\PermissionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Repository bindings
        $this->app->bind(CoSoRepositoryInterface::class, CoSoRepository::class);
        $this->app->bind(KhuNhaRepositoryInterface::class, KhuNhaRepository::class);
        $this->app->bind(PhongRepositoryInterface::class, PhongRepository::class);
        $this->app->bind(ThietBiRepositoryInterface::class, ThietBiRepository::class);
        $this->app->bind(LichSuBaoDuongRepositoryInterface::class, LichSuBaoDuongRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

        // Services: bind concrete to itself for clarity (optional, container can resolve automatically)
        $this->app->bind(CoSoService::class, CoSoService::class);
        $this->app->bind(KhuNhaService::class, KhuNhaService::class);
        $this->app->bind(PhongService::class, PhongService::class);
        $this->app->bind(ThietBiService::class, ThietBiService::class);
        $this->app->bind(LichSuBaoDuongService::class, LichSuBaoDuongService::class);
        $this->app->bind(DashboardService::class, DashboardService::class);
        $this->app->bind(PermissionService::class, PermissionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

```

Hoàn tất! Để đảm bảo code hoạt động, tôi sẽ chạy test PHP syntax:

Hoàn tất refactor! ✅

---

## 📁 CÁC FILE ĐÃ TẠO/SỬA

### **1. Interface** - `app/Contracts/Repositories/PermissionRepositoryInterface.php`
```php
interface PermissionRepositoryInterface
{
    public function getByUserId(int $userId): Collection;
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission;
    public function create(array $data): UserPermission;
    public function update(int $id, array $data): UserPermission;
    public function delete(int $id): bool;
    public function deleteByUserId(int $userId): bool;
    public function syncPermissions(int $userId, array $permissions): void;
}
```

### **2. Repository** - `app/Repositories/PermissionRepository.php`
- Triển khai interface
- Xử lý trực tiếp với Model `UserPermission`
- Phương thức `syncPermissions()` xử lý cập nhật hàng loạt

### **3. Service** - `app/Services/PermissionService.php`
- Inject `PermissionRepositoryInterface`
- Business logic: `getNonAdminUsers()`, `getScreensTree()`, `getUserPermissions()`, `updateUserPermissions()`

### **4. Form Request** - `app/Http/Requests/Permission/UpdatePermissionRequest.php`
- Validation rules riêng
- Custom messages và attributes tiếng Việt

### **5. Controller** - `app/Http/Controllers/PermissionController.php` (refactored)
```php
class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;  // ← Dependency Injection
    }

    public function updateUserPermissions(UpdatePermissionRequest $request, User $user)
    {
        $this->permissionService->updateUserPermissions($user, $request->validated()['permissions']);
        // ...
    }
}
```

### **6. ServiceProvider** - `app/Providers/AppServiceProvider.php` (updated)
```php
// Thêm binding mới
$this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
$this->app->bind(PermissionService::class, PermissionService::class);
```

---

## 📊 SO SÁNH TRƯỚC VÀ SAU

| Tiêu chí | Trước | Sau |
|----------|-------|-----|
| Service Layer | ❌ Không | ✅ `PermissionService` |
| Repository | ❌ Không | ✅ `PermissionRepository` |
| Form Request | ❌ Validate trong controller | ✅ `UpdatePermissionRequest` |
| Dependency Injection | ❌ Không | ✅ Inject qua constructor |
| Error Handling | ❌ Không có try-catch | ✅ Có try-catch |
| Tách biệt concerns | ❌ Logic trong controller | ✅ Tách riêng từng layer |

---

## 🧪 KIỂM TRA

Cache đã được clear. Bạn có thể test lại chức năng phân quyền trên trình duyệt để đảm bảo mọi thứ hoạt động bình thường! 👍

```php
interface PermissionRepositoryInterface
{
    public function getByUserId(int $userId): Collection;
    public function getByUserAndScreen(int $userId, int $screenId): ?UserPermission;
    public function create(array $data): UserPermission;
    public function update(int $id, array $data): UserPermission;
    public function delete(int $id): bool;
    public function deleteByUserId(int $userId): bool;
    public function syncPermissions(int $userId, array $permissions): void;
}
```

```php
class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;  // ← Dependency Injection
    }

    public function updateUserPermissions(UpdatePermissionRequest $request, User $user)
    {
        $this->permissionService->updateUserPermissions($user, $request->validated()['permissions']);
        // ...
    }
}
```

```php
// Thêm binding mới
$this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
$this->app->bind(PermissionService::class, PermissionService::class);
```

---

**User**

ok tốt rồi mà bạn xem lại chức năng đăng nhập có làm theo hướng đối tượng luôn chưa nha nếu chưa bạn refactor giúp tôi luôn nhé

---

**Cursor**

Để tôi kiểm tra `AuthController` hiện tại:

Đúng rồi! `AuthController` chưa theo hướng đối tượng:
- ✅ Đã có `LoginRequest` 
- ❌ Chưa có `AuthService`
- ❌ Logic nằm trực tiếp trong Controller

Tôi sẽ refactor ngay:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthService
{
    /**
     * Xử lý đăng nhập
     *
     * @param array $credentials
     * @param bool $remember
     * @param Request $request
     * @return bool
     */
    public function login(array $credentials, bool $remember, Request $request): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return true;
        }

        return false;
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Lấy user đang đăng nhập
     *
     * @return \App\Models\User|null
     */
    public function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * Kiểm tra user đã đăng nhập chưa
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Lấy ID của user đang đăng nhập
     *
     * @return int|null
     */
    public function getCurrentUserId(): ?int
    {
        return Auth::id();
    }
}

```

```php
<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị form đăng nhập
     *
     * @return \Inertia\Response
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Xử lý đăng nhập
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if ($this->authService->login($credentials, $remember, $request)) {
                return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
            }

            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput($request->only('email', 'remember'));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.',
            ])->withInput($request->only('email', 'remember'));
        }
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request);
            return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Có lỗi xảy ra khi đăng xuất.');
        }
    }
}

```

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Interfaces
use App\Contracts\Repositories\CoSoRepositoryInterface;
use App\Contracts\Repositories\KhuNhaRepositoryInterface;
use App\Contracts\Repositories\PhongRepositoryInterface;
use App\Contracts\Repositories\ThietBiRepositoryInterface;
use App\Contracts\Repositories\LichSuBaoDuongRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;

// Repository Implementations
use App\Repositories\CoSoRepository;
use App\Repositories\KhuNhaRepository;
use App\Repositories\PhongRepository;
use App\Repositories\ThietBiRepository;
use App\Repositories\LichSuBaoDuongRepository;
use App\Repositories\PermissionRepository;

// Services
use App\Services\CoSoService;
use App\Services\KhuNhaService;
use App\Services\PhongService;
use App\Services\ThietBiService;
use App\Services\LichSuBaoDuongService;
use App\Services\DashboardService;
use App\Services\PermissionService;
use App\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register Repository bindings
        $this->app->bind(CoSoRepositoryInterface::class, CoSoRepository::class);
        $this->app->bind(KhuNhaRepositoryInterface::class, KhuNhaRepository::class);
        $this->app->bind(PhongRepositoryInterface::class, PhongRepository::class);
        $this->app->bind(ThietBiRepositoryInterface::class, ThietBiRepository::class);
        $this->app->bind(LichSuBaoDuongRepositoryInterface::class, LichSuBaoDuongRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

        // Services: bind concrete to itself for clarity (optional, container can resolve automatically)
        $this->app->bind(CoSoService::class, CoSoService::class);
        $this->app->bind(KhuNhaService::class, KhuNhaService::class);
        $this->app->bind(PhongService::class, PhongService::class);
        $this->app->bind(ThietBiService::class, ThietBiService::class);
        $this->app->bind(LichSuBaoDuongService::class, LichSuBaoDuongService::class);
        $this->app->bind(DashboardService::class, DashboardService::class);
        $this->app->bind(PermissionService::class, PermissionService::class);
        $this->app->bind(AuthService::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

```

Hoàn tất refactor Auth! ✅

---

## 📁 CÁC FILE ĐÃ TẠO/SỬA

### **1. Service** - `app/Services/AuthService.php` (mới)

```php
class AuthService
{
    public function login(array $credentials, bool $remember, Request $request): bool;
    public function logout(Request $request): void;
    public function getCurrentUser();
    public function isAuthenticated(): bool;
    public function getCurrentUserId(): ?int;
}
```

### **2. Controller** - `app/Http/Controllers/AuthController.php` (refactored)

```php
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;  // ← Dependency Injection
    }

    public function login(LoginRequest $request)
    {
        // Dùng service thay vì gọi Auth:: trực tiếp
        if ($this->authService->login($credentials, $remember, $request)) {
            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }
        // ...
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        // ...
    }
}
```

### **3. ServiceProvider** - `app/Providers/AppServiceProvider.php` (updated)

```php
use App\Services\AuthService;

// Thêm dòng này
$this->app->bind(AuthService::class, AuthService::class);
```

---

## 📊 SO SÁNH TRƯỚC VÀ SAU

| Tiêu chí | Trước | Sau |
|----------|-------|-----|
| Service Layer | ❌ | ✅ `AuthService` |
| Form Request | ✅ `LoginRequest` | ✅ `LoginRequest` |
| Dependency Injection | ❌ | ✅ |
| Error Handling | ❌ Không có try-catch | ✅ Có try-catch |
| Tách biệt concerns | ❌ Logic trong controller | ✅ Logic trong service |

---

## 📋 TỔNG KẾT CẤU TRÚC DỰ ÁN

Bây giờ tất cả các module chính đều theo chuẩn hướng đối tượng:

| Module | Service | Repository | Form Request |
|--------|---------|------------|--------------|
| CoSo | ✅ | ✅ | ✅ |
| KhuNha | ✅ | ✅ | ✅ |
| Phong | ✅ | ✅ | ✅ |
| ThietBi | ✅ | ✅ | ✅ |
| LichSuBaoDuong | ✅ | ✅ | ✅ |
| Dashboard | ✅ | - | - |
| **Permission** | ✅ | ✅ | ✅ |
| **Auth** | ✅ | - | ✅ |

> **Lưu ý:** Auth không cần Repository vì không làm việc CRUD với database - nó dùng Laravel Auth Facade.

Bạn có thể test lại chức năng đăng nhập/đăng xuất trên trình duyệt! 👍

```php
class AuthService
{
    public function login(array $credentials, bool $remember, Request $request): bool;
    public function logout(Request $request): void;
    public function getCurrentUser();
    public function isAuthenticated(): bool;
    public function getCurrentUserId(): ?int;
}
```

```php
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;  // ← Dependency Injection
    }

    public function login(LoginRequest $request)
    {
        // Dùng service thay vì gọi Auth:: trực tiếp
        if ($this->authService->login($credentials, $remember, $request)) {
            return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
        }
        // ...
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        // ...
    }
}
```

```php
use App\Services\AuthService;

// Thêm dòng này
$this->app->bind(AuthService::class, AuthService::class);
```

---

**User**

bạn giải thích cho tôi cơ chế hoạt động của chức năng phân quyền sau khi chuyển sang oop đi bạn ví dụ như khi tích chọn 1 quyền cụ thể rồi sẽ đi từ đâu tới đâu trong các cái models services repository controller.. và middleware xác thực các route kiểu gì đi bạn

---

**Cursor**

Để giải thích chi tiết, tôi cần đọc các file liên quan:

Tuyệt vời! Tôi sẽ giải thích chi tiết **2 luồng chính**:

---

# 🔄 LUỒNG 1: LƯU PHÂN QUYỀN (Khi Admin tick chọn quyền và bấm Lưu)

## Sơ đồ tổng quan:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              FRONTEND (React)                                   │
│  ┌──────────────────────────────────────────────────────────────────────────┐   │
│  │  Permission/Index.jsx                                                     │   │
│  │  ─────────────────────                                                    │   │
│  │  1. Admin tick checkbox "Xem" cho "Danh sách cơ sở"                      │   │
│  │  2. State permissions cập nhật: { 3: { can_view: true, ... } }           │   │
│  │  3. Click "Lưu phân quyền"                                               │   │
│  │  4. Gọi: router.post('/phan-quyen/5/permissions', { permissions: [...] })│   │
│  └──────────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              BACKEND (Laravel)                                  │
│                                                                                 │
│  ┌─────────────────────┐                                                        │
│  │   routes/web.php    │  Route::post('/phan-quyen/{user}/permissions')         │
│  │                     │  → middleware: auth, permission:phan-quyen,can_edit    │
│  └─────────┬───────────┘                                                        │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  UpdatePermissionRequest (Form Request)                                 │    │
│  │  ────────────────────────────────────────                               │    │
│  │  - Validate: permissions.*.screen_id exists in screens table            │    │
│  │  - Validate: permissions.*.can_view, can_create... là boolean           │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │ Validation OK                                                      │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionController                                                    │    │
│  │  ──────────────────────                                                  │    │
│  │  public function updateUserPermissions(UpdatePermissionRequest $request) │    │
│  │  {                                                                       │    │
│  │      $this->permissionService->updateUserPermissions(                    │    │
│  │          $user,                                                          │    │
│  │          $request->validated()['permissions']                            │    │
│  │      );                                                                   │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionService                                                       │    │
│  │  ─────────────────────                                                   │    │
│  │  public function updateUserPermissions(User $user, array $permissions)   │    │
│  │  {                                                                       │    │
│  │      $this->permissionRepository->syncPermissions($user->id, $perms);    │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionRepository                                                    │    │
│  │  ────────────────────────                                                │    │
│  │  public function syncPermissions($userId, $permissions)                  │    │
│  │  {                                                                       │    │
│  │      // 1. Xóa tất cả quyền cũ của user                                 │    │
│  │      $this->model->where('user_id', $userId)->delete();                  │    │
│  │                                                                          │    │
│  │      // 2. Tạo mới từng quyền                                           │    │
│  │      foreach ($permissions as $perm) {                                   │    │
│  │          if ($this->hasAnyPermission($perm)) {                          │    │
│  │              $this->model->create([...]);                                │    │
│  │          }                                                               │    │
│  │      }                                                                   │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  UserPermission Model → Database (user_permissions table)                │    │
│  │  ───────────────────────────────────────────────────────                 │    │
│  │  INSERT INTO user_permissions (user_id, screen_id, can_view, ...)        │    │
│  │  VALUES (5, 3, true, false, false, false)                                │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## Chi tiết từng bước:

### **Bước 1: Frontend gửi request**
```jsx
// Permission/Index.jsx
const handleSave = () => {
    const permissionsArray = Object.entries(permissions).map(([screenId, perms]) => ({
        screen_id: parseInt(screenId),
        ...perms,  // { can_view: true, can_create: false, ... }
    }));

    router.post(`/phan-quyen/${selectedUserId}/permissions`, { 
        permissions: permissionsArray 
    });
};
```

**Data gửi đi:**
```json
{
    "permissions": [
        { "screen_id": 3, "can_view": true, "can_create": false, "can_edit": false, "can_delete": false },
        { "screen_id": 9, "can_view": true, "can_create": true, "can_edit": true, "can_delete": false }
    ]
}
```

### **Bước 2: Route nhận request**
```php
// routes/web.php
Route::middleware('permission:phan-quyen,can_edit')->group(function () {
    Route::post('/phan-quyen/{user}/permissions', [PermissionController::class, 'updateUserPermissions']);
});
```

### **Bước 3: Form Request validate**
```php
// UpdatePermissionRequest.php
public function rules()
{
    return [
        'permissions' => 'required|array',
        'permissions.*.screen_id' => 'required|exists:screens,id',
        'permissions.*.can_view' => 'boolean',
        // ...
    ];
}
```

### **Bước 4: Controller gọi Service**
```php
// PermissionController.php
public function updateUserPermissions(UpdatePermissionRequest $request, User $user)
{
    $this->permissionService->updateUserPermissions($user, $request->validated()['permissions']);
    return back()->with('success', 'Cập nhật phân quyền thành công!');
}
```

### **Bước 5: Service gọi Repository**
```php
// PermissionService.php
public function updateUserPermissions(User $user, array $permissions): void
{
    $this->permissionRepository->syncPermissions($user->id, $permissions);
}
```

### **Bước 6: Repository thao tác với Model/Database**
```php
// PermissionRepository.php
public function syncPermissions(int $userId, array $permissions): void
{
    // Xóa quyền cũ
    $this->model->where('user_id', $userId)->delete();

    // Tạo quyền mới
    foreach ($permissions as $permission) {
        if ($this->hasAnyPermission($permission)) {
            $this->model->create([
                'user_id' => $userId,
                'screen_id' => $permission['screen_id'],
                'can_view' => $permission['can_view'] ?? false,
                // ...
            ]);
        }
    }
}
```

---

# 🔒 LUỒNG 2: KIỂM TRA QUYỀN (Khi user truy cập route)

## Sơ đồ:

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  User click menu "Danh sách cơ sở" → Request: GET /co-so                        │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  routes/web.php                                                                 │
│  ─────────────────                                                              │
│  Route::middleware('permission:co-so,can_view')->group(function () {            │
│      Route::get('/co-so', [CoSoController::class, 'index']);                    │
│  });                                                                            │
│                                                                                 │
│  → Gọi middleware: permission với params: screenCode="co-so", permission="can_view" │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  CheckPermission Middleware                                                     │
│  ─────────────────────────────                                                  │
│  public function handle($request, $next, $screenCode, $permission)              │
│  {                                                                              │
│      $user = $request->user();  // Lấy user đang login                          │
│                                                                                 │
│      // Admin bypass tất cả                                                     │
│      if ($user->isAdmin()) {                                                    │
│          return $next($request);  ─────────────────────────→ ✅ CHO QUA         │
│      }                                                                          │
│                                                                                 │
│      // Kiểm tra quyền                                                          │
│      if (!$user->hasPermission($screenCode, $permission)) {                     │
│          abort(403);  ─────────────────────────────────────→ ❌ TỪ CHỐI         │
│      }                                                                          │
│                                                                                 │
│      return $next($request);  ─────────────────────────────→ ✅ CHO QUA         │
│  }                                                                              │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  User Model - hasPermission()                                                   │
│  ────────────────────────────────                                               │
│  public function hasPermission($screenCode, $permission)                        │
│  {                                                                              │
│      // 1. Tìm screen theo code                                                 │
│      $screen = Screen::where('code', $screenCode)->first();                     │
│      // screenCode = "co-so" → screen.id = 3                                    │
│                                                                                 │
│      // 2. Tìm permission của user cho screen này                               │
│      $userPermission = $this->permissions()                                     │
│          ->where('screen_id', $screen->id)                                      │
│          ->first();                                                             │
│                                                                                 │
│      // 3. Trả về giá trị của quyền cần kiểm tra                               │
│      return $userPermission && $userPermission->{$permission};                  │
│      // $permission = "can_view" → return $userPermission->can_view             │
│  }                                                                              │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  Database Query                                                                 │
│  ──────────────────                                                             │
│  SELECT * FROM screens WHERE code = 'co-so' LIMIT 1;                            │
│  → Kết quả: { id: 3, code: 'co-so', name: 'Danh sách cơ sở', ... }              │
│                                                                                 │
│  SELECT * FROM user_permissions WHERE user_id = 5 AND screen_id = 3 LIMIT 1;    │
│  → Kết quả: { can_view: true, can_create: false, can_edit: false, ... }         │
│                                                                                 │
│  → can_view = true → ✅ CHO QUA                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  CoSoController::index()                                                        │
│  → Hiển thị trang danh sách cơ sở                                               │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

# 📊 TỔNG KẾT CẤU TRÚC OOP

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                            CẤU TRÚC LAYERED ARCHITECTURE                     │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ┌─────────────────┐                                                        │
│   │   PRESENTATION  │  React Components (Permission/Index.jsx)               │
│   │      LAYER      │  Giao diện người dùng                                  │
│   └────────┬────────┘                                                        │
│            │ HTTP Request/Response                                           │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │   CONTROLLER    │  PermissionController                                  │
│   │      LAYER      │  - Nhận request, gọi service, trả response             │
│   │                 │  - Sử dụng Form Request để validate                    │
│   └────────┬────────┘                                                        │
│            │ Gọi phương thức                                                 │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │    SERVICE      │  PermissionService                                     │
│   │     LAYER       │  - Xử lý business logic                                │
│   │                 │  - Điều phối giữa controller và repository             │
│   └────────┬────────┘                                                        │
│            │ Gọi phương thức                                                 │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │   REPOSITORY    │  PermissionRepository                                  │
│   │     LAYER       │  - Thao tác với database                               │
│   │                 │  - CRUD operations                                     │
│   └────────┬────────┘                                                        │
│            │ Eloquent ORM                                                    │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │     MODEL       │  UserPermission, Screen, User                          │
│   │     LAYER       │  - Định nghĩa bảng, quan hệ                            │
│   │                 │  - Helper methods (hasPermission, isAdmin...)          │
│   └────────┬────────┘                                                        │
│            │ SQL Query                                                       │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │    DATABASE     │  MySQL: screens, user_permissions, users               │
│   └─────────────────┘                                                        │
│                                                                              │
├──────────────────────────────────────────────────────────────────────────────┤
│                            MIDDLEWARE (Cross-cutting)                        │
├──────────────────────────────────────────────────────────────────────────────┤
│   CheckPermission Middleware                                                 │
│   - Chặn request trước khi vào Controller                                    │
│   - Kiểm tra quyền bằng User::hasPermission()                               │
│   - Admin bypass tất cả                                                      │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

# 🔗 DEPENDENCY INJECTION FLOW

```php
// 1. AppServiceProvider đăng ký binding
$this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

// 2. Khi Laravel khởi tạo PermissionService
class PermissionService {
    public function __construct(PermissionRepositoryInterface $permissionRepository)
    // Laravel tự động inject PermissionRepository vào đây
}

// 3. Khi Laravel khởi tạo PermissionController
class PermissionController {
    public function __construct(PermissionService $permissionService)
    // Laravel tự động inject PermissionService vào đây
}
```

---

Bạn có muốn tôi giải thích thêm phần nào không? 😊

```plaintext
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              FRONTEND (React)                                   │
│  ┌──────────────────────────────────────────────────────────────────────────┐   │
│  │  Permission/Index.jsx                                                     │   │
│  │  ─────────────────────                                                    │   │
│  │  1. Admin tick checkbox "Xem" cho "Danh sách cơ sở"                      │   │
│  │  2. State permissions cập nhật: { 3: { can_view: true, ... } }           │   │
│  │  3. Click "Lưu phân quyền"                                               │   │
│  │  4. Gọi: router.post('/phan-quyen/5/permissions', { permissions: [...] })│   │
│  └──────────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              BACKEND (Laravel)                                  │
│                                                                                 │
│  ┌─────────────────────┐                                                        │
│  │   routes/web.php    │  Route::post('/phan-quyen/{user}/permissions')         │
│  │                     │  → middleware: auth, permission:phan-quyen,can_edit    │
│  └─────────┬───────────┘                                                        │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  UpdatePermissionRequest (Form Request)                                 │    │
│  │  ────────────────────────────────────────                               │    │
│  │  - Validate: permissions.*.screen_id exists in screens table            │    │
│  │  - Validate: permissions.*.can_view, can_create... là boolean           │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │ Validation OK                                                      │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionController                                                    │    │
│  │  ──────────────────────                                                  │    │
│  │  public function updateUserPermissions(UpdatePermissionRequest $request) │    │
│  │  {                                                                       │    │
│  │      $this->permissionService->updateUserPermissions(                    │    │
│  │          $user,                                                          │    │
│  │          $request->validated()['permissions']                            │    │
│  │      );                                                                   │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionService                                                       │    │
│  │  ─────────────────────                                                   │    │
│  │  public function updateUserPermissions(User $user, array $permissions)   │    │
│  │  {                                                                       │    │
│  │      $this->permissionRepository->syncPermissions($user->id, $perms);    │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  PermissionRepository                                                    │    │
│  │  ────────────────────────                                                │    │
│  │  public function syncPermissions($userId, $permissions)                  │    │
│  │  {                                                                       │    │
│  │      // 1. Xóa tất cả quyền cũ của user                                 │    │
│  │      $this->model->where('user_id', $userId)->delete();                  │    │
│  │                                                                          │    │
│  │      // 2. Tạo mới từng quyền                                           │    │
│  │      foreach ($permissions as $perm) {                                   │    │
│  │          if ($this->hasAnyPermission($perm)) {                          │    │
│  │              $this->model->create([...]);                                │    │
│  │          }                                                               │    │
│  │      }                                                                   │    │
│  │  }                                                                       │    │
│  └─────────┬───────────────────────────────────────────────────────────────┘    │
│            │                                                                    │
│            ▼                                                                    │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  UserPermission Model → Database (user_permissions table)                │    │
│  │  ───────────────────────────────────────────────────────                 │    │
│  │  INSERT INTO user_permissions (user_id, screen_id, can_view, ...)        │    │
│  │  VALUES (5, 3, true, false, false, false)                                │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────┘
```

```javascriptreact
// Permission/Index.jsx
const handleSave = () => {
    const permissionsArray = Object.entries(permissions).map(([screenId, perms]) => ({
        screen_id: parseInt(screenId),
        ...perms,  // { can_view: true, can_create: false, ... }
    }));

    router.post(`/phan-quyen/${selectedUserId}/permissions`, { 
        permissions: permissionsArray 
    });
};
```

```json
{
    "permissions": [
        { "screen_id": 3, "can_view": true, "can_create": false, "can_edit": false, "can_delete": false },
        { "screen_id": 9, "can_view": true, "can_create": true, "can_edit": true, "can_delete": false }
    ]
}
```

```php
// routes/web.php
Route::middleware('permission:phan-quyen,can_edit')->group(function () {
    Route::post('/phan-quyen/{user}/permissions', [PermissionController::class, 'updateUserPermissions']);
});
```

```php
// UpdatePermissionRequest.php
public function rules()
{
    return [
        'permissions' => 'required|array',
        'permissions.*.screen_id' => 'required|exists:screens,id',
        'permissions.*.can_view' => 'boolean',
        // ...
    ];
}
```

```php
// PermissionController.php
public function updateUserPermissions(UpdatePermissionRequest $request, User $user)
{
    $this->permissionService->updateUserPermissions($user, $request->validated()['permissions']);
    return back()->with('success', 'Cập nhật phân quyền thành công!');
}
```

```php
// PermissionService.php
public function updateUserPermissions(User $user, array $permissions): void
{
    $this->permissionRepository->syncPermissions($user->id, $permissions);
}
```

```php
// PermissionRepository.php
public function syncPermissions(int $userId, array $permissions): void
{
    // Xóa quyền cũ
    $this->model->where('user_id', $userId)->delete();

    // Tạo quyền mới
    foreach ($permissions as $permission) {
        if ($this->hasAnyPermission($permission)) {
            $this->model->create([
                'user_id' => $userId,
                'screen_id' => $permission['screen_id'],
                'can_view' => $permission['can_view'] ?? false,
                // ...
            ]);
        }
    }
}
```

```plaintext
┌─────────────────────────────────────────────────────────────────────────────────┐
│  User click menu "Danh sách cơ sở" → Request: GET /co-so                        │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  routes/web.php                                                                 │
│  ─────────────────                                                              │
│  Route::middleware('permission:co-so,can_view')->group(function () {            │
│      Route::get('/co-so', [CoSoController::class, 'index']);                    │
│  });                                                                            │
│                                                                                 │
│  → Gọi middleware: permission với params: screenCode="co-so", permission="can_view" │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  CheckPermission Middleware                                                     │
│  ─────────────────────────────                                                  │
│  public function handle($request, $next, $screenCode, $permission)              │
│  {                                                                              │
│      $user = $request->user();  // Lấy user đang login                          │
│                                                                                 │
│      // Admin bypass tất cả                                                     │
│      if ($user->isAdmin()) {                                                    │
│          return $next($request);  ─────────────────────────→ ✅ CHO QUA         │
│      }                                                                          │
│                                                                                 │
│      // Kiểm tra quyền                                                          │
│      if (!$user->hasPermission($screenCode, $permission)) {                     │
│          abort(403);  ─────────────────────────────────────→ ❌ TỪ CHỐI         │
│      }                                                                          │
│                                                                                 │
│      return $next($request);  ─────────────────────────────→ ✅ CHO QUA         │
│  }                                                                              │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  User Model - hasPermission()                                                   │
│  ────────────────────────────────                                               │
│  public function hasPermission($screenCode, $permission)                        │
│  {                                                                              │
│      // 1. Tìm screen theo code                                                 │
│      $screen = Screen::where('code', $screenCode)->first();                     │
│      // screenCode = "co-so" → screen.id = 3                                    │
│                                                                                 │
│      // 2. Tìm permission của user cho screen này                               │
│      $userPermission = $this->permissions()                                     │
│          ->where('screen_id', $screen->id)                                      │
│          ->first();                                                             │
│                                                                                 │
│      // 3. Trả về giá trị của quyền cần kiểm tra                               │
│      return $userPermission && $userPermission->{$permission};                  │
│      // $permission = "can_view" → return $userPermission->can_view             │
│  }                                                                              │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  Database Query                                                                 │
│  ──────────────────                                                             │
│  SELECT * FROM screens WHERE code = 'co-so' LIMIT 1;                            │
│  → Kết quả: { id: 3, code: 'co-so', name: 'Danh sách cơ sở', ... }              │
│                                                                                 │
│  SELECT * FROM user_permissions WHERE user_id = 5 AND screen_id = 3 LIMIT 1;    │
│  → Kết quả: { can_view: true, can_create: false, can_edit: false, ... }         │
│                                                                                 │
│  → can_view = true → ✅ CHO QUA                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│  CoSoController::index()                                                        │
│  → Hiển thị trang danh sách cơ sở                                               │
└─────────────────────────────────────────────────────────────────────────────────┘
```

```plaintext
┌──────────────────────────────────────────────────────────────────────────────┐
│                            CẤU TRÚC LAYERED ARCHITECTURE                     │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ┌─────────────────┐                                                        │
│   │   PRESENTATION  │  React Components (Permission/Index.jsx)               │
│   │      LAYER      │  Giao diện người dùng                                  │
│   └────────┬────────┘                                                        │
│            │ HTTP Request/Response                                           │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │   CONTROLLER    │  PermissionController                                  │
│   │      LAYER      │  - Nhận request, gọi service, trả response             │
│   │                 │  - Sử dụng Form Request để validate                    │
│   └────────┬────────┘                                                        │
│            │ Gọi phương thức                                                 │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │    SERVICE      │  PermissionService                                     │
│   │     LAYER       │  - Xử lý business logic                                │
│   │                 │  - Điều phối giữa controller và repository             │
│   └────────┬────────┘                                                        │
│            │ Gọi phương thức                                                 │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │   REPOSITORY    │  PermissionRepository                                  │
│   │     LAYER       │  - Thao tác với database                               │
│   │                 │  - CRUD operations                                     │
│   └────────┬────────┘                                                        │
│            │ Eloquent ORM                                                    │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │     MODEL       │  UserPermission, Screen, User                          │
│   │     LAYER       │  - Định nghĩa bảng, quan hệ                            │
│   │                 │  - Helper methods (hasPermission, isAdmin...)          │
│   └────────┬────────┘                                                        │
│            │ SQL Query                                                       │
│            ▼                                                                 │
│   ┌─────────────────┐                                                        │
│   │    DATABASE     │  MySQL: screens, user_permissions, users               │
│   └─────────────────┘                                                        │
│                                                                              │
├──────────────────────────────────────────────────────────────────────────────┤
│                            MIDDLEWARE (Cross-cutting)                        │
├──────────────────────────────────────────────────────────────────────────────┤
│   CheckPermission Middleware                                                 │
│   - Chặn request trước khi vào Controller                                    │
│   - Kiểm tra quyền bằng User::hasPermission()                               │
│   - Admin bypass tất cả                                                      │
└──────────────────────────────────────────────────────────────────────────────┘
```

```php
// 1. AppServiceProvider đăng ký binding
$this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);

// 2. Khi Laravel khởi tạo PermissionService
class PermissionService {
    public function __construct(PermissionRepositoryInterface $permissionRepository)
    // Laravel tự động inject PermissionRepository vào đây
}

// 3. Khi Laravel khởi tạo PermissionController
class PermissionController {
    public function __construct(PermissionService $permissionService)
    // Laravel tự động inject PermissionService vào đây
}
```

