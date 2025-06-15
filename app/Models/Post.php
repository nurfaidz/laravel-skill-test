<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'is_draft',
        'published_at',
    ];

    /**
     * Scope get only active posts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope get only draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_draft', true);
    }

    /**
     * Scope to get scheduled posts
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_draft', false)
            ->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    /**
     * Check if the post is published
     */
    public function isPublished(): bool
    {
        return ! $this->is_draft &&
                $this->published_at !== null &&
                $this->published_at <= now();
    }

    /**
     * Check if the post is a scheduled
     */
    public function isScheduled(): bool
    {
        return ! $this->is_draft &&
                $this->published_at !== null &&
                $this->published_at >= now();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
