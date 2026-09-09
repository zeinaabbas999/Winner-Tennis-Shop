@extends('layouts.store')

@section('title', 'How to Choose a Tennis Racket')

@section('content')
    <header class="info-page-header info-page-header--guide">
        <div class="container info-page-header__inner">
            <div>
                <p class="eyebrow text-green">WINNER guide</p>
                <h1>How to choose<br>a tennis racket</h1>
            </div>
            <div class="info-page-intro">
                <p>Start with your level and swing. Specifications refine the choice, but comfort in your hand matters most.</p>
                <small><strong>Before you choose</strong>The best specification is the one you can swing comfortably for a full session.</small>
            </div>
        </div>
    </header>

    <div class="info-page-content">
        <div class="container info-sections">
            <section class="info-section">
                <header class="info-section__heading"><span>01</span><div><p class="eyebrow text-green">Playing level</p><h2>Start with your level</h2></div></header>
                <div class="info-section__body guide-level-list">
                    @foreach([
                        ['Beginner', 'Look for easy handling and forgiveness. A larger head, often around 105 to 110 square inches, gives a larger effective hitting area. A lighter frame can be easier to swing.'],
                        ['Intermediate', 'A 100 to 105 square inch head often balances forgiveness and precision. Choose weight you can swing comfortably through a full session without losing timing.'],
                        ['Advanced', 'Experienced players may prefer a 95 to 100 square inch head and more mass for stability and control. These frames generally reward sound technique and faster swings.'],
                    ] as [$title, $copy])
                        <article><h3>{{ $title }}</h3><p>{{ $copy }}</p><a href="{{ route('shop.category', 'tennis-rackets') }}">Browse rackets</a></article>
                    @endforeach
                </div>
            </section>

            <section class="info-section">
                <header class="info-section__heading"><span>02</span><div><p class="eyebrow text-green">Racket specifications</p><h2>Understand the frame</h2></div></header>
                <div class="info-section__body guide-spec-list">
                    @foreach([
                        ['01', 'Head size', 'Larger heads generally offer a larger sweet spot and more forgiveness. Smaller heads tend to favor precision.'],
                        ['02', 'Weight', 'More mass can improve stability through impact, but it requires enough strength and preparation. Lighter rackets are easier to maneuver.'],
                        ['03', 'Balance', 'Head-light frames feel easier to move at the net. Head-heavy frames can add momentum. Even balance aims for a middle ground.'],
                        ['04', 'String pattern', 'Open patterns such as 16 x 19 often support spin and power. Denser patterns such as 18 x 20 generally offer control.'],
                        ['05', 'Power and control', 'Larger, lighter and more open setups usually lean toward accessible power. Smaller, heavier and denser setups typically emphasize control.'],
                        ['06', 'Grip size', 'Use a grip that feels secure without squeezing. When uncertain between two sizes, choose the smaller size and add an overgrip.'],
                    ] as [$number, $title, $copy])
                        <article><span>{{ $number }}</span><div><h3>{{ $title }}</h3><p>{{ $copy }}</p></div></article>
                    @endforeach
                </div>
            </section>

            <section class="info-section info-section--closing">
                <header class="info-section__heading"><span>03</span><div><p class="eyebrow text-green">Final recommendation</p><h2>Comfort first</h2></div></header>
                <div class="info-section__body"><p>Comfort is personal. Demo a racket when possible and avoid treating any specification as a medical recommendation. A frame that feels natural through a full session is more useful than one chosen only from a specification sheet.</p><a class="btn btn-dark" href="{{ route('shop.category', 'tennis-rackets') }}">Shop rackets</a></div>
            </section>
        </div>
    </div>
@endsection
