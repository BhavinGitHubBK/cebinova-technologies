@extends('layouts.admin')
@section('title', 'Preview '.$campaign->name)
@section('heading', 'Preview')
@section('breadcrumb', 'Admin / Promotions / Preview')
@section('content')
@include('admin.promotions.partials.nav')
<link rel="stylesheet" href="{{ asset('assets/promotions.css') }}?v=18">
@include('admin.promotions.partials.preview', ['campaign' => $campaign])
@endsection
