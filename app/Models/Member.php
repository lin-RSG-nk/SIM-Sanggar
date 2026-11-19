<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'member_code',
    'name',
    'gender',
    'birth_date',
    'phone',
    'email',
    'address',
    'registered_at',
    'status',
    'notes',
])]
class Member extends Model
{
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}