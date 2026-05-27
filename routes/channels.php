<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth', 'throttle:30,1']]);

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('audit-logs', function (User $user) {
    return $user->can('audit.view');
});

Broadcast::channel('imports.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});
