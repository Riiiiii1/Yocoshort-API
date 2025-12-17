<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'user_short_link_id',
        'clicked_at',
        'ip_address',
        'browser',
        'platform',
        'referer'
    ];

    public function link()
    {
        return $this->belongsTo(UserShortUrl::class, 'user_short_link_id');
    }
}
