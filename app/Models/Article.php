<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_id',
        'title',
        'description',
        'content',
        'author',
        'url',
        'url_to_image',
        'category',
        'published_at',
        'external_id',
        'raw_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Get the source that owns the article.
     */
    public function source()
    {
        return $this->belongsTo(Source::class);
    }
}
