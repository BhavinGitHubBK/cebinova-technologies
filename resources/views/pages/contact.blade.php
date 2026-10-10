@extends('layouts.app')

@section('title', 'Contact | CEBINOVA Technologies')
@section('description', 'Get a free consultation from CEBINOVA Technologies. Tell us about your business and we will recommend the right next step.')

@section('content')
    @include('sections.contact.hero')
    @include('sections.contact.panel')
    @include('sections.home.process')
@endsection
