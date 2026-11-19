<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'instructor_code',
    'name',
    'gender',
    'phone',
    'email',
    'address',
    'hire_date',
    'status',
    'notes',
])]
class Instructor extends Model
{
    public function compensationRules(): HasMany
    {
        return $this->hasMany(InstructorCompensationRule::class);
    }

    public function feeRecords(): HasMany
    {
        return $this->hasMany(InstructorFeeRecord::class);
    }
}