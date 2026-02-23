<?php

namespace App\Domains\User\Services;

use App\Domains\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Create a user. By superadmin: can set tenant_id and is_tenant_owner.
     * By tenant owner: creates member for their tenant only (is_tenant_owner = false).
     *
     * @param  array{name: string, email: string, password: string, tenant_id?: int|null, is_tenant_owner?: bool}  $data
     */
    public function create(array $data, bool $bySuperadmin): User
    {
        if ($bySuperadmin) {
            $tenantId = $data['tenant_id'] ?? null;
            $isOwner = (bool) ($data['is_tenant_owner'] ?? false);
            if ($isOwner && $tenantId === null) {
                throw ValidationException::withMessages([
                    'tenant_id' => [__('Tenant is required when creating an owner.')],
                ]);
            }
            if ($tenantId !== null && $isOwner) {
                $this->ensureOnlyOneOwnerPerTenant($tenantId, null);
            }
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tenant_id' => $tenantId,
                'is_tenant_owner' => $isOwner,
            ]);
        } else {
            $tenantId = auth()->user()->tenant_id;
            if (! $tenantId || ! auth()->user()->isTenantOwner()) {
                abort(403, __('Only tenant owner or superadmin can create users.'));
            }
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tenant_id' => $tenantId,
                'is_tenant_owner' => false,
            ]);
        }

        return $user;
    }

    /**
     * Update a user. Superadmin can change tenant_id and is_tenant_owner.
     * Tenant owner can only update name, email, password for members of their tenant.
     *
     * @param  array{name?: string, email?: string, password?: string, tenant_id?: int|null, is_tenant_owner?: bool}  $data
     */
    public function update(User $user, array $data, bool $bySuperadmin): void
    {
        if ($bySuperadmin) {
            if (array_key_exists('is_tenant_owner', $data)) {
                $isOwner = (bool) $data['is_tenant_owner'];
                $tenantId = $data['tenant_id'] ?? $user->tenant_id;
                if ($isOwner && $tenantId === null) {
                    throw ValidationException::withMessages([
                        'tenant_id' => [__('Tenant is required for owner.')],
                    ]);
                }
                if ($tenantId !== null) {
                    $this->ensureOnlyOneOwnerPerTenant($tenantId, $user->id);
                }
            }
            if (array_key_exists('name', $data)) {
                $user->name = $data['name'];
            }
            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }
            if (array_key_exists('tenant_id', $data)) {
                $user->tenant_id = $data['tenant_id'];
            }
            if (array_key_exists('is_tenant_owner', $data)) {
                $user->is_tenant_owner = (bool) $data['is_tenant_owner'];
            }
            if (! empty($data['password'] ?? null)) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
        } else {
            if (auth()->user()->tenant_id !== $user->tenant_id || $user->isTenantOwner()) {
                abort(403, __('You can only edit members of your tenant.'));
            }
            if (array_key_exists('name', $data)) {
                $user->name = $data['name'];
            }
            if (array_key_exists('email', $data)) {
                $user->email = $data['email'];
            }
            if (! empty($data['password'] ?? null)) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
        }
    }

    /**
     * Delete a user. Superadmin can delete any. Tenant owner can only delete members (not owner, not self).
     */
    public function delete(User $user, bool $bySuperadmin): void
    {
        if ($bySuperadmin) {
            $user->delete();

            return;
        }
        if (auth()->id() === $user->id) {
            abort(403, __('You cannot delete yourself.'));
        }
        if (auth()->user()->tenant_id !== $user->tenant_id || $user->isTenantOwner()) {
            abort(403, __('You can only delete members of your tenant.'));
        }
        $user->delete();
    }

    private function ensureOnlyOneOwnerPerTenant(int $tenantId, ?int $exceptUserId): void
    {
        $query = User::where('tenant_id', $tenantId)->where('is_tenant_owner', true);
        if ($exceptUserId !== null) {
            $query->where('id', '!=', $exceptUserId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages([
                'is_tenant_owner' => [__('This tenant already has an owner.')],
            ]);
        }
    }
}
