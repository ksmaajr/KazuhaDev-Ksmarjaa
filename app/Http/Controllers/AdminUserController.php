<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminRequest;
use App\Models\LicenseEvent;
use App\Models\User;
use App\Support\ListFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ListFilters::read($request);
        $query = User::where('role', 'admin');
        ListFilters::search($query, $filters, ['name', 'email']);
        ListFilters::dates($query, $filters);
        ListFilters::sort($query, $filters);

        return view('admins', ['admins' => $query->paginate($filters['per_page'] ?? 10)->withQueryString()]);
    }

    public function store(StoreAdminRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $admin = new User($request->safe()->only(['name', 'email', 'password']));
            $admin->role = 'admin';
            $admin->save();
            LicenseEvent::create(['action' => 'admin_created', 'user_id' => $request->user()->id, 'ip_address' => $request->ip(), 'metadata' => ['target_user_id' => $admin->id, 'target_email' => $admin->email]]);
        });

        return redirect()->route('admins.index')->with('success', 'Akun administrator baru berhasil dibuat dengan role admin.');
    }
}
