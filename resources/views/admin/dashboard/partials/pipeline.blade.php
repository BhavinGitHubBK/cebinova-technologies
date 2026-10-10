@foreach ($pipeline as $stage)
    <button type="button" class="dash-pipe is-{{ \Illuminate\Support\Str::slug($stage['status']) }}" data-dash-stage="{{ $stage['status'] }}">
        <span class="dash-pipe-name">{{ $stage['status'] }}</span>
        <strong>{{ $stage['count'] }}</strong>
        <span class="dash-pipe-track" aria-hidden="true"><span style="width: {{ $stage['percent'] }}%"></span></span>
        <span class="dash-pipe-meta">{{ $stage['percent'] }}% of leads in this period</span>
    </button>
@endforeach
