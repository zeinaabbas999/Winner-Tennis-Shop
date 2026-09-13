@extends('layouts.store')

@section('title', 'Shipping, Returns and Exchanges')
@section('meta_description', 'WINNER shipping, returns and exchange policies.')

@section('content')
    <article class="legal-document">
        <header class="info-page-header">
            <div class="container info-page-header__inner">
                <div class="legal-document__intro">
                    <p class="eyebrow text-green">Customer care</p>
                    <h1>Policies</h1>
                    <p>Clear information for delivery, returns and exchanges.</p>
                </div>
            </div>
        </header>

        <div class="container info-page-container legal-layout">
            <nav aria-label="Policies contents">
                <strong>Contents</strong>
                @foreach ($policies as $policy)
                    <a href="#{{ $policy->key }}">{{ $policy->title }}</a>
                @endforeach
            </nav>

            <div class="legal-copy">
                @foreach ($policies as $policy)
                    <section id="{{ $policy->key }}">
                        <h2>{{ $policy->title }}</h2>
                        <p>{{ $policy->intro }}</p>
                        @foreach ($policy->sections as $section)
                            <h3>{{ $section['heading'] }}</h3>
                            <p class="policy-copy">{{ $section['content'] }}</p>
                        @endforeach
                        <small>
                            Effective {{ $policy->effective_date?->format('d F Y') }}
                            · Last updated {{ $policy->updated_at->format('d F Y') }}
                        </small>
                        @if(in_array($policy->key, ['returns', 'exchanges'], true))
                            @include('pages.partials.policy-request', ['type' => $policy->key === 'returns' ? 'return' : 'exchange', 'items' => $eligibleItems[$policy->key === 'returns' ? 'return' : 'exchange'] ?? collect()])
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </article>
@endsection
