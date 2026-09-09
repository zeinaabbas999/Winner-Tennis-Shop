@extends('layouts.store')

@section('title', 'Contact')

@section('content')
    <header class="info-page-header">
        <div class="container info-page-header__inner">
            <div>
                <p class="eyebrow text-green">Contact WINNER</p>
                <h1>Talk tennis<br>with us</h1>
            </div>
            <p>Questions about stock, racket specifications or choosing equipment? Tell us what you play with and what you want to improve.</p>
        </div>
    </header>

    <div class="info-page-content">
        <div class="container contact-editorial">
            <section class="contact-editorial__primary">
                <p class="eyebrow">Email</p>
                <h2>Start the conversation</h2>
                <p>Include your racket model or the product name when relevant. We will help you find the practical next step.</p>
                <div class="contact-editorial__actions">
                    <a class="btn btn-lime" href="mailto:winner.tennis@gmail.com">Email WINNER</a>
                    <a class="contact-email" href="mailto:winner.tennis@gmail.com">winner.tennis@gmail.com</a>
                </div>
            </section>
            <div class="contact-editorial__details">
                <article><div><p class="eyebrow text-green">Location</p><h3>Cairo, Egypt</h3><p>WINNER is an online tennis store. Collection details are shared when confirmed.</p></div></article>
                <article><div><p class="eyebrow text-green">Phone</p><h3>Available soon</h3><p>A verified store number will be published here when available.</p></div></article>
                <article><div><p class="eyebrow text-green">Useful details</p><h3>Help us help you</h3><p>Share your playing level, current racket and what you want to improve so our advice stays practical.</p></div></article>
            </div>
        </div>
    </div>
@endsection
