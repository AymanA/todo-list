<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemActions extends Model
{
    use HasFactory;
    protected $fillable = [
        'item_id',
        'start_time',
        'stop_time',
        'tracking',
        'time_spent',
    ];

    protected $table = 'item_actions';
}
