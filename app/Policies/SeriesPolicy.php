<?php

namespace App\Policies;

use App\Models\Series;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SeriesPolicy
{
    use HandlesAuthorization;

    /**
     * Check whether the current user can view all series in index
     */
    public function index(User $user): bool
    {
        return auth()->check() && ($user->isAdmin() || $user->isAssistant());
    }

    /**
     * Check whether the current user can create a series.
     */
    public function create(User $user): bool
    {
        return auth()->check() && ($user->isModerator() || $user->isAdmin() || $user->isAssistant());
    }

    public function view(?User $user, Series $series): Response
    {
        /*
         * return true only for the following:
         * - series is Public and contains clips with assets
         * - user is series owner
         * - user is  admin or superadmin
         */

        return (
            ($series->is_public && $series->clips->filter(
                fn ($clip) => $clip->assets()->count() || $clip->is_livestream
            )->count() > 0
            )
            || (optional($user)->is($series->owner) || optional($user)->isAdmin() || optional($user)->isAssistant())
        )
            ? Response::allow()
            : Response::deny('You do not own this series');
    }

    /**
     * Check whether the given user can edit the given series
     */
    public function edit(User $user, Series $series): Response
    {
        return (
            ($user->isAdmin() || $user->isAssistant()) ||
            ($user->is($series->owner) ||
                $user->isMemberOf($series))

        )
            ? Response::allow()
            : Response::deny('You do not own this series');
    }

    /**
     * Check whether the current user can create a series.
     */
    public function update(User $user, Series $series): Response
    {
        //assistants are not allowed to update series info
        return ($user->is($series->owner) || ($user->isAdmin()))
            ? Response::allow()
            : Response::deny('You do not own this series');
    }

    /**
     * Check whether the given user can delete the given series
     */
    public function delete(User $user, Series $series): Response
    {
        return $user->is($series->owner) || $user->isAdmin()
            ? Response::allow()
            : Response::deny('You do not own this series');
    }

    public function changeOwner(User $user): bool
    {
        return $user->isAdmin();
    }
}
