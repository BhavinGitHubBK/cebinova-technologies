@extends('layouts.app')

@section('title', 'CEBINOVA Business Demos | CEBINOVA Technologies')
@section('description', 'Explore practical CEBINOVA solution demos designed for different industries and business requirements.')

@section('content')
    @include('sections.demos.hero')
    @include('sections.demos.catalog')
    @include('sections.home.process')
    @include('sections.demos.faq')
    @include('sections.home.cta')
@endsection
