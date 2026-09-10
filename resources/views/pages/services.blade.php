@extends('layouts.store')

@section('title', 'Tennis Services')

@section('content')
    <header class="info-page-header">
        <div class="container info-page-header__inner">
            <div>
                <p class="eyebrow text-green">WINNER services</p>
                <h1>Support for<br>your equipment</h1>
            </div>
        </div>
    </header>

    <div class="info-page-content">
        <div class="container service-editorial-list">
            @foreach ([['Racket Stringing', 'For players replacing broken, worn or performance-focused strings. We can discuss string type and a suitable tension range.', 'Strings selected around feel, durability and playing frequency.'], ['Grip Replacement', 'For worn base grips or players who want a cleaner, more secure feel in the hand.', 'A fresh grip restores comfort and dependable contact.'], ['Racket Setup Assistance', 'For help understanding string, overgrip and dampener options around your current racket.', 'Small setup choices can noticeably change the response.'], ['Racket Selection Advice', 'For players comparing head size, weight, balance and string pattern before purchasing.', 'A practical shortlist based on level, swing and preferred feel.']] as [$name, $description, $detail])
                <article>
                    <div>
                        <h2>{{ $name }}</h2>
                        <p>{{ $description }}</p><small>{{ $detail }}</small>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="container info-contact-note">
            <p class="eyebrow text-green">Arrange a service</p>
            <p>Email <a href="mailto:winner.tennis@gmail.com">winner.tennis@gmail.com</a> with your racket model and what you
                need. We will confirm the practical next step.</p>
        </div>
    </div>
@endsection
