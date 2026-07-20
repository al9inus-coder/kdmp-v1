<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserPasswordRequest;
use App\Models\User;
use App\Support\SessionRevoker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Role yang boleh dipilih melalui form (Super Admin hanya via seeder). */
    private const ASSIGNABLE_ROLES = ['Admin', 'Kabid', 'Staff'];

    public function index(Request $request): View
    {
        $search = $request->q;
        $role = $request->role;
        $status = $request->status;

        $users = User::with('roles')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', $role)))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', (int) $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->pluck('name');

        return view('admin.users.index', compact('users', 'search', 'role', 'status', 'roles'));
    }

    public function create(): View
    {
        $user = new User(['is_active' => true]);
        $roles = self::ASSIGNABLE_ROLES;

        return view('admin.users.create', compact('user', 'roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => (bool) $data['is_active'],
        ]);

        $user->assignRole($data['role']);

        $this->recordAudit('user.created', $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" berhasil ditambahkan.");
    }

    public function show(User $user): View
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = self::ASSIGNABLE_ROLES;

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Cegah menonaktifkan akun sendiri.
        if ($user->is($request->user()) && ! (bool) $data['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => (bool) $data['is_active'],
        ]);

        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        $this->recordAudit('user.updated', $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        $this->recordAudit('user.deleted', $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$name}\" berhasil dihapus.");
    }

    public function editPassword(User $user): View
    {
        return view('admin.users.reset-password', compact('user'));
    }

    public function updatePassword(UserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => Hash::make($request->validated()['password']),
            'remember_token' => Str::random(60),
        ]);

        SessionRevoker::revoke($user, $user->is($request->user()) ? $request->session()->getId() : null);

        $this->recordAudit('user.password_reset', $user);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Password untuk \"{$user->name}\" berhasil direset.");
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            SessionRevoker::revoke($user);
        }

        $this->recordAudit($user->is_active ? 'user.activated' : 'user.deactivated', $user);

        $state = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "User \"{$user->name}\" berhasil {$state}.");
    }

    /**
     * Struktur audit siap-pasang. Saat ini menulis ke log aplikasi.
     *
     * TODO: ganti ke package activity log (mis. spatie/laravel-activitylog)
     *       ketika modul audit resmi dipasang, tanpa mengubah pemanggil.
     */
    private function recordAudit(string $action, User $target): void
    {
        Log::info('user.audit', [
            'action' => $action,
            'actor_id' => auth()->id(),
            'target_id' => $target->id,
            'target_email' => $target->email,
            'at' => now()->toDateTimeString(),
        ]);
    }
}
