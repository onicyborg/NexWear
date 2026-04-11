<?php

namespace App\Support;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogger
{
    public static function record(string $action, string $table, string $recordId, array $old = null, array $new = null): void
    {
        $req = request();

        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_values' => $old,
            'new_values' => $new,
            'method' => $req?->method(),
            'url' => $req?->fullUrl(),
            'request_payload' => $req?->all(),
            'ip_address' => $req?->ip(),
        ]);
    }
}
