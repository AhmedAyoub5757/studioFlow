<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'client_id',
        'created_by',
        'status',
        'budget',
        'start_date',
        'end_date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            $project->slug = $project->slug ?: Str::slug($project->name) . '-' . Str::random(5);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role_on_project')
            ->withTimestamps();
    }

    public function staffWithRole(string $role): BelongsToMany
    {
        return $this->staff()->wherePivot('role_on_project', $role);
    }

    public function milestones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** Is $user the manager on THIS project? Reused across Sprint 2/3 policies. */
    public function isManagedBy(User $user): bool
    {
        return $this->staffWithRole('manager')->where('user_id', $user->id)->exists();
    }

    public function bugs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bug::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
