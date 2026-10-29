<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'login'    => 'required|string|max:255|unique:users,login',
            'password' => 'required|string|min:6',
        ]);

        $teacherRoleId = Role::where('name', 'teacher')->firstOrFail()->id;
        
        User::create([
            'name'     => $validated['name'],
            'login'    => $validated['login'],
            'password' => $validated['password'],
            'role_id'  => $teacherRoleId,
        ]);

        return back()->with('success', 'Аккаунт учителя создан.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Вы не можете удалить свой аккаунт.');
        }

        $user->delete();
        return back()->with('success', 'Пользователь удален.');
    }
}