@extends('layouts.admin')
@section('content')
<x-admin-page-header eyebrow="Content" title="Policies" description="Manage the legal and customer-care content published by WINNER." />
<div class="table-responsive admin-table-wrap"><table class="table align-middle admin-table"><thead><tr><th>Policy</th><th>Effective</th><th>Visibility</th><th class="text-end">Action</th></tr></thead><tbody>@foreach($policies as $policy)<tr><td><strong>{{ $policy->title }}</strong><small class="d-block">/{{ $policy->key }}</small></td><td>{{ $policy->effective_date?->format('d M Y') ?: 'Not set' }}</td><td>{{ $policy->is_visible ? 'Published' : 'Hidden' }}</td><td><div class="admin-actions"><a class="btn admin-action-edit" href="{{ route('admin.policies.edit', $policy) }}">Edit</a></div></td></tr>@endforeach</tbody></table></div>
@endsection
