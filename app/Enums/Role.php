<?php

namespace App\Enums;

enum Role: int
{
    case ServiceAdmin = 1;
    case CompanyOwner = 2;
    case CompanyAdmin = 3;
    case CompanyUser = 4;

    public static function toArray(): array
    {
        return array_column(Role::cases(), 'value');
    }
}
