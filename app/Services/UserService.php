<?php

namespace App\Services;

use App\Exceptions\LoginUserFailureException;
use App\Exceptions\UserActivationEmailNotMatchesException;
use App\Http\Controllers\UserController;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * @param array $args
     * @return array
     * @throws LoginUserFailureException
     */
    public function login(array $args): array
    {
        $guard = auth('api');

        if (!$token = $guard->attempt($args)) {
            throw new LoginUserFailureException();
        }

        $user = $guard->user();
        $companyIds = $user->companies->map(fn($item) => $item->id);

        $userCompanyRoles = UserCompanyRole::ofUser($user->id)
            ->get()
            ->groupBy('company_id')
            ->map(fn($item, $key) => ['company_id' => $key, 'roles' => $item->pluck('role_id')->all()])
            ->toArray();

        return [
            'token' => $token,
            'companies' => $companyIds,
            'roles' => $userCompanyRoles
        ];
    }

    /**
     * @param array $args
     * @param Invitation $invitation
     * @return void
     * @throws UserActivationEmailNotMatchesException
     */
    public function activate(array $args): void
    {
        /** @var Invitation $invitation */
        $invitation = auth('invitation')->user();

        if (!UserController::checkEmailMatches($args['email'], $invitation)) {
            throw new UserActivationEmailNotMatchesException();
        }

        $user = User::where(['email' => $args['email']])->first();

        if (is_null($user->password)) {
            $user->update([
                'name' => $args['name'],
                'lastName' => $args['lastName'],
                'password' => Hash::make($args['password'])
            ]);
        }

        $invitation->delete();
    }

    /**
     * @param $email
     * @param Authenticatable|null $invitation
     * @return bool
     */
    private function checkEmailMatches(string $email, Authenticatable ...$authenticatables): bool
    {
        foreach ($authenticatables as $item) {
            if ($item->email !== $email) {
                return false;
            }
        }

        return true;
    }
}
