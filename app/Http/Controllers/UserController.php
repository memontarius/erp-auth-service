<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 500);
        }

        $user = auth()->user();

        $companyIds = $user->companies->map(fn($item) => $item->id);

        $userCompanyRoles = [];
        foreach ($user->roles as $role) {
            $userCompanyRoles[$role->pivot->company->id][] = $role->type->value;
        }

        return response()->json([
            'token' => $token,
            'companies' => $companyIds,
            'roles' => $userCompanyRoles
        ]);
    }

    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'string',
            'lastName' => 'string',
            'password' => 'required|string|min:3'
        ]);

        $invitation = auth('invitation')->user();

        if (!$this->checkEmailMatches($data['email'], $invitation)) {
            return response()->json(['message' => 'Email don\'t matches'], 500);
        }

        $user = User::where(['email' => $data['email']])->first();

        if (is_null($user->password)) {
            $user->update([
                'name' => $data['name'],
                'lastName' => $data['lastName'],
                'password' => Hash::make($data['password'])
            ]);
        }

        $invitation->delete();

        return response()->json([
            'message' => 'Successful activation'
        ]);
    }

    /**
     * @param $email
     * @param Authenticatable|null $invitation
     * @return bool
     */
    public function checkEmailMatches(string $email, Authenticatable ...$authenticatables): bool
    {
        foreach ($authenticatables as $item) {
            if ($item->email !== $email) {
                return false;
            }
        }

        return true;
    }
}
