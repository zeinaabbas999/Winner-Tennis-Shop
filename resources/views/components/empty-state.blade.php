@props(['title', 'text', 'href', 'action', 'kind' => 'default'])
<div {{ $attributes->class('empty-state') }}>
    @if($kind === 'wishlist')
        <svg class="empty-state__art" viewBox="0 0 120 90" aria-hidden="true"><path d="M60 72 29 43C12 27 38 7 60 29 82 7 108 27 91 43Z"/><circle cx="94" cy="67" r="11"/><path d="M88 61c5 2 9 6 12 12M20 72c10-7 18-8 28-5"/></svg>
    @elseif($kind === 'orders')
        <svg class="empty-state__art" viewBox="0 0 120 90" aria-hidden="true"><path d="m27 35 33-17 33 17-33 17zM27 35v35l33 16 33-16V35M60 52v34M19 20c8-6 16-8 25-8M96 15l7 7-13 13"/></svg>
    @else
        <svg class="empty-state__art" viewBox="0 0 120 90" aria-hidden="true"><path d="M24 31h72l-8 43H32zM43 31c0-12 7-20 17-20s17 8 17 20M44 46c9 7 23 7 32 0"/><circle cx="91" cy="18" r="10"/><path d="M86 13c4 1 8 5 10 10"/></svg>
    @endif
    <h2 class="empty-state__title">{{ $title }}</h2>
    <p class="empty-state__text">{{ $text }}</p>
    <a class="btn btn-dark empty-state__action" href="{{ $href }}">{{ $action }}</a>
</div>
