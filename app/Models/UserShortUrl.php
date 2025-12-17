<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Domain;

class UserShortUrl extends Model
{
    use HasFactory;

    protected $table = 'user_short_links';

    protected $fillable = [
        'domain_id',
        'short_code',
        'original_url',
        'etiquetas',
        'clicks',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
    public function clicks_data() // Usamos este nombre para no confundir con la columna 'clicks'
    {
        return $this->hasMany(Click::class, 'user_short_link_id');
    }
}
