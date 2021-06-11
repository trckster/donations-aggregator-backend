<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('donations', function (User $user) {
    return $user->id;
});
