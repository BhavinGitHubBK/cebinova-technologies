<ol class="visitor-timeline">
    @forelse ($views as $view)
        @if ($view instanceof \App\Models\AnalyticsAccountEvent)
            <li class="is-account is-{{ $view->action }}">
                <time datetime="{{ $view->occurred_at?->toIso8601String() }}">{{ ist($view->occurred_at, 'h:i A') }}</time>
                <span class="visitor-timeline-mark" aria-hidden="true"></span>
                <div>
                    <strong>{{ $view->label() }}</strong>
                    @if (filled($view->path))
                        <span>On {{ page_label($view->path) }}</span>
                    @endif
                    <em>{{ $view->ip_address ? 'IP '.$view->ip_address : 'Account activity' }}</em>
                </div>
            </li>
        @else
            <li>
                <time datetime="{{ $view->viewed_at?->toIso8601String() }}">{{ ist($view->viewed_at, 'h:i A') }}</time>
                <span class="visitor-timeline-mark" aria-hidden="true"></span>
                <div>
                    <strong>{{ page_label($view->path) }}</strong>
                    @if ($view->path !== '/' && $view->path !== '')
                        <span>{{ $view->path }}</span>
                    @endif
                    <em>{{ filled($view->referrer_path) ? 'Came from '.page_label($view->referrer_path) : 'Opened directly' }}</em>
                </div>
            </li>
        @endif
    @empty
        <li class="is-empty">No pages recorded.</li>
    @endforelse
</ol>
