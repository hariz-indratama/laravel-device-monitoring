<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;

class OutletPolicy
{
    /**
     * Determine if the user can view the outlet.
     */
    public function view(User $user, Outlet $outlet): bool
    {
        return $user->id === $outlet->user_id;
    }

    /**
     * Determine if the user can update the outlet.
     */
    public function update(User $user, Outlet $outlet): bool
    {
        return $user->id === $outlet->user_id;
    }

    /**
     * Determine if the user can delete the outlet.
     */
    public function delete(User $user, Outlet $outlet): bool
    {
        return $user->id === $outlet->user_id;
    }
}
