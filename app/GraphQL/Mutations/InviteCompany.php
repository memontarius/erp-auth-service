<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Services\InvitationService;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;


final readonly class InviteCompany
{
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'email' => 'required|email',
            'company' => 'required|unique:companies,name'
        ]);

        if ($validator->fails()) {
            throw new Error("Invalid arguments. " .
                implode(' ', Collection::make($validator->errors()->getMessages())->flatten()->toArray()));
        }

        /** @var InvitationService $invitationService */
        $invitationService = app()->make(InvitationService::class);

        return $invitationService->inviteCompany($args);
    }
}
