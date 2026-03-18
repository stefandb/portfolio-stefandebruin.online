<?php

namespace App\Models;

use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'from',
        'to',
        'status_code',
    ];
}
