<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $table = 'password_reset_otps';
    
    public $timestamps = false;

    protected $fillable = [
        'email',
        'otp',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}