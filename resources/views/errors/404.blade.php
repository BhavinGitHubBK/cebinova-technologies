@extends('layouts.app')

@section('title', 'Page not found | CEBINOVA Technologies')

@section('content')
    <section class="flex min-h-[70vh] items-center pt-28">
        <div class="container-wide max-w-xl py-20">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">404</p>
            <h1 class="page-hero-title mt-3">This page is not available.</h1>
            <p class="mt-4 text-muted">The link may be incorrect, or the page has moved. Return to the homepage or tell us what you were looking for.</p>
            <div class="mt-8 flex gap-3">
                <x-button href="{{ route('home') }}">Back to home</x-button>
                <x-button href="{{ route('contact') }}" variant="outline">Contact</x-button>
            </div>
        </div>
    </section>
@endsection
