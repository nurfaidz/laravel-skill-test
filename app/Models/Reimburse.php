<?php

namespace App\Models;

use App\Enums\StatusReimburse;
use App\Models\Scopes\ReimbursScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimburse extends Model
{
    /** @use HasFactory<\Database\Factories\ReimburseFactory> */
    use HasFactory;

    protected $fillable = [
        'requested_by',
        'requested_at',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => StatusReimburse::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ReimbursScope);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
