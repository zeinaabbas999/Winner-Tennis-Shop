@extends('layouts.admin') @section('content')
    <x-admin-page-header eyebrow="Catalogue" title="Categories"
        description="Organize the collections customers use to browse the store.">
        <x-slot:action><a class="btn btn-dark" href="{{ route('admin.categories.create') }}">Add category</a></x-slot:action></x-admin-page-header>
    <div class="table-responsive">
        <table class="table align-middle admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <div class="admin-actions"><a class="btn admin-action-edit"
                                    href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                    onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button
                                        class="btn admin-action-delete">Delete</button></form>
                            </div>
                        </td>
                </tr>@empty<tr>
                        <td colspan="4">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>{{ $categories->links() }}
@endsection
