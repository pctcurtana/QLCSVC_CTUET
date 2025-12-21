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


