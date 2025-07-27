<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Izinkan admin, kasubbag, sekretaris, dan ppk untuk melihat semua SPPD
        return in_array($user->role, ['admin', 'kasubbag', 'sekretaris', 'ppk']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TravelRequest $travelRequest)
    {
        // Izinkan admin, kasubbag, sekretaris, ppk untuk melihat SPPD apapun
        if (in_array($user->role, ['admin', 'kasubbag', 'sekretaris', 'ppk'])) {
            return true;
        }

        // Izinkan user melihat SPPD miliknya sendiri
        return $user->id === $travelRequest->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Hanya kasubbag yang bisa membuat SPPD baru
        return $user->role === 'kasubbag';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TravelRequest $travelRequest)
    {
        // Hanya pengaju yang bisa mengupdate SPPD miliknya sendiri
        // Dan hanya jika statusnya 'in_review' atau 'revision'
        return $user->id === $travelRequest->user_id &&
               in_array($travelRequest->status, ['in_review', 'revision']);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TravelRequest $travelRequest)
    {
        // Hanya pengaju yang bisa menghapus SPPD miliknya sendiri
        // Dan hanya jika statusnya 'in_review'
        return $user->id === $travelRequest->user_id &&
               $travelRequest->status === 'in_review';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TravelRequest $travelRequest)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TravelRequest $travelRequest)
    {
        //
    }
}
