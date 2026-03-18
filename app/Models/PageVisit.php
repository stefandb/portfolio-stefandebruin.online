<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageVisit extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'url',
        'referer',
        'session_id',
        'trackable_id',
        'trackable_type',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }
}
