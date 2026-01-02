<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Quest;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('quest.{questId}', function ($user, $questId) {
    $quest = Quest::find($questId);
    
    if (!$quest) {
        return false;
    }
    
    // Only allow users who are part of this quest (patron or adventurer)
    return $quest->patron_id === $user->id || $quest->adventurer_id === $user->id;
});

