<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Milestone;

class MilestoneService
{
    public function create(Project $project, array $data): Milestone
    {
        return Milestone::create([...$data, 'project_id' => $project->id]);
    }
}
