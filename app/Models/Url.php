<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
        protected $table = 'short_links';
        protected $primaryKey = 'id';
        public $incrementing = true;
        protected $keyType = 'int';
      protected $fillable = [
        'long_url',
        'short_code',
        'clicks',
        'expires_at'
    ];

    protected function casts():array
    {
        return [
            'clicks' =>'integer'             
        ];

    }


}
