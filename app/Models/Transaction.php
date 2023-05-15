<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_client_id',
        'to_client_id',
        'product_id',
        'amount',
        'balance_before',
        'balance_after',
        'type',
        'details',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
