<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'transaction_code',
    'member_id',
    'user_id',
    'transaction_type',
    'transaction_date',
    'subtotal',
    'discount',
    'total_amount',
    'payment_method',
    'status',
    'paid_at',
    'notes',
])]
class Transaction extends Model
{
    //
}