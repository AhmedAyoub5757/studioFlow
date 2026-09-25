<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionService
{
    public function create(Project $project, array $data): Subscription
    {
        $startedAt = Carbon::parse($data['started_at']);

        return Subscription::create(array_merge($data, [
            'project_id' => $project->id,
            'status' => 'active',
            'next_billing_date' => $this->nextBillingDate($startedAt, $data['billing_cycle']),
        ]));
    }

    private function nextBillingDate(Carbon $from, string $cycle): Carbon
    {
        switch ($cycle) {
            case 'monthly':
                return $from->copy()->addMonthNoOverflow();
            case 'quarterly':
                return $from->copy()->addMonthsNoOverflow(3);
            case 'yearly':
                return $from->copy()->addYear();
            default:
                return $from->copy();
        }
    }

    /**
     * Generates the invoice for the current billing cycle and advances
     * next_billing_date. In production this would be called by a scheduled
     * command (php artisan schedule) rather than an on-demand endpoint —
     * we expose it manually here since we're excluding task scheduling/CI setup
     * from this learning phase. The logic itself is identical either way.
     */
    public function generateInvoiceForCycle(Subscription $subscription, InvoiceService $invoices, User $issuedBy)
    {
        $invoice = $invoices->create($subscription->project, [
            'type' => 'subscription',
            'items' => [[
                'description' => $subscription->name . ' — ' . $subscription->next_billing_date->format('M Y'),
                'quantity' => 1,
                'unit_price' => $subscription->amount,
            ]],
        ], $issuedBy);

        $subscription->update([
            'next_billing_date' => $this->nextBillingDate($subscription->next_billing_date, $subscription->billing_cycle),
        ]);

        return $invoice;
    }
}