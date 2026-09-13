@extends('layouts.store')

@section('title', $policy->title)
@section('meta_description', $policy->intro)

@section('content')
    <article class="legal-document">
        <header class="info-page-header">
            <div class="container info-page-header__inner">
                <div class="legal-document__intro">
                    <p class="eyebrow text-green">{{ $policy->eyebrow }}</p>
                    <h1>{{ $policy->title }}</h1>
                    <p>{{ $policy->intro }}</p>
                    <small>
                        Effective {{ $policy->effective_date?->format('d F Y') }}
                        · Last updated {{ $policy->updated_at->format('d F Y') }}
                    </small>
                </div>
            </div>
        </header>

        <div class="container info-page-container legal-layout">
            <nav aria-label="{{ $policy->title }} contents">
                <strong>Contents</strong>
                @foreach ($policy->sections as $index => $section)
                    <a href="#{{ $policy->key }}-{{ $index + 1 }}">{{ $section['heading'] }}</a>
                @endforeach
            </nav>

            <div class="legal-copy">
                @foreach ($policy->sections as $index => $section)
                    <section id="{{ $policy->key }}-{{ $index + 1 }}">
                        <h2>{{ $section['heading'] }}</h2>
                        <p class="policy-copy">{{ $section['content'] }}</p>
                    </section>
                @endforeach
            </div>
        </div>
        @if(in_array($policy->key, ['returns', 'exchanges'], true))
            @include('pages.partials.policy-request', ['type' => $policy->key === 'returns' ? 'return' : 'exchange', 'items' => $eligibleItems[$policy->key === 'returns' ? 'return' : 'exchange'] ?? collect()])
        @endif
    </article>
@endsection
