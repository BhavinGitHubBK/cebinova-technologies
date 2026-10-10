@extends('layouts.app')

@section('title', 'Marketing Packages | CEBINOVA Technologies')
@section('description', 'Your complete marketing team starting at ₹4,999/month. Compare Regular, Festival and Complete Growth. Clear prices and WhatsApp start.')

@section('content')
    @include('sections.marketing.hero')
    @include('sections.marketing.explorer')

    @include('sections.marketing.comparison')
    @include('sections.marketing.samples')
    @include('sections.marketing.how-it-works')
    @include('sections.marketing.trust')
    @include('sections.marketing.faq')
    @include('sections.marketing.addons')
    @include('sections.home.cta')
    @include('sections.marketing.sticky')
@endsection
