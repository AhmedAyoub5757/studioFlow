<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function view(User $user, Subscription $subscription): bool
    {
        if ($subscription->project->client_id === $user->id) {
            return true;
        }

        return $user->can('permission', 'subscriptions.manage')
            && ($user->can('permission', 'projects.view_any') || $subscription->project->isManagedBy($user));
    }

    public function manage(User $user): bool
    {
        return $user->can('permission', 'subscriptions.manage');
    }
}
