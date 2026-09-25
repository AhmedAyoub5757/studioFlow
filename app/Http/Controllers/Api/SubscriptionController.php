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

    public function store(StoreSubscriptionRequest $request, Project $project)
    {
        $subscription = $this->subscriptions->create($project, $request->validated());

        return new SubscriptionResource($subscription);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription)
    {
        $subscription->update($request->validated());

        return new SubscriptionResource($subscription);
    }

    public function generateInvoice(Subscription $subscription, InvoiceService $invoices)
    {
        $this->authorize('manage', Subscription::class);

        $invoice = $this->subscriptions->generateInvoiceForCycle(
            $subscription, $invoices, request()->user()
        );

        return new InvoiceResource($invoice);
    }
}