<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:owner,staff',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'editUser' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role'  => 'required|in:owner,staff',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)];
        }

        if ($request->email !== $user->email) {
            $rules['email'] = 'required|email|unique:users,email';
        }

        $validated = $request->validate($rules);

        $user->name  = $validated['name'];
        $user->email = $validated['email'] ?? $user->email;
        $user->phone = $validated['phone'] ?? null;
        $user->role  = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
