<?php

namespace App\Models;

use App\Models\Scopes\SameUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ChatThread extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function iterations(): HasMany
    {
        return $this->hasMany(ChatThreadIteration::class)->orderBy('created_at', 'ASC');
    }

    public function scopeNotDocumentRelated($query)
    {
        return $query->whereNull('document_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SameUserScope());
        static::creating(function ($thread) {
            if (Auth::check() && !$thread->user_id) {
                $thread->user_id = Auth::user()->id;
            }
        });
    }
}
