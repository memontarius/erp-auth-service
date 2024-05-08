<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\Role;
use App\Services\InvitationService;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final readonly class InviteUser
{
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'email' => 'required|email',
            'roles' => 'array',
            'roles.*' => Rule::in([Role::CompanyAdmin, Role::CompanyUser])
        ]);

        if ($validator->fails()) {
            throw new Error("Invalid arguments. " .
                implode(' ', Collection::make($validator->errors()->getMessages())->flatten()->toArray()));
        }

        /** @var InvitationService $invitationService */
        $invitationService = app()->make(InvitationService::class);

        return $invitationService->inviteUser($args);
    }
}
