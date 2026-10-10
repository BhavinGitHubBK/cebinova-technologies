@extends('layouts.app')

@php
    $homeData = $homeData ?? App\Services\HomepageBuilder::homeData();
@endphp

@section('title', 'CEBINOVA Technologies | Technology for Every Business')
@section('description', 'CEBINOVA Technologies provides websites, eCommerce, custom software, AI automation and complete digital solutions for businesses.')

@section('content')
    @include('sections.home.hero')
    @include('sections.home.services')
    @include('sections.home.problems')
    @include('sections.home.packages')
    @include('sections.home.portfolio')
    @include('sections.home.why')
    @include('sections.home.process')
    @include('sections.home.cta')
@endsection
