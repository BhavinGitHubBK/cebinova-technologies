@extends('layouts.app')

@section('title', 'Services | CEBINOVA Technologies')
@section('description', 'Websites, eCommerce, custom software, AI automation, digital transformation and marketing - complete technology services from CEBINOVA.')

@section('content')
    @include('sections.services.hero')
    @include('sections.services.catalog')
    @include('sections.services.path')
    @include('sections.home.process')
    @include('sections.services.faq')
    @include('sections.home.cta')
@endsection
