<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\CatalogFilter;
use App\Support\DatabaseConflict;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $q = CatalogFilter::text($request->query('q'));
        $categories = Category::withCount(['subcategories', 'products'])
            ->when($q !== '', fn ($query) => $query->whereRaw("name LIKE ? ESCAPE '!'", [CatalogFilter::pattern($q)]))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.categories.index', compact('categories', 'q'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        try {
            UniqueSlug::create(Category::class, $request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This category already exists.']);
        }

        return to_route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $category->update($request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This category already exists.']);
        }

        return to_route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $message = 'Move or delete this category’s subcategories and products before deleting it.';

        if ($category->subcategories()->exists() || $category->products()->exists()) {
            throw ValidationException::withMessages(['delete' => $message]);
        }

        try {
            $category->delete();
        } catch (QueryException $exception) {
            if (! DatabaseConflict::foreignKey($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages(['delete' => $message]);
        }

        return to_route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
