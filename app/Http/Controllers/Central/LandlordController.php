<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class LandlordController extends Controller
{
    /**
     * Show the landlord dashboard with all tenants (schools).
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->orderBy('created_at', 'desc')->get();
        $totalSchools = $tenants->count();
        $activeSchools = $tenants->filter(fn ($t) => ($t->getAttribute('status') ?? 'active') === 'active')->count();
        $inactiveSchools = $tenants->filter(fn ($t) => ($t->getAttribute('status') ?? 'active') === 'suspended')->count();
        return view('central.dashboard', compact('tenants', 'totalSchools', 'activeSchools', 'inactiveSchools'));
    }

    /**
     * Show a single school (tenant) for management.
     */
    public function show(string $school)
    {
        $tenant = Tenant::with('domains')->findOrFail($school);
        $primaryDomain = $tenant->domains->first();
        $superAdmin = null;
        $tenancy = app(\Stancl\Tenancy\Tenancy::class);
        $tenancy->initialize($tenant);
        try {
            $superAdmin = User::where('user_type', 'super_admin')->first();
        } finally {
            $tenancy->end();
        }
        return view('central.schools.show', compact('tenant', 'primaryDomain', 'superAdmin'));
    }

    /**
     * Store a new tenant (school).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id'   => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_-]+$/'],
            'school_name' => ['required', 'string', 'max:255'],
            'domain'      => ['required', 'string', 'max:255'],
        ], [
            'school_id.regex' => 'School ID may only contain lowercase letters, numbers, hyphens and underscores.',
        ]);

        $id = Str::lower($validated['school_id']);
        $name = $validated['school_name'];
        $domain = Str::lower(trim($validated['domain']));

        if (Tenant::find($id)) {
            return redirect()->route('landlord.dashboard')
                ->with('error', "A school with ID \"{$id}\" already exists.");
        }
        if (Domain::where('domain', $domain)->exists()) {
            return redirect()->route('landlord.dashboard')
                ->with('error', "Domain \"{$domain}\" is already in use.");
        }

        try {
            $tenant = Tenant::create([
                'id' => $id,
                'name' => $name,
                'status' => 'active',
                'is_active' => true,
            ]);
            $tenant->createDomain($domain);
            return redirect()->route('landlord.dashboard')
                ->with('success', "School \"{$name}\" created successfully. Domain: {$domain}");
        } catch (\Throwable $e) {
            return redirect()->route('landlord.dashboard')
                ->with('error', 'Failed to create school: ' . $e->getMessage());
        }
    }

    /**
     * Update school name and/or domain.
     */
    public function update(Request $request, string $school)
    {
        $tenant = Tenant::with('domains')->findOrFail($school);
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'domain'      => ['required', 'string', 'max:255'],
        ]);
        $domain = Str::lower(trim($validated['domain']));
        $existing = Domain::where('domain', $domain)->where('tenant_id', '!=', $school)->first();
        if ($existing) {
            return redirect()->route('landlord.schools.show', $school)
                ->with('error', "Domain \"{$domain}\" is already in use by another school.");
        }
        $tenant->setAttribute('name', $validated['school_name']);
        $tenant->save();
        $primary = $tenant->domains->first();
        if ($primary) {
            $primary->domain = $domain;
            $primary->save();
        } else {
            $tenant->createDomain($domain);
        }
        return redirect()->route('landlord.schools.show', $school)
            ->with('success', 'School updated successfully.');
    }

    /**
     * Toggle tenant status: active <-> suspended. Persist column and data JSON so both stay in sync.
     */
    public function toggleStatus(string $school)
    {
        $tenant = Tenant::findOrFail($school);
        $current = $tenant->getAttribute('status') ?? 'active';
        $newStatus = ($current === 'suspended') ? 'active' : 'suspended';

        Tenant::where('id', $school)->update(['status' => $newStatus]);
        if (\Schema::hasColumn('tenants', 'is_active')) {
            Tenant::where('id', $school)->update(['is_active' => $newStatus === 'active']);
        }

        // Keep data JSON in sync so models that read from data (e.g. Stancl) show correct status
        $data = $tenant->getAttribute('data');
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }
        $data = is_array($data) ? $data : [];
        $data['status'] = $newStatus;
        Tenant::where('id', $school)->update(['data' => json_encode($data)]);

        $message = $newStatus === 'active' ? 'School has been activated.' : 'School has been suspended.';
        return redirect()->route('landlord.dashboard')
            ->with('success', $message);
    }

    /**
     * Delete tenant and drop database.
     */
    public function destroy(string $school)
    {
        $tenant = Tenant::findOrFail($school);
        $name = $tenant->getAttribute('name') ?? $school;
        try {
            $tenant->delete();
            return redirect()->route('landlord.dashboard')
                ->with('success', "School \"{$name}\" has been removed.");
        } catch (\Throwable $e) {
            return redirect()->route('landlord.dashboard')
                ->with('error', 'Failed to delete school: ' . $e->getMessage());
        }
    }

    /**
     * Reset the super admin password for a tenant (run in tenant context).
     */
    public function resetPassword(Request $request, string $school)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $tenant = Tenant::findOrFail($school);
        $tenancy = app(\Stancl\Tenancy\Tenancy::class);
        $tenancy->initialize($tenant);
        try {
            $user = User::where('user_type', 'super_admin')->first();
            if (! $user) {
                return redirect()->route('landlord.schools.show', $school)
                    ->with('error', 'Super admin user not found in this school.');
            }
            $user->password = Hash::make($request->password);
            $user->save();
        } finally {
            $tenancy->end();
        }
        return redirect()->route('landlord.schools.show', $school)
            ->with('success', 'Super admin password has been reset.');
    }
}
