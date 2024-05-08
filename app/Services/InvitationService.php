<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role as RoleModel;
use App\Models\User;
use Tymon\JWTAuth\JWTGuard;

class InvitationService
{
    private array $roles;

    public function __construct()
    {
        $this->setupRoles();
    }

    public function inviteCompany(array $args): string
    {
        $invitation = $this->createInvitation($args['email']);

        $company = Company::create(['name' => $args['company']]);
        $user = User::firstOrCreate(['email' => $args['email']]);

        $user->companies()->attach($company->id);
        $this->attachRoles($user, $company, [Role::CompanyOwner->value]);

        $token = $this->generateToken($invitation);
        $this->addToken($invitation, $token);

        return $token;
    }

    public function inviteUser(array $args): string
    {
        $invitation = $this->createInvitation($args['email']);

        $user = auth()->user();
        $company = $user->companies->first();
        $invitedUser = User::firstOrCreate(['email' => $args['email']]);

        $invitedUser->companies()->sync($company->id, false);
        $this->attachRoles($invitedUser, $company, $args['roles']);

        $token = $this->generateToken($invitation);
        $this->addToken($invitation, $token);

        return $token;
    }

    private function setupRoles(): void
    {
        $this->roles = RoleModel::all()
            ->reduce(function ($carry, $role, $key) {
                $carry[$role->type->value] = $role->id;
                return $carry;
            }, []);
    }

    /**
     * Get role id in table
     * @return int
     */
    public function getRoleId(Role|int $role): int
    {
        $id = $role instanceof Role ? $role->value : $role;
        return $this->roles[$id];
    }

    /**
     * @param Invitation $invitation
     * @return string
     */
    public function generateToken(Invitation $invitation): string
    {
        /** @var JWTGuard $guard */
        $guard = auth('invitation');
        $token = $guard->login($invitation);
        return $token;
    }

    /**
     * @param string $email
     * @return Invitation
     */
    public function createInvitation(string $email): Invitation
    {
        $invitation = new Invitation([
            'email' => $email,
            'token' => ''
        ]);
        $invitation->save();
        return $invitation;
    }

    /**
     * @param mixed $roleId
     * @param $company
     * @param $user
     * @return void
     */
    public function attachRoles(User $user, Company $company, array $roleTypeIds): void
    {
        $rolesIds = array_map(fn($id) => $this->getRoleId($id), $roleTypeIds);

        $existingRoleIds = $user->roles()
            ->where('company_id', $company->id)
            ->whereIn('role_id', $rolesIds)
            ->get()
            ->pluck('pivot.role_id')
            ->toArray();

        $rolesIds = array_diff($rolesIds, $existingRoleIds);

        if (!empty($rolesIds)) {
            $roles = array_reduce(
                $rolesIds,
                function ($carry, $item) use ($user, $company) {
                    $carry[$item] = ['company_id' => $company->id];
                    return $carry;
                }, []);

            $user->roles()->attach($roles);
        }
    }

    /**
     * @param Invitation $invitation
     * @param string $token
     * @return void
     */
    public function addToken(Invitation $invitation, string $token): void
    {
        $invitation->setAttribute('token', $token);
        $invitation->save();
    }
}
