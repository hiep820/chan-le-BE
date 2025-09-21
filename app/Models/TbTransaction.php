<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbTransaction extends Model
{
    use HasFactory;
    protected $table = 'tb_transactions';
    protected $fillable = [
        'transaction_id',
        'gateway',
        'transaction_date',
        'account_number',
        'sub_account',
        'amount_in',
        'amount_out',
        'accumulated',
        'code',
        'transaction_content',
        'reference_number',
        'body',
    ];

}
