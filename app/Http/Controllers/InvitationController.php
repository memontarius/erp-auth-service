<?php

namespace App\Http\Controllers;


use App\Enums\Role;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Role as RoleModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\JWTGuard;

class InvitationController extends Controller
{
    private array $roles;

    public function __construct()
    {
        $this->setupRoles();
    }

    public function inviteCompany(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'company' => 'required|unique:companies,name'
        ]);

        $invitation = $this->createInvitation($data['email']);

        $company = Company::create(['name' => $data['company']]);
        $user = User::firstOrCreate(['email' => $data['email']]);

        $user->companies()->attach($company->id);
        $this->attachRoles($user, $company, [$this->getRoleId(Role::CompanyOwner)]);

        $token = $this->generateToken($invitation);
        $this->addToken($invitation, $token);

        return $this->responseWithToken($token);
    }

    public function inviteUser(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'ids' => 'array',
            'ids.*' => Rule::in([Role::CompanyAdmin, Role::CompanyUser])
        ]);

        $invitation = $this->createInvitation($data['email']);

        $user = auth()->user();
        $company = $user->companies->first();
        $invitedUser = User::firstOrCreate(['email' => $data['email']]);

        $invitedUser->companies()->sync($company->id, false);
        $this->attachRoles($invitedUser, $company, $data['ids']);

        $token = $this->generateToken($invitation);
        $this->addToken($invitation, $token);

        return $this->responseWithToken($token);
    }

    private function setupRoles(): void
    {
        $roles = RoleModel::all();

        $this->roles = $roles->reduce(function ($carry, $role, $key) {
            $carry[$role->type->value] = $role->id;
            return $carry;
        }, []);
    }

    private function responseWithToken(string $token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * @param Invitation $invitation
     * @return string
     */
    private function generateToken(Invitation $invitation): string
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
    private function createInvitation(string $email): Invitation
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
    public function attachRoles(User $user, Company $company, array $rolesIds): void
    {
        $roles = array_reduce(
            $rolesIds,
            function ($carry, $item) use ($company) {
                $carry[$item] = ['company_id' => $company->id];
                return $carry;
            },
            []);

        $user->roles()->sync($roles, false);
    }

    /**
     * Get role id in table
     * @return int
     */
    public function getRoleId(Role $role): int
    {
        return $this->roles[$role->value];
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
