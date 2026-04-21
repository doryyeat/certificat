<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Mail\ManagerCredentialsMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class ManagerController extends Controller
{
    public function index(Request $request): Response
    {
        $orgId = $request->user()->organization_id;

        $managers = User::query()
            ->where('organization_id', $orgId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'manager'))
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'phone', 'created_at']);

        return Inertia::render('Business/Managers/Index', [
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'generate_password' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
            'send_credentials' => ['nullable', 'boolean'],
        ]);

        $plainPassword = null;
        if (!empty($data['generate_password'])) {
            $plainPassword = $this->generatePassword();
        } else {
            $plainPassword = $data['password'] ?? null;
        }

        if (!$plainPassword) {
            return back()->withErrors(['password' => 'Укажите пароль или выберите генерацию пароля.']);
        }

        // На всякий случай создаём роль, если сидер не прогоняли
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        $manager = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'organization_id' => $orgId,
            'client_type' => 'business',
            'password' => Hash::make($plainPassword),
        ]);
        $manager->assignRole('manager');

        if (!empty($data['send_credentials'])) {
            $this->sendCredentials($manager, $plainPassword);
        }

        return redirect()->route('business.managers.index')->with('success', 'Менеджер добавлен.');
    }

    public function destroy(Request $request, User $user)
    {
        $orgId = $request->user()->organization_id;

        if ((int) $user->organization_id !== (int) $orgId || ! $user->hasRole('manager')) {
            abort(403);
        }

        $user->delete();

        return back()->with('success', 'Менеджер удалён.');
    }

    public function sendCredentialsAction(Request $request, User $user)
    {
        $orgId = $request->user()->organization_id;

        if ((int) $user->organization_id !== (int) $orgId || ! $user->hasRole('manager')) {
            abort(403);
        }

        $plainPassword = $this->generatePassword();
        $user->password = Hash::make($plainPassword);
        $user->save();

        $this->sendCredentials($user, $plainPassword);

        return back()->with('success', 'Данные для входа отправлены менеджеру.');
    }

    private function sendCredentials(User $manager, string $plainPassword): void
    {
        Mail::to($manager->email)->send(new ManagerCredentialsMail(
            managerName: $manager->name,
            email: $manager->email,
            password: $plainPassword,
            loginUrl: route('login'),
        ));
    }

    private function generatePassword(): string
    {
        // Без неоднозначных символов, 10 знаков
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $pass = '';
        for ($i = 0; $i < 10; $i++) {
            $pass .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $pass;
    }
}

