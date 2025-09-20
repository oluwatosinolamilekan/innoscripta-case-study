<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'api_id',
        'api_name',
        'description',
        'url',
        'category',
        'language',
        'country',
    ];

    /**
     * Get the articles for the source.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
