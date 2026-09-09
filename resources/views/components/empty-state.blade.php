@props(['title', 'text', 'href', 'action'])
<div {{ $attributes->class('empty-state') }}>
    <h2 class="empty-state__title">{{ $title }}</h2>
    <p class="empty-state__text">{{ $text }}</p>
    <a class="btn btn-dark empty-state__action" href="{{ $href }}">{{ $action }}</a>
</div>
