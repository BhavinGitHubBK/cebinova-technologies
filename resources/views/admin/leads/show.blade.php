@extends('layouts.admin')

@section('title', $lead->name)
@section('breadcrumb', 'Admin / Leads / Detail')
@section('heading', $lead->name)

@section('content')
@php
    $wa = preg_replace('/\D+/', '', $lead->whatsapp ?: $lead->phone);
@endphp
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
    <div class="admin-card">
        <h2 style="margin-top:0;font-size:1rem;">Lead details</h2>
        <p><strong>Business:</strong> {{ $lead->business_name ?: '—' }}</p>
        <p><strong>Phone:</strong> <a href="tel:{{ preg_replace('/\D+/', '', $lead->phone) }}">{{ $lead->phone }}</a></p>
        <p><strong>WhatsApp:</strong> @if($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">Chat</a>@else — @endif</p>
        <p><strong>Email:</strong> @if($lead->email)<a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@else — @endif</p>
        <p><strong>Service:</strong> {{ $lead->service }}</p>
        <p><strong>Package:</strong> {{ $lead->package_category }} {{ $lead->plan_duration }} {{ $lead->selected_price }}</p>
        <p><strong>City:</strong> {{ $lead->city ?: '—' }}</p>
        <p><strong>Budget:</strong> {{ $lead->budget ?: '—' }}</p>
        <p><strong>Source:</strong> {{ $lead->source }}</p>
        <p><strong>Message:</strong><br>{{ $lead->message ?: '—' }}</p>
        <p><strong>Created:</strong> {{ $lead->created_at }}</p>
        @if($lead->trashed())
            <p class="admin-alert admin-alert-error">This lead is in trash.</p>
            @can('admin.leads.manage')
                <form method="POST" action="{{ route('admin.leads.restore', $lead->id) }}">@csrf<button class="admin-btn admin-btn-accent" type="submit">Restore</button></form>
            @endcan
        @endif
    </div>

    <div class="admin-card">
        <h2 style="margin-top:0;font-size:1rem;">Manage</h2>
        @can('admin.leads.manage')
            <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
                @csrf @method('PUT')
                <div class="admin-field"><label class="admin-label">Status</label>
                    <select class="admin-select" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected($lead->status === $status)>{{ $status }}</option>@endforeach</select>
                </div>
                <div class="admin-field"><label class="admin-label">Follow-up date</label>
                    <input class="admin-input" type="datetime-local" name="follow_up_at" value="{{ optional($lead->follow_up_at)->format('Y-m-d\TH:i') }}">
                </div>
                <div class="admin-field"><label class="admin-label">Assign to</label>
                    <select class="admin-select" name="assigned_to"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected($lead->assigned_to === $admin->id)>{{ $admin->name }}</option>@endforeach</select>
                </div>
                <div class="admin-field"><label class="admin-label">Internal notes</label>
                    <textarea class="admin-textarea" name="notes" rows="6">{{ old('notes', $lead->notes) }}</textarea>
                </div>
                <button class="admin-btn admin-btn-primary" type="submit">Save</button>
            </form>
            @unless($lead->trashed())
                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" style="margin-top:1rem;">
                    @csrf @method('DELETE')
                    <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Move this lead to trash?">Delete</button>
                </form>
            @endunless
        @else
            <p>Status: {{ $lead->status }}</p>
            <p>Notes: {{ $lead->notes ?: '—' }}</p>
        @endcan
    </div>
</div>
@endsection
