<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'member_id',
    'class_schedule_id',
    'membership_id',
    'transaction_detail_id',
    'recorded_by',
    'attendance_date',
    'attendance_status',
    'check_in_method',
    'check_in_at',
    'notes',
])]
class Attendance extends Model
{
    //
}