<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'name',
        'status',
        'current_stage',
        'application_date',
        'hired_at',
        'sms_phone_number',
        'global_phone_number',
        'language',
        'referer_source',
        'position_title',
        'location_name',
    ];

    protected $casts = [
        'application_date' => 'date',
        'hired_at' => 'date',
    ];
}
