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
    /**
     * @OA\Get(
     *     path="/milestones/{milestone}/approvals",
     *     tags={"Approvals"}, summary="List approval history for a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/milestones/{milestone}/approvals/request",
     *     tags={"Approvals"}, summary="Request client approval (PM / Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=201, description="Approval requested, milestone marked completed"),
     *     @OA\Response(response=422, description="An approval is already pending")
     * )
     */

    public function request(RequestApprovalRequest $request, Milestone $milestone)
    {
        $approval = $this->approvals->request($milestone, $request->user());

        return new ApprovalResource($approval->load('requester'));
    }
    /**
     * @OA\Post(
     *     path="/milestones/{milestone}/approvals/decide",
     *     tags={"Approvals"}, summary="Approve or reject (project's Client only, or Agency Manager)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"status"},
     *         @OA\Property(property="status", type="string", enum={"approved","rejected"}),
     *         @OA\Property(property="decision_note", type="string", description="Required if status is rejected")
     *     )),
     *     @OA\Response(response=200, description="Decision recorded"),
     *     @OA\Response(response=422, description="decision_note required for rejection, or no pending approval exists"),
     *     @OA\Response(response=403, description="Not the owning client")
     * )
     */
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
