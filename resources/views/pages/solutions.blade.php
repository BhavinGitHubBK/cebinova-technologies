@extends('layouts.app')

@section('title', 'Solutions | CEBINOVA Technologies')
@section('description', 'CEBINOVA business solutions for kirana stores, retail, professional services, eCommerce, operations and automation.')

@section('content')
    @include('sections.solutions.hero')
    @include('sections.solutions.catalog')
    @include('sections.solutions.paths')
    @include('sections.solutions.industries')
    @include('sections.home.process')
    @include('sections.solutions.faq')
    @include('sections.home.cta')
@endsection
