<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ImageUploadService
{
    public function store(UploadedFile $upload): string
    {
        $bytes = file_get_contents($upload->getRealPath());
        $image = $bytes === false ? false : @imagecreatefromstring($bytes);

        if ($image === false) {
            throw ValidationException::withMessages(['image' => 'This image could not be decoded. Choose another file.']);
        }

        $stream = fopen('php://temp', 'w+b');
        $path = 'products/'.Str::uuid().'.webp';

        try {
            $longEdge = max(imagesx($image), imagesy($image));
            if ($longEdge > 1440) {
                $scaled = imagescale($image, max(1, (int) round(imagesx($image) * 1440 / $longEdge)), max(1, (int) round(imagesy($image) * 1440 / $longEdge)));
                if ($scaled !== false) {
                    $image = $scaled;
                }
            }

            imagepalettetotruecolor($image);
            imagesavealpha($image, true);

            if (! imagewebp($image, $stream, 86) || ftell($stream) === 0) {
                throw ValidationException::withMessages(['image' => 'This image could not be processed. Choose another file.']);
            }

            rewind($stream);
            if (! Storage::disk('public')->put($path, $stream)) {
                throw ValidationException::withMessages(['image' => 'The image could not be saved. Try again.']);
            }
        } finally {
            fclose($stream);
        }

        return $path;
    }

    public function remove(?string $path): void
    {
        if (self::isUpload($path)) {
            try {
                if (! Storage::disk('public')->delete($path)) {
                    logger()->warning('A product image could not be removed.', ['image' => $path]);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public static function publicUrl(?string $path): string
    {
        if ($path && preg_match('#\Ademo/[a-z0-9-]+\.webp\z#', $path) && is_file(public_path('images/'.$path))) {
            return asset('images/'.$path);
        }

        if (self::isUpload($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return asset('images/product-fallback.svg');
    }

    private static function isUpload(?string $path): bool
    {
        return $path !== null && (bool) preg_match('#\Aproducts/[a-f0-9-]{36}\.webp\z#', $path);
    }
}
