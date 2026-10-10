@extends('layouts.admin')
@section('title', $title)
@section('heading', $title)
@section('content')
<p class="report-intro">{{ $purpose }}</p>
<form method="GET" action="{{ route('admin.reports.review', $key) }}" class="admin-card report-range">
    @include('admin.reports.range')
    <button class="admin-btn admin-btn-primary" type="submit">Update report</button>
    <a class="admin-btn admin-btn-ghost" href="{{ route('admin.reports.index', $range->parameters()) }}">All reports</a>
</form>
<section class="admin-card report-reading-card">
    <p>{{ $range->from->format('d M Y') }} – {{ $range->to->format('d M Y') }}</p>
    <h2>{{ $reading }}</h2>
    @if (! empty($note))
        <p>{{ $note }}</p>
    @endif
</section>
<div class="admin-stat-grid report-stats">
    @foreach ($stats as $stat)
        <article class="admin-card">
            <p class="admin-stat-label">{{ $stat['label'] }}</p>
            <p class="admin-stat-value">{{ $stat['value'] }}</p>
        </article>
    @endforeach
</div>
@foreach ($tables as $tableIndex => $table)
    @php
        $rows = $table['rows'];
        $tableSort = (string) request('table') === (string) $tableIndex ? (string) request('sort') : '';
        $tableDir = request('dir') === 'desc' ? 'desc' : 'asc';
        if ($tableSort !== '' && array_key_exists((int) $tableSort, $rows[0] ?? [])) {
            $rows = admin_sort_rows($rows, (int) $tableSort, $tableDir);
        }
    @endphp
    <div class="admin-card admin-table-wrap report-table">
        <h2>{{ $table['title'] }}</h2>
        <table class="admin-table lead-table">
            <thead>
                <tr>
                    @foreach ($table['columns'] as $index => $column)
                        <th><x-admin.sort :column="$index" :label="$column" :sort="$tableSort" :dir="$tableDir" :table="$tableIndex" /></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>
                                @if (is_array($cell))
                                    <a href="{{ $cell['url'] }}">{{ $cell['text'] }}</a>
                                @else
                                    {{ $cell }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($table['columns']) }}">{{ $table['empty'] }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endforeach
<p><a href="{{ route('admin.reports.show', ['type' => 'sales', ...$range->parameters()]) }}">Open the lead register for this period</a></p>
@endsection
