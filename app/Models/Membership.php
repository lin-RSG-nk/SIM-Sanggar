<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'membership_code',
    'member_id',
    'membership_package_id',
    'start_date',
    'end_date',
    'total_quota',
    'remaining_quota',
    'status',
    'notes',
    'transaction_id',
    'transaction_detail_id',
])]
class Membership extends Model
{
    //
}