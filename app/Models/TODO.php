<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TODO extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'description',
        'status',
    ];

    protected $table = 'todo';
}
