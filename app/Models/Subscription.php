<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'project_id', 'name', 'amount', 'billing_cycle', 'status', 'next_billing_date', 'started_at',
    ];

    protected $casts = [
        'next_billing_date' => 'date',
        'started_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
