<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'clientType' => request()->cookie('client_type', 'client'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'client_type' => 'required|in:client,business',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'client_type' => $request->client_type,
        ]);

        // Назначаем роль
        $user->assignRole($request->client_type);

        // Если это бизнес, создаем организацию
        if ($request->client_type === 'business') {
            $organization = Organization::create([
                'name' => $request->name . ' Organization',
                'slug' => \Str::slug($request->name . '-' . uniqid()),
                'plan_name' => 'free',
                'subscription_active_until' => now()->addMonth(),
            ]);

            $user->organization_id = $organization->id;
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user);

        // Редиректим в зависимости от типа
        if ($request->client_type === 'business') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('client.certificates.index');
    }
}
