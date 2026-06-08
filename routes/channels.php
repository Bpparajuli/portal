<?php

use Illuminate\Support\Facades\Broadcast;

// Private chat channel for real-time messaging
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Private typing indicator channel
Broadcast::channel('typing.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
