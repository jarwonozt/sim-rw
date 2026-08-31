<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiAccessUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Manajemen akses REST API (docs/api-guide.md) — Super Admin menambahkan
 * akun (baru atau yang sudah ada) untuk developer/integrasi, lalu
 * menerbitkan token Sanctum untuk akun tersebut dari satu tempat.
 */
class ApiAccessController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ROLES = ['super_admin', 'ketua_rw', 'sekretaris', 'bendahara', 'ketua_rt', 'warga'];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->withCount('tokens')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $selectedUser = null;
        $selectedUserTokens = [];

        if ($request->filled('user_id')) {
            $selectedUser = User::query()->find($request->integer('user_id'), ['id', 'name', 'email', 'role']);
            $selectedUserTokens = $selectedUser
                ? $selectedUser->tokens()->latest()->get(['id', 'name', 'last_used_at', 'created_at'])
                : [];
        }

        return Inertia::render('ApiAccess/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
            'roles' => self::ROLES,
            'selectedUser' => $selectedUser,
            'selectedUserTokens' => $selectedUserTokens,
            'newApiToken' => session('newApiToken'),
        ]);
    }

    public function storeUser(StoreApiAccessUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->except('password'),
            'password' => Hash::make($request->validated('password')),
            'is_active' => true,
        ]);

        return Redirect::route('api-access.index', ['user_id' => $user->id])
            ->with('success', "Akun \"{$user->name}\" berhasil dibuat.");
    }

    public function storeToken(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $token = $user->createToken($data['name']);

        return Redirect::route('api-access.index', ['user_id' => $user->id])
            ->with('newApiToken', $token->plainTextToken);
    }

    public function destroyToken(Request $request, PersonalAccessToken $apiToken): RedirectResponse
    {
        $userId = $apiToken->tokenable_id;

        $apiToken->delete();

        return Redirect::route('api-access.index', ['user_id' => $userId])
            ->with('success', 'Token API berhasil dicabut.');
    }
}
