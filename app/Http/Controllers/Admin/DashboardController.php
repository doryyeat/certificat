<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\RegisterRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'pending_requests' => RegisterRequest::where('status', RegisterRequest::STATUS_PENDING)->count(),
                'organizations' => Organization::count(),
                'business_users' => User::role('business')->count(),
            ],
        ]);
    }
}
