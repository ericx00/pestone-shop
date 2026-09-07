<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequest extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'company',
        'subject', 'message', 'items', 'status', 'admin_notes',
    ];

    protected $casts = ['items' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
