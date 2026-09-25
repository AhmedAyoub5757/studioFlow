<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;

class CommentService
{
    public function create($commentable, string $body, User $user): Comment
    {
        return $commentable->comments()->create([
            'body' => $body,
            'user_id' => $user->id,
        ]);
    }
}