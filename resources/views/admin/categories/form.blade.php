@extends('layouts.admin')

@section('content')
    <x-admin-page-header
        eyebrow="Catalogue"
        :title="$category->exists ? 'Edit category' : 'Add category'"
        :description="$category->exists
            ? 'Update this category\'s name and description.'
            : 'Create a new category for organizing products.'"
    />

    <form method="POST" class="admin-category-form" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($category->exists)
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input id="name" class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" class="form-control" rows="5" name="description">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="admin-form-actions">
            <button class="btn btn-dark" type="submit">Save category</button>
            <a class="btn btn-outline-dark" href="{{ route('admin.categories.index') }}">Cancel</a>
        </div>
    </form>
@endsection
