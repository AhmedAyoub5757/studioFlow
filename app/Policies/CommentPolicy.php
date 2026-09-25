<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Can $user comment on $commentable (a Task or Milestone)?
     * Rule: anyone who can VIEW the parent project can comment.
     * We reuse the parent's own `view` policy check via its `project` relation.
     */
    public function create(User $user, $commentable): bool
    {
        return $user->can('view', $commentable->project);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id; // only the author can edit their own comment
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true; // author can always delete their own comment
        }

        return $user->can('permission', 'comments.delete_any'); // moderation override
    }
}
