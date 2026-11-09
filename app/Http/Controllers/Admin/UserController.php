<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search')) ?: null,
            'role_id' => $request->integer('role_id') ?: null,
        ];

        $users = User::query()
            ->with('role')
            ->where('organization_id', $request->user()->organization_id)
            ->when($filters['search'], function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'], fn ($query, int $roleId) => $query->where('role_id', $roleId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.create', [
            'roles' => Role::orderBy('name')->get(),
            'organization' => $request->user()->organization,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role_id' => $request->integer('role_id'),
            'organization_id' => $request->user()->organization_id,
            'status' => 'active',
            'password' => (string) $request->input('password'),
        ]);

        $this->logger->log(
            'user.created',
            $user,
            "Pengguna {$user->name} dibuat.",
            $request->user(),
            ['role_id' => $user->role_id],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('messages.user.created'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->assertOrganization($request, $user);

        return view('admin.users.edit', [
            'user' => $user->load('role'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->assertOrganization($request, $user);

        $previousRole = $user->role_id;

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role_id' => $request->integer('role_id'),
        ];

        if ($request->filled('password')) {
            $data['password'] = (string) $request->input('password');
        }

        $user->update($data);

        if ($previousRole !== $user->role_id) {
            $this->logger->log(
                'user.role_changed',
                $user,
                "Peran pengguna {$user->name} diubah.",
                $request->user(),
                ['from' => $previousRole, 'to' => $user->role_id],
            );
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('messages.user.updated'));
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->assertOrganization($request, $user);

        $user->update(['status' => 'suspended']);

        $this->logger->log('user.suspended', $user, "Akun {$user->name} ditangguhkan.", $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('messages.user.suspended'));
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $this->assertOrganization($request, $user);

        $user->update(['status' => 'active']);

        $this->logger->log('user.activated', $user, "Akun {$user->name} diaktifkan kembali.", $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('messages.user.activated'));
    }

    private function assertOrganization(Request $request, User $user): void
    {
        abort_unless($user->organization_id === $request->user()->organization_id, 404);
    }
}
