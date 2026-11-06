<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryRequest;
use App\Models\Category;
use App\Models\Subcategory;
use App\Support\CatalogFilter;
use App\Support\DatabaseConflict;
use App\Support\UniqueSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubcategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = CatalogFilter::from($request);
        $subcategories = Subcategory::with('category')->withCount('products')
            ->when($filters->category !== null, fn ($q) => $q->where('category_id', $filters->category))
            ->when($filters->q !== '', fn ($q) => $q->whereRaw("name LIKE ? ESCAPE '!'", [CatalogFilter::pattern($filters->q)]))
            ->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.subcategories.index', compact('subcategories', 'categories', 'filters'));
    }

    public function create(): View
    {
        return $this->form(new Subcategory);
    }

    public function store(SubcategoryRequest $request): RedirectResponse
    {
        try {
            UniqueSlug::create(Subcategory::class, $request->validated());
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This subcategory already exists under the selected category.']);
        } catch (QueryException $exception) {
            if (! DatabaseConflict::foreignKey($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages(['category_id' => 'That category no longer exists. Choose another one.']);
        }

        return to_route('admin.subcategories.index')->with('success', 'Subcategory created.');
    }

    public function edit(Subcategory $subcategory): View
    {
        return $this->form($subcategory);
    }

    public function update(SubcategoryRequest $request, Subcategory $subcategory): RedirectResponse
    {
        $fields = $request->validated();
        $message = 'Move the products out of this subcategory before changing its parent category.';

        if ((int) $fields['category_id'] !== $subcategory->category_id && $subcategory->products()->exists()) {
            throw ValidationException::withMessages(['category_id' => $message]);
        }

        try {
            $subcategory->update($fields);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This subcategory already exists under the selected category.']);
        } catch (QueryException $exception) {
            if (! DatabaseConflict::foreignKey($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages(['category_id' => 'The parent category changed or this subcategory now has products. Refresh and try again.']);
        }

        return to_route('admin.subcategories.index')->with('success', 'Subcategory updated.');
    }

    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        $message = 'Move or delete the products in this subcategory before deleting it.';
        if ($subcategory->products()->exists()) {
            throw ValidationException::withMessages(['delete' => $message]);
        }

        try {
            $subcategory->delete();
        } catch (QueryException $exception) {
            if (! DatabaseConflict::foreignKey($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages(['delete' => $message]);
        }

        return to_route('admin.subcategories.index')->with('success', 'Subcategory deleted.');
    }

    private function form(Subcategory $subcategory): View
    {
        return view('admin.subcategories.form', [
            'subcategory' => $subcategory,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
