<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'account_number',
        'account_holder',
        'qr_code',
        'max',
        'min',
        'active_is',
    ];

    protected function fullImagePath(): Attribute
    {
        return Attribute::make(

            get: fn ($value, $attributes) => url(Storage::url($attributes['qr_code'])),
        );
    }
}
