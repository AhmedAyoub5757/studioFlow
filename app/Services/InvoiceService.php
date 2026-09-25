<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    private const TRANSITIONS = [
        'draft' => ['sent', 'cancelled'],
        'sent' => ['paid', 'cancelled', 'overdue'],
        'overdue' => ['paid', 'cancelled'],
        'paid' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function create(Project $project, array $data, User $issuedBy): Invoice
    {
        return DB::transaction(function () use ($project, $data, $issuedBy) {
            $invoice = Invoice::create([
                'project_id' => $project->id,
                'milestone_id' => $data['milestone_id'] ?? null,
                'type' => $data['type'],
                'tax' => $data['tax'] ?? 0,
                'due_date' => $data['due_date'] ?? null,
                'issued_by' => $issuedBy->id,
                'status' => 'draft',
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $invoice->recalculateTotals();

            return $invoice->fresh(['items']);
        });
    }

    public function transition(Invoice $invoice, string $target): Invoice
    {
        $allowed = self::TRANSITIONS[$invoice->status] ?? [];

        if (! in_array($target, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move invoice from '{$invoice->status}' to '{$target}'.",
            ]);
        }

        $attrs = ['status' => $target];
        if ($target === 'sent') {
            $attrs['sent_at'] = now();
        }

        $invoice->update($attrs);

        return $invoice;
    }

    public function pay(Invoice $invoice, User $payer, string $method): Invoice
    {
        if ($invoice->status !== 'sent' && $invoice->status !== 'overdue') {
            throw ValidationException::withMessages([
                'status' => "Invoice must be 'sent' or 'overdue' to be paid, currently '{$invoice->status}'.",
            ]);
        }

        return DB::transaction(function () use ($invoice, $payer, $method) {
            Payment::create([
                'invoice_id' => $invoice->id,
                'paid_by' => $payer->id,
                'amount' => $invoice->total,
                'method' => $method,
                'status' => 'completed',
                'transaction_ref' => 'TXN-' . strtoupper(Str::random(10)),
            ]);

            $invoice->update(['status' => 'paid', 'paid_at' => now()]);

            // Notification hook (Sprint 6):
            // Notification::send($invoice->project->staff, new InvoicePaid($invoice));

            return $invoice->fresh(['payments']);
        });
    }

    public function refund(Invoice $invoice, User $refundedBy, string $reason): Invoice
    {
        if ($invoice->status !== 'paid') {
            throw ValidationException::withMessages([
                'status' => "Only a 'paid' invoice can be refunded, currently '{$invoice->status}'.",
            ]);
        }

        return DB::transaction(function () use ($invoice, $reason) {
            $payment = $invoice->payments()->where('status', 'completed')->latest()->firstOrFail();
            $payment->update(['status' => 'refunded']);

            $invoice->update(['status' => 'refunded']);

            // $reason could be logged to an audit table in a future sprint.

            return $invoice->fresh(['payments']);
        });
    }
}