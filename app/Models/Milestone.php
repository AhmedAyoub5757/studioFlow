<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'amount',
        'due_date',
        'status',
        'order',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable')->latest();
    }

    /** Convenience accessor: the current pending approval, if any */
    public function pendingApproval(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Approval::class, 'approvable')->where('status', 'pending')->latestOfMany();
    }
}
