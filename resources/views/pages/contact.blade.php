@extends('layouts.store')

@section('title', 'Contact')

@section('content')
    <header class="info-page-header">
        <div class="container info-page-header__inner">
            <div>
                <p class="eyebrow text-green">Contact WINNER</p>
                <h1>Talk tennis<br>with us</h1>
            </div>
        </div>
    </header>

    <div class="info-page-content">
        <div class="container contact-editorial">
            <section class="contact-editorial__primary">
                <p class="eyebrow">Email</p>
                <h2>Start the conversation</h2>
                <p>Include your racket model or the product name when relevant. We will help you find the practical next
                    step.</p>
                <div class="contact-editorial__actions">
                    <a class="btn btn-lime" href="mailto:winner.tennis999@gmail.com">Email WINNER</a>
                    <a class="contact-email" href="mailto:winner.tennis999@gmail.com">winner.tennis999@gmail.com</a>
                </div>
            </section>
            <div class="contact-editorial__details">
                <article>
                    <div>
                        <p class="eyebrow text-green">Location</p>
                        <h3>Cairo, Egypt</h3>
                        <p>WINNER is an online tennis store. Collection details are shared when confirmed.</p>
                    </div>
                </article>
                <article>
                    <div>
                        <p class="eyebrow text-green">Phone</p>
                        <h3><a href="tel:01123456789">01123456789</a></h3>
                        <p>Call WINNER for practical product and order support.</p>
                    </div>
                </article>
                <article>
                    <div>
                        <p class="eyebrow text-green">Useful details</p>
                        <h3>Help us help you</h3>
                        <p>Share your playing level, current racket and what you want to improve so our advice stays
                            practical.</p>
                    </div>
                </article>
            </div>
            <section class="contact-question customer-panel">
                <p class="eyebrow text-green">Quick question</p>
                <h2>Ask us a quick question</h2>
                <form class="contact-question-form" method="POST" action="{{ route('questions.store') }}">@csrf
                    <div class="row g-3"><div class="col-md-6"><label class="form-label" for="question-name">Name</label><input class="form-control" id="question-name" name="name" value="{{ old('name', auth()->user()?->name) }}" maxlength="255" required></div><div class="col-md-6"><label class="form-label" for="question-email">Email</label><input class="form-control" id="question-email" type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" maxlength="255" required></div><div class="col-12"><label class="form-label" for="question">Question</label><textarea class="form-control" id="question" name="question" rows="5" maxlength="2000" required>{{ old('question') }}</textarea></div></div>
                    <button class="btn btn-dark mt-3">Submit question</button>
                </form>
            </section>
        </div>
    </div>
@endsection
