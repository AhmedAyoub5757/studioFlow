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
    /**
     * @OA\Get(
     *     path="/projects/{project}/invoices",
     *     tags={"Invoices"}, summary="List invoices for a project", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Invoice"))))
     * )
     */
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
        if (
            $project->client_id !== request()->user()->id
            && ! request()->user()->can('permission', 'invoices.view_any')
        ) {
            abort(403);
        }

        return InvoiceResource::collection($query->latest()->paginate(15));
    }

    /**
     * @OA\Post(
     *     path="/projects/{project}/invoices",
     *     tags={"Invoices"}, summary="Create an invoice (Finance / Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"type","items"},
     *         @OA\Property(property="type", type="string", enum={"deposit","milestone","final","subscription"}),
     *         @OA\Property(property="milestone_id", type="integer", nullable=true),
     *         @OA\Property(property="tax", type="number"),
     *         @OA\Property(property="due_date", type="string", format="date"),
     *         @OA\Property(property="items", type="array", @OA\Items(
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="quantity", type="integer"),
     *             @OA\Property(property="unit_price", type="number")
     *         ))
     *     )),
     *     @OA\Response(response=201, description="Created as draft, totals computed server-side", @OA\JsonContent(ref="#/components/schemas/Invoice"))
     * )
     */
    public function store(StoreInvoiceRequest $request, Project $project)
    {
        $invoice = $this->invoices->create($project, $request->validated(), $request->user());

        return new InvoiceResource($invoice);
    }
    /**
     * @OA\Get(
     *     path="/invoices/{invoice}",
     *     tags={"Invoices"}, summary="View an invoice", security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail with items and payments", @OA\JsonContent(ref="#/components/schemas/Invoice"))
     * )
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice->load(['items', 'payments']));
    }
    /**
     * @OA\Post(
     *     path="/invoices/{invoice}/send",
     *     tags={"Invoices"}, summary="Mark invoice as sent (draft→sent)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Sent"),
     *     @OA\Response(response=422, description="Illegal transition from current status")
     * )
     */
    public function send(Invoice $invoice)
    {
        $this->authorize('manage', $invoice);

        $invoice = $this->invoices->transition($invoice, 'sent');

        return new InvoiceResource($invoice);
    }
    /**
     * @OA\Post(
     *     path="/invoices/{invoice}/cancel",
     *     tags={"Invoices"}, summary="Cancel an invoice", security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cancelled")
     * )
     */
    public function cancel(Invoice $invoice)
    {
        $this->authorize('manage', $invoice);

        $invoice = $this->invoices->transition($invoice, 'cancelled');

        return new InvoiceResource($invoice);
    }
    /**
     * @OA\Post(
     *     path="/invoices/{invoice}/pay",
     *     tags={"Invoices"}, summary="Pay an invoice (owning Client only)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"method"},
     *         @OA\Property(property="method", type="string", enum={"card","bank_transfer","manual"})
     *     )),
     *     @OA\Response(response=200, description="Paid, Payment record created"),
     *     @OA\Response(response=422, description="Invoice not in sent/overdue status"),
     *     @OA\Response(response=403, description="Not the project's client")
     * )
     */
    public function pay(PayInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoices->pay($invoice, $request->user(), $request->method);

        return new InvoiceResource($invoice->load('payments'));
    }
    /**
     * @OA\Post(
     *     path="/invoices/{invoice}/refund",
     *     tags={"Invoices"}, summary="Refund a paid invoice (Finance only)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"reason"}, @OA\Property(property="reason", type="string"))),
     *     @OA\Response(response=200, description="Refunded"),
     *     @OA\Response(response=422, description="Invoice not in paid status"),
     *     @OA\Response(response=403, description="Client cannot refund")
     * )
     */
    public function refund(RefundInvoiceRequest $request, Invoice $invoice)
    {
        $invoice = $this->invoices->refund($invoice, $request->user(), $request->reason);

        return new InvoiceResource($invoice->load('payments'));
    }
}
