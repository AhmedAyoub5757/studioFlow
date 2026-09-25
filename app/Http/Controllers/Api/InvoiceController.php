<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayInvoiceRequest;
use App\Http\Requests\RefundInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    protected InvoiceService $invoices;

    public function __construct(InvoiceService $invoices)
    {
        $this->invoices = $invoices;
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $query = $project->invoices()->with(['items']);

        // Client only ever sees their own project's invoices (already scoped by route),
        // but double-check they're not somehow peeking at someone else's — defense in depth.
        if ($project->client_id !== request()->user()->id
            && ! request()->user()->can('permission', 'invoices.view_any')) {
            abort(403);
        }

        return InvoiceResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreInvoiceRequest $request, Project $project)
    {
        $invoice = $this->invoices->create($project, $request->validated(), $request->user());

        return new InvoiceResource($invoice);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['items', 'payments']));
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('manage', $invoice);

        $invoice = $this->invoices->transition($invoice, 'sent');

        return new InvoiceResource($invoice);
    }

    public function cancel(Invoice $invoice)
    {
        $this->authorize('manage', $invoice);

        $invoice = $this->invoices->transition($invoice, 'cancelled');

        return new InvoiceResource($invoice);
    }

    public function pay(PayInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoices->pay($invoice, $request->user(), $request->method);

        return new InvoiceResource($invoice->load('payments'));
    }

    public function refund(RefundInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoices->refund($invoice, $request->user(), $request->reason);

        return new InvoiceResource($invoice->load('payments'));
    }
}