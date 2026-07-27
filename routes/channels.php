<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user is allowed to listen to the
| channel.
|
*/

/**
 * Per-user private channel scoped to the authenticated user's clinic.
 *
 * Channel: private-clinic.{clinicId}.user.{userId}
 *
 * Both ids must match the caller; this enforces multi-tenant isolation
 * (an authenticated user in clinic A cannot subscribe to a channel in
 * clinic B even if they happen to know a user id there).
 */
Broadcast::channel('clinic.{clinicId}.user.{userId}', function ($user, $clinicId, $userId) {
    return (int) $user->id === (int) $userId
        && (int) $user->clinic_id === (int) $clinicId;
});
