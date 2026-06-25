<?php

namespace App\Http\Controllers;

use App\Models\DotKiemTraThietBi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DotKiemTraThietBiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'trang_thai', 'per_page']);
        $query = DotKiemTraThietBi::query()->latest('id');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ten_dot', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (($filters['trang_thai'] ?? '') === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['trang_thai'] ?? '') === 'inactive') {
            $query->where('is_active', false);
        }

        $dotKiemTras = $query->paginate((int)$request->input('per_page', 10))->withQueryString();

        $stats = [
            'tong' => DotKiemTraThietBi::count(),
            'dang_active' => DotKiemTraThietBi::where('is_active', true)->count(),
            'chua_active' => DotKiemTraThietBi::where('is_active', false)->count(),
        ];

        return Inertia::render('DotKiemTraThietBi/Index', [
            'dotKiemTras' => $dotKiemTras,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_dot' => 'required|string|max:255',
            'ngay_bat_dau' => 'nullable|date',
            'ngay_ket_thuc' => 'nullable|date|after_or_equal:ngay_bat_dau',
            'mo_ta' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated) {
            if (!empty($validated['is_active'])) {
                DotKiemTraThietBi::query()->update(['is_active' => false]);
            }

            DotKiemTraThietBi::create(array_merge($validated, [
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'nguoi_tao_id' => auth()->id(),
            ]));
        });

        return back()->with('success', 'Tạo đợt kiểm tra thiết bị thành công!');
    }

    public function activate(DotKiemTraThietBi $dotKiemTraThietBi)
    {
        DB::transaction(function () use ($dotKiemTraThietBi) {
            DotKiemTraThietBi::query()->update(['is_active' => false]);
            $dotKiemTraThietBi->update(['is_active' => true]);
        });

        return back()->with('success', 'Đã kích hoạt đợt kiểm tra.');
    }

    public function destroy(DotKiemTraThietBi $dotKiemTraThietBi)
    {
        if ($dotKiemTraThietBi->is_active) {
            return back()->with('error', 'Không thể xóa đợt đang active.');
        }

        $dotKiemTraThietBi->delete();
        return back()->with('success', 'Đã xóa đợt kiểm tra.');
    }
}
