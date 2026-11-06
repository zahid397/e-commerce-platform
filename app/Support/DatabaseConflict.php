<?php

namespace App\Support;

use Illuminate\Database\QueryException;

final class DatabaseConflict
{
    public static function foreignKey(QueryException $exception): bool
    {
        return in_array((int) ($exception->errorInfo[1] ?? 0), [1451, 1452], true)
            || str_contains($exception->getMessage(), 'FOREIGN KEY constraint failed');
    }
}
