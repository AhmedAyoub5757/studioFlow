<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DecideApprovalRequest;
use App\Http\Requests\RequestApprovalRequest;
use App\Http\Resources\ApprovalResource;
use App\Models\Milestone;
use App\Services\ApprovalService;

class ApprovalController extends Controller
{
    protected ApprovalService $approvals;

    public function __construct(ApprovalService $approvals)
    {
        $this->approvals = $approvals;
    }

    public function index(Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return ApprovalResource::collection(
            $milestone->approvals()->with(['requester', 'decider'])->get()
        );
    }

    public function request(RequestApprovalRequest $request, Milestone $milestone)
    {
        $approval = $this->approvals->request($milestone, $request->user());

        return new ApprovalResource($approval->load('requester'));
    }

    public function decide(DecideApprovalRequest $request, Milestone $milestone)
    {
        $approval = $this->approvals->decide(
            $milestone,
            $request->user(),
            $request->status,
            $request->decision_note
        );

        return new ApprovalResource($approval->load(['requester', 'decider']));
    }
}