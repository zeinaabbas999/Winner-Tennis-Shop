<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('name')->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(SaveCategoryRequest $request): RedirectResponse
    {
        Category::create($this->data($request));

        return to_route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(SaveCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->data($request, $category));

        return to_route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('warning', 'Move or delete this category’s products first.');
        } $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function data(SaveCategoryRequest $request, ?Category $category = null): array
    {
        $data = $request->validated();
        $base = Str::slug($data['name']) ?: 'category';
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->when($category, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return array_merge($data, ['slug' => $slug]);
    }
}
