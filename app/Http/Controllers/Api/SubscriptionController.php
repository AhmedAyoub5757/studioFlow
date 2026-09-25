<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\UpdateSubscriptionRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Project;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/projects/{project}/subscriptions",
     *     tags={"Subscriptions"}, summary="List subscriptions for a project", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List")
     * )
     */
    protected SubscriptionService $subscriptions;

    public function __construct(SubscriptionService $subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        return SubscriptionResource::collection($project->subscriptions()->get());
    }
    /**
     * @OA\Post(
     *     path="/projects/{project}/subscriptions",
     *     tags={"Subscriptions"}, summary="Create a maintenance subscription (Finance)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","amount","billing_cycle","started_at"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="amount", type="number"),
     *         @OA\Property(property="billing_cycle", type="string", enum={"monthly","quarterly","yearly"}),
     *         @OA\Property(property="started_at", type="string", format="date")
     *     )),
     *     @OA\Response(response=201, description="Created, next_billing_date computed")
     * )
     */

    public function store(StoreSubscriptionRequest $request, Project $project)
    {
        $subscription = $this->subscriptions->create($project, $request->validated());

        return new SubscriptionResource($subscription);
    }
    /**
     * @OA\Put(
     *     path="/subscriptions/{subscription}",
     *     tags={"Subscriptions"}, summary="Update a subscription (pause/cancel/change amount)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="subscription", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="status", type="string", enum={"active","paused","cancelled"}),
     *         @OA\Property(property="amount", type="number")
     *     )),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $subscription->update($request->validated());

        return new SubscriptionResource($subscription);
    }
    /**
     * @OA\Post(
     *     path="/subscriptions/{subscription}/generate-invoice",
     *     tags={"Subscriptions"}, summary="Generate this cycle's invoice and advance next_billing_date",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="subscription", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Invoice generated", @OA\JsonContent(ref="#/components/schemas/Invoice"))
     * )
     */
    public function generateInvoice(Subscription $subscription, InvoiceService $invoices)
    {
        $this->authorize('manage', Subscription::class);

        $invoice = $this->subscriptions->generateInvoiceForCycle(
            $subscription,
            $invoices,
            request()->user()
        );

        return new InvoiceResource($invoice);
    }
}
