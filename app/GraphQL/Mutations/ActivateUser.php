<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\UserActivationEmailNotMatchesException;
use App\Services\UserService;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

final readonly class ActivateUser
{
    public function __invoke(null $_, array $args)
    {
        $validator = Validator::make($args, [
            'email' => 'required|email',
            'name' => 'string',
            'lastName' => 'string',
            'password' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
            throw new Error("Invalid arguments. " .
                implode(' ', Collection::make($validator->errors()->getMessages())->flatten()->toArray()));
        }

        /** @var UserService $userService */
        $userService = app()->make(UserService::class);

        try {
            $userService->activate($args);
        } catch (UserActivationEmailNotMatchesException $e) {
            throw new Error('Email don\'t matches.');
        }

        return 'Successful activation';
    }
}
