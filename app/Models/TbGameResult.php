<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbGameResult extends Model
{
    use HasFactory;

    protected $table = 'tb_game_results';

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'game_name',
        'bet_key',
        'reference_number',
        'amount',
        'result',
        'reward_amount',
        'is_paid',
        'note',
        'transaction_date',
    ];

    public function transaction()
    {
        return $this->belongsTo(TbTransaction::class, 'transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
