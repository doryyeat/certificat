<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendAcceptBusiness;
use App\Models\Organization;
use App\Models\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisterRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $requests = RegisterRequest::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/RegisterRequests/Index', [
            'requests' => $requests,
            'filters' => ['status' => $status],
        ]);
    }

    public function show(RegisterRequest $registerRequest): Response
    {
        $registerRequest->load(['organization', 'user']);

        return Inertia::render('Admin/RegisterRequests/Show', [
            'request' => $registerRequest,
        ]);
    }

    public function accept(RegisterRequest $registerRequest): RedirectResponse
    {
        if ($registerRequest->status !== RegisterRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Заявка уже обработана.']);
        }

        DB::transaction(function () use ($registerRequest) {
            $slugBase = Str::slug($registerRequest->name);
            $slug = $slugBase.'-'.Str::lower(Str::random(6));

            $organization = Organization::create([
                'name' => $registerRequest->name,
                'slug' => $slug,
                'plan_name' => 'free',
                'subscription_active_until' => now()->addMonths(3),
            ]);

            $user = User::create([
                'name' => $registerRequest->contact,
                'email' => $registerRequest->email,
                'password' => Str::random(32),
                'organization_id' => $organization->id,
                'client_type' => 'business',
                'email_verified_at' => now(),
            ]);

            $hashed = DB::table('register_requests')
                ->where('id', $registerRequest->id)
                ->value('password');

            DB::table('users')->where('id', $user->id)->update(['password' => $hashed]);

            $user->assignRole('business');

            $registerRequest->update([
                'status' => RegisterRequest::STATUS_ACCEPTED,
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'reason' => null,
            ]);
            Mail::to($user)->send(new SendAcceptBusiness($registerRequest->email,$registerRequest));
        });

        return redirect()
            ->route('admin.register-requests.index')
            ->with('success', 'Заявка принята: создана организация и пользователь.');
    }

    public function reject(Request $request, RegisterRequest $registerRequest): RedirectResponse
    {
        if ($registerRequest->status !== RegisterRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Заявка уже обработана.']);
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $registerRequest->update([
            'status' => RegisterRequest::STATUS_REJECTED,
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()
            ->route('admin.register-requests.index')
            ->with('success', 'Заявка отклонена.');
    }
}
