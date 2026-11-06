<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

final class UniqueSlug
{
    /**
     * Slugs are assigned once. The unique index arbitrates concurrent inserts.
     *
     * @template T of Model
     *
     * @param  class-string<T>  $model
     * @return T
     */
    public static function create(string $model, array $fields): Model
    {
        $base = rtrim(substr(Str::slug($fields['name']), 0, 140), '-') ?: 'item';

        for ($i = 1; $i <= 50; $i++) {
            $suffix = $i === 1 ? '' : '-'.$i;
            if ($i === 50) {
                $suffix = '-'.Str::lower(Str::random(12));
            }
            $slug = $base.$suffix;

            if ($model::where('slug', $slug)->exists()) {
                continue;
            }

            try {
                return $model::create($fields + ['slug' => $slug]);
            } catch (UniqueConstraintViolationException $exception) {
                // A different unique index (e.g. category name) needs its own feedback.
                if (! $model::where('slug', $slug)->exists() || $i === 50) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('A unique catalogue URL could not be allocated.');
    }
}
