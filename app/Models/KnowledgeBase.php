<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'unit_owner',
        'visibility',
        'category',
        'content',
        'tags',
        'status',
        'views_count',
    ];

    // Boot method untuk membuat slug otomatis sebelum data disimpan
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($knowledge) {
            $knowledge->slug = Str::slug($knowledge->title) . '-' . Str::random(5);
        });
    }

    // Relasi ke User pembuat artikel
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}