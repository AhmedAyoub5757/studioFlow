<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;


class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    protected Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Invoice paid',
            'message' => "Invoice {$this->invoice->invoice_number} ({$this->invoice->total}) has been paid.",
            'invoice_id' => $this->invoice->id,
            'project_id' => $this->invoice->project_id,
            'action_url' => "/projects/{$this->invoice->project_id}/invoices/{$this->invoice->id}",
        ];
    }
}