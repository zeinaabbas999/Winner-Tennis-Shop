@props(['eyebrow', 'title', 'description' => null])
<header class="admin-page-head">
    <div><small>{{ $eyebrow }}</small><h1>{{ $title }}</h1>@if($description)<p>{{ $description }}</p>@endif</div>
    @isset($action)<div class="admin-page-action">{{ $action }}</div>@endisset
</header>
