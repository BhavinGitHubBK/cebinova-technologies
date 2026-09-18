@extends('layouts.app')

@section('title', 'Industries | CEBINOVA Technologies')
@section('description', 'Technology solutions for kirana stores, dairy, restaurants, retail, manufacturing, healthcare, education, real estate and more.')

@section('content')
    @include('sections.industries.hero')
    @include('sections.industries.catalog')
    @include('sections.home.process')
    @include('sections.industries.faq')
    @include('sections.home.cta')
@endsection
