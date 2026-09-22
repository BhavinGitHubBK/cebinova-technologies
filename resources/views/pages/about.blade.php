@extends('layouts.app')

@section('title', 'About CEBINOVA Technologies | Technology for Every Business')
@section('description', 'CEBINOVA Technologies helps businesses of every size use technology to start, operate, automate and grow - as one technology partner for the complete business journey.')

@section('content')
    @include('sections.about.hero')
    @include('sections.about.who')
    @include('sections.about.why')
    @include('sections.about.approach')
    @include('sections.about.journey')
    @include('sections.about.team')
    @include('sections.about.testimonials')
    @include('sections.about.vision')
    @include('sections.home.process')
    @include('sections.about.faq')
    @include('sections.home.cta')
@endsection
