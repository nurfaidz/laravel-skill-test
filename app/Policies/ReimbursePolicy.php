<?php

namespace App\Policies;

use App\Models\Reimburse;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReimbursePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reimburse $reimburse): Response
    {
        if (! $reimburse) {
            return Response::denyAsNotFound();
        }

        return $user->id === $reimburse->requested_by
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reimburse $reimburse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reimburse $reimburse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reimburse $reimburse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reimburse $reimburse): bool
    {
        return false;
    }
}
