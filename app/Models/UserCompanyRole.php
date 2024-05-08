<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;


class UserCompanyRole extends Pivot
{
    protected $table = 'users_companies_roles';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'company_id',
        'role_id'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function scopeOfUser(Builder $query, string $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function scopeOfCompany(Builder $query, string $companyId): void
    {
        $query->where('company_id', $companyId);
    }
}
