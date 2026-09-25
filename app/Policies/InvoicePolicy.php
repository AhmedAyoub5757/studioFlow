<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($invoice->project->client_id === $user->id) {
            return true; // client sees their own invoices
        }

        if (! $user->can('permission', 'invoices.view_any')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $invoice->project->isManagedBy($user);
    }

    public function create(User $user, Project $project): bool
    {
        if (! $user->can('permission', 'invoices.manage')) {
            return false;
        }

        // Finance is agency-wide by design (not scoped per-project like PM), so any
        // Finance user can invoice any project. Agency Manager override also applies.
        return true;
    }

    /** Send, edit line items, cancel while still draft/sent */
    public function manage(User $user, Invoice $invoice): bool
    {
        return $user->can('permission', 'invoices.manage');
    }

    public function refund(User $user, Invoice $invoice): bool
    {
        return $user->can('permission', 'invoices.refund');
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        if (! $user->can('permission', 'invoices.pay')) {
            return false;
        }

        return $invoice->project->client_id === $user->id;
    }
}