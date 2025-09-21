<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserPreference;

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

    /**
     * Apply filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFilters($query, array $filters)
    {
        // Define filter map with callbacks
        $filterMap = [
            'search' => fn($value) => $query->whereFullText(['title', 'description'], $value),
            'date_from' => fn($value) => $query->where('published_at', '>=', $value),
            'date_to' => fn($value) => $query->where('published_at', '<=', $value),
        ];
        
        // Apply each filter if it exists
        foreach ($filterMap as $key => $callback) {
            if (!empty($filters[$key])) {
                $callback($filters[$key]);
            }
        }
        
        return $query;
    }
    
    /**
     * Apply user preferences to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\UserPreference|null $preferences
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyUserPreferences($query, ?UserPreference $preferences)
    {
        if (!$preferences) {
            return $query;
        }
        
        // Define preference map with callbacks
        $preferenceMap = [
            'preferred_sources' => fn($value) => $query->whereIn('source_id', $value),
            'preferred_categories' => fn($value) => $query->whereIn('category', $value),
            'preferred_authors' => fn($value) => $query->where(function ($q) use ($value) {
                foreach ($value as $author) {
                    $q->orWhere('author', 'like', '%' . $author . '%');
                }
            }),
        ];
        
        // Apply each preference if it exists
        foreach ($preferenceMap as $key => $callback) {
            if (!empty($preferences->$key)) {
                $callback($preferences->$key);
            }
        }
        
        return $query;
    }
}
