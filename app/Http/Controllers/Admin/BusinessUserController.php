<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusinessUserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->role('business')
            ->with('organization')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('organization', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%'.$search.'%');
                    });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/BusinessUsers/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function block(Request $request, User $user): RedirectResponse
    {
        if (! $user->hasRole('business')) {
            abort(403);
        }

        $data = $request->validate([
            'block_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'is_blocked' => true,
            'block_reason' => $data['block_reason'] ?? null,
        ]);

        return back()->with('success', 'Учётная запись заблокирована.');
    }

    public function unblock(User $user): RedirectResponse
    {
        if (! $user->hasRole('business')) {
            abort(403);
        }

        $user->update([
            'is_blocked' => false,
            'block_reason' => null,
        ]);

        return back()->with('success', 'Блокировка снята.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (! $user->hasRole('business')) {
            abort(403);
        }

        $user->syncRoles([]);
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        $user->delete();

        return redirect()->route('admin.business-users.index')->with('success', 'Пользователь удалён.');
    }
}
