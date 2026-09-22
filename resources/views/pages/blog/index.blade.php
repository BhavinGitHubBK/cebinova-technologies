@extends('layouts.app')

@section('title', 'Blog | CEBINOVA Technologies')
@section('description', 'News and updates from CEBINOVA Technologies.')

@section('content')
    <x-page-hero
        :wrap="true"
        eyebrow="Blog"
        title="News and notes from CEBINOVA"
        text="Practical updates on websites, software, automation and digital growth."
    />

    <section class="section-pad section-soft">
        <div class="container-wide grid gap-5 md:grid-cols-2 xl:grid-cols-3" data-stagger>
            @forelse ($posts as $post)
                <article class="demo-card lift-card card-surface flex h-full flex-col overflow-hidden">
                    <div class="flex flex-1 flex-col p-6 sm:p-7">
                        @if ($post->category)
                            <p class="text-[12px] font-semibold uppercase tracking-[0.14em] text-navy/40">{{ $post->category->name }}</p>
                        @endif
                        <h2 class="mt-1 text-lg text-navy sm:text-xl">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-navy/80">{{ $post->title }}</a>
                        </h2>
                        @if ($post->excerpt)
                            <p class="mt-2 flex-1 text-[15.5px] leading-relaxed text-muted">{{ $post->excerpt }}</p>
                        @endif
                        <p class="mt-4 text-[13px] text-navy/45">{{ optional($post->published_at)->format('M j, Y') }}</p>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-muted">No published posts yet.</div>
            @endforelse
        </div>
        <div class="container-wide mt-10">
            {{ $posts->links() }}
        </div>
    </section>
@endsection
