<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Показать профиль покупателя
     */
    public function show()
    {
        $user = Auth::user();

        return Inertia::render('Client/Profile/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Показать форму редактирования
     */
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Client/Profile/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Обновить профиль
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();
        }

        return redirect()->route('client.profile.show')
            ->with('success', 'Профиль успешно обновлен');
    }

    /**
     * Сменить пароль
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('client.profile.show')
            ->with('success', 'Пароль успешно изменен');
    }
}
