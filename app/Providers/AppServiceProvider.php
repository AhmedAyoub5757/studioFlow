<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'task' => \App\Models\Task::class,
            'milestone' => \App\Models\Milestone::class,
            // 'bug' => \App\Models\Bug::class,       // add in Sprint 4
            // 'invoice' => \App\Models\Invoice::class, // add in Sprint 5
        ]);
    }
}
