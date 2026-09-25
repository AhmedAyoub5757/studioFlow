<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class DashboardService
{
    public function build(User $user): array
    {
        if ($user->hasRole('super_admin') || $user->hasRole('agency_manager')) {
            return $this->agencyOverview($user);
        }

        if ($user->hasRole('project_manager')) {
            return $this->pmDashboard($user);
        }

        if ($user->hasRole('developer') || $user->hasRole('designer')) {
            return $this->staffDashboard($user);
        }

        if ($user->hasRole('qa')) {
            return $this->qaDashboard($user);
        }

        if ($user->hasRole('finance')) {
            return $this->financeDashboard($user);
        }

        if ($user->hasRole('client')) {
            return $this->clientDashboard($user);
        }

        return ['message' => 'No dashboard available for your role.'];
    }

    /** Agency-wide bird's-eye view */
    private function agencyOverview(User $user): array
    {
        return [
            'role' => 'agency_overview',
            'projects' => [
                'total' => Project::count(),
                'in_progress' => Project::where('status', 'in_progress')->count(),
                'completed' => Project::where('status', 'completed')->count(),
            ],
            'tasks' => [
                'open' => Task::whereIn('status', ['todo', 'in_progress', 'in_review'])->count(),
                'overdue' => Task::where('due_date', '<', now())->whereNot('status', 'done')->count(),
            ],
            'bugs' => [
                'open' => Bug::whereIn('status', ['open', 'in_progress'])->count(),
                'critical_open' => Bug::where('severity', 'critical')
                    ->whereIn('status', ['open', 'in_progress'])->count(),
            ],
            'invoices' => [
                'outstanding_total' => Invoice::whereIn('status', ['sent', 'overdue'])->sum('total'),
                'paid_this_month' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)->sum('total'),
            ],
        ];
    }

    /** PM sees only their managed projects, but the full operational picture within them */
    private function pmDashboard(User $user): array
    {
        $projectIds = $user->projectsAsStaff()
            ->wherePivot('role_on_project', 'manager')
            ->pluck('projects.id');

        return [
            'role' => 'project_manager',
            'managed_projects' => Project::whereIn('id', $projectIds)->count(),
            'tasks_by_status' => Task::whereIn('project_id', $projectIds)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')->pluck('count', 'status'),
            'open_bugs' => Bug::whereIn('project_id', $projectIds)
                ->whereIn('status', ['open', 'in_progress'])->count(),
            'pending_approvals' => \App\Models\Approval::whereHasMorph('approvable', ['milestone'], function ($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })->where('status', 'pending')->count(),
            'outstanding_invoices' => Invoice::whereIn('project_id', $projectIds)
                ->whereIn('status', ['sent', 'overdue'])->count(),
        ];
    }

    /** Developer/Designer: exactly what's on their plate */
    private function staffDashboard(User $user): array
    {
        return [
            'role' => 'staff',
            'my_tasks' => [
                'todo' => Task::where('assigned_to', $user->id)->where('status', 'todo')->count(),
                'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'in_review' => Task::where('assigned_to', $user->id)->where('status', 'in_review')->count(),
                'overdue' => Task::where('assigned_to', $user->id)
                    ->where('due_date', '<', now())->whereNot('status', 'done')->count(),
            ],
            'my_open_bugs' => Bug::where('assigned_to', $user->id)
                ->whereIn('status', ['open', 'in_progress', 'fixed'])->count(),
            'hours_logged_this_week' => $user->timeLogs()
                ->whereBetween('log_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours'),
        ];
    }

    /** QA: triage view across all projects they're staffed on */
    private function qaDashboard(User $user): array
    {
        $projectIds = $user->projectsAsStaff()->wherePivot('role_on_project', 'qa')->pluck('projects.id');

        return [
            'role' => 'qa',
            'bugs_to_verify' => Bug::whereIn('project_id', $projectIds)->where('status', 'fixed')->count(),
            'open_bugs_by_severity' => Bug::whereIn('project_id', $projectIds)
                ->whereIn('status', ['open', 'in_progress'])
                ->selectRaw('severity, count(*) as count')
                ->groupBy('severity')->pluck('count', 'severity'),
            'my_reported_bugs' => Bug::where('reported_by', $user->id)->count(),
        ];
    }

    /** Finance: money in, money outstanding, agency-wide */
    private function financeDashboard(User $user): array
    {
        return [
            'role' => 'finance',
            'invoices' => [
                'draft' => Invoice::where('status', 'draft')->count(),
                'sent' => Invoice::where('status', 'sent')->count(),
                'overdue' => Invoice::where('status', 'overdue')->count(),
                'paid_this_month' => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)->sum('total'),
            ],
            'outstanding_total' => Invoice::whereIn('status', ['sent', 'overdue'])->sum('total'),
            'active_subscriptions' => \App\Models\Subscription::where('status', 'active')->count(),
            'subscriptions_due_this_week' => \App\Models\Subscription::where('status', 'active')
                ->whereBetween('next_billing_date', [now(), now()->addWeek()])->count(),
        ];
    }

    /** Client: their own project(s), delivery + billing status */
    private function clientDashboard(User $user): array
    {
        $projects = Project::where('client_id', $user->id)->get();

        return [
            'role' => 'client',
            'projects' => $projects->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'status' => $p->status,
                    'pending_milestone_approvals' => $p->milestones()
                        ->whereHas('approvals', function ($q) {
                            return $q->where('status', 'pending');
                        })->count(),
                    'outstanding_invoice_total' => $p->invoices()
                        ->whereIn('status', ['sent', 'overdue'])->sum('total'),
                ];
            }),
        ];
    }
}