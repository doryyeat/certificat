<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $organizations = Organization::query()
            ->withCount('users')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $organizations,
            'filters' => ['search' => $search],
        ]);
    }

    public function edit(Organization $organization): Response
    {
        $organization->loadCount('users');

        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:organizations,slug,'.$organization->id],
            'plan_name' => ['required', 'string', 'max:100'],
            'subscription_active_until' => ['nullable', 'date'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'logo_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $data['slug'] = Str::slug($data['slug']);

        $organization->update($data);

        return redirect()
            ->route('admin.organizations.edit', $organization)
            ->with('success', 'Организация обновлена.');
    }
}
