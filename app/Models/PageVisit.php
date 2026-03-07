<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<Model, $this>
     */
    public function trackable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
