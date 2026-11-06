<?php

namespace App\Services;

use App\Models\Product;
use App\Support\UniqueSlug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProductService
{
    public function __construct(private ImageUploadService $images) {}

    public function create(array $fields, UploadedFile $upload): Product
    {
        $path = $this->images->store($upload);

        try {
            return UniqueSlug::create(Product::class, $fields + ['image' => $path]);
        } catch (Throwable $exception) {
            $this->images->remove($path);

            throw $exception;
        }
    }

    public function update(Product $product, array $fields, ?UploadedFile $upload): void
    {
        $newImage = $upload ? $this->images->store($upload) : null;

        try {
            $oldImage = DB::transaction(function () use ($product, $fields, $newImage): ?string {
                $current = Product::lockForUpdate()->findOrFail($product->id);
                $oldImage = $current->image;
                $current->update($newImage ? $fields + ['image' => $newImage] : $fields);

                return $oldImage;
            });
        } catch (Throwable $exception) {
            $this->images->remove($newImage);

            throw $exception;
        }

        if ($newImage) {
            $this->images->remove($oldImage);
        }
    }

    public function delete(Product $product): void
    {
        $image = DB::transaction(function () use ($product): string {
            $current = Product::lockForUpdate()->findOrFail($product->id);
            $image = $current->image;
            $current->delete();

            return $image;
        });

        $this->images->remove($image);
    }
}
