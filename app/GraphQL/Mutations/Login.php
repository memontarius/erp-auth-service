<?php declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\LoginUserFailureException;
use App\Services\UserService;
use GraphQL\Error\Error;

final readonly class Login
{
    public function __invoke(null $_, array $args)
    {
        /** @var UserService $userService */
        $userService = app()->make(UserService::class);

        try {
            $result = $userService->login($args);
        } catch (LoginUserFailureException $e) {
            throw new Error('Invalid credential.');
        }

        return $result;
    }
}
