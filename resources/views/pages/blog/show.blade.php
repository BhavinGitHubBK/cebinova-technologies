@extends('layouts.app')

@section('title', ($post->seo_title ?: $post->title).' | CEBINOVA')
@section('description', $post->seo_description ?: ($post->excerpt ?: 'CEBINOVA Technologies blog'))

@section('content')
    <x-page-hero
        :wrap="true"
        :eyebrow="$post->category?->name ?: 'Blog'"
        :title="$post->title"
        :text="$post->excerpt"
    />

    <section class="section-pad">
        <div class="container-narrow prose-like mx-auto max-w-3xl">
            <p class="text-[13px] text-navy/45">{{ optional($post->published_at)->format('F j, Y') }}@if($post->author) · {{ $post->author->name }}@endif</p>
            <div class="mt-8 whitespace-pre-wrap text-[16px] leading-relaxed text-muted">{{ $post->content }}</div>
            <div class="mt-10">
                <x-button href="{{ route('blog.index') }}" variant="outline">Back to blog</x-button>
            </div>
        </div>
    </section>
@endsection
