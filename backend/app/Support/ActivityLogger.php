<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function record(Model $actor, string $logName, string $logUser): void
    {
        $type = match (true) {
            $actor instanceof Admin => 'admin',
            $actor instanceof User && $actor->isClient() => 'customer',
            default => 'unknown',
        };

        ActivityLog::query()->create([
            'actor_type' => $type,
            'actor_id' => $actor->getKey(),
            'log_name' => $logName,
            'log_user' => $logUser,
            'logged_at' => now(),
        ]);
    }
}
