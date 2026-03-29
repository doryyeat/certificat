<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Показать профиль бизнеса
     */
    public function show()
    {
        $user = Auth::user();
        $business = $user->business;

        return Inertia::render('Business/Profile/Show', [
            'user' => $user,
            'business' => $business,
        ]);
    }

    /**
     * Показать форму редактирования
     */
    public function edit()
    {
        $user = Auth::user();
        $business = $user->business;

        return Inertia::render('Business/Profile/Edit', [
            'user' => $user,
            'business' => $business,
        ]);
    }

    /**
     * Обновить профиль
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'business_name' => 'required|string|max:255',
            'inn' => 'required|string|size:12|unique:businesses,inn,' . $business->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
        ]);

        // Обновляем пользователя
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Обновляем бизнес
        $business->update([
            'name' => $validated['business_name'],
            'inn' => $validated['inn'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'description' => $validated['description'],
            'website' => $validated['website'],
        ]);

        return redirect()->route('business.profile.show')
            ->with('success', 'Профиль успешно обновлен');
    }
}
