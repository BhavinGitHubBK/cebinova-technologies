@extends('layouts.admin')

@section('title', $lead->name)
@section('breadcrumb', 'Admin / Leads / Detail')
@section('heading', $lead->name)

@section('content')
@php
    $phone = preg_replace('/\D+/', '', (string) $lead->phone);
    $wa = preg_replace('/\D+/', '', (string) ($lead->whatsapp ?: $lead->phone));
    $statusClass = match ($lead->status) {
        'New' => 'is-new',
        'Contacted' => 'is-contacted',
        'Follow-up', 'Qualified', 'Proposal Sent' => 'is-follow',
        'Converted', 'Won' => 'is-won',
        'Lost' => 'is-lost',
        default => '',
    };
    $package = collect([$lead->package_category, $lead->plan_duration, $lead->selected_price])
        ->filter(fn ($value) => filled($value))
        ->implode(' · ');
    $followUp = $lead->follow_up_at;
    $followOverdue = $followUp && $followUp->isPast() && ! in_array($lead->status, ['Won', 'Converted', 'Lost'], true);
@endphp

<section class="lead-board lead-detail">
    <div class="admin-card lead-detail-head">
        <div class="lead-detail-meta">
            <span class="admin-badge {{ $statusClass }}">{{ $lead->status }}</span>
            <span>Created {{ $lead->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</span>
            @if ($lead->promotion_snapshot)
                <span>Offer {{ $lead->promotion_snapshot['promotion_name'] ?? 'saved' }} @if (! empty($lead->promotion_snapshot['coupon_code'])) · {{ $lead->promotion_snapshot['coupon_code'] }} @endif · saved {{ $lead->promotion_snapshot['discount_total'] ?? '' }}</span>
            @endif
            @if ($lead->assignee)
                <span>Assigned to {{ $lead->assignee->name }}</span>
            @endif
            @if ($followUp)
                <span class="{{ $followOverdue ? 'is-overdue' : '' }}">Follow-up {{ $followUp->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</span>
            @endif
        </div>
        <div class="lead-detail-actions">
            @if ($phone)
                <a class="lead-contact-btn" href="tel:{{ $phone }}"><x-mark name="phone" class="h-4 w-4" /> Call</a>
            @endif
            @if ($wa)
                <a class="lead-contact-btn" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"><x-mark name="whatsapp" class="h-4 w-4" /> WhatsApp</a>
            @endif
            @if ($lead->email)
                <a class="lead-contact-btn" href="mailto:{{ $lead->email }}"><x-mark name="mail" class="h-4 w-4" /> Email</a>
            @endif
            <a class="lead-contact-btn" href="{{ route('admin.leads.index') }}"><x-mark name="list" class="h-4 w-4" /> All leads</a>
        </div>
    </div>

    <div class="lead-detail-grid">
        <article class="admin-card">
            <h2>Enquiry</h2>
            @if ($lead->trashed())
                <p class="admin-alert admin-alert-error">This lead is in trash.</p>
                @can('admin.leads.manage')
                    <form method="POST" action="{{ route('admin.leads.restore', $lead->id) }}" class="lead-restore">
                        @csrf
                        <button class="admin-btn admin-btn-accent" type="submit">Restore</button>
                    </form>
                @endcan
            @endif
            <dl class="lead-facts">
                <div>
                    <dt>Business</dt>
                    <dd @class(['is-empty' => blank($lead->business_name)])>{{ $lead->business_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Phone</dt>
                    <dd @class(['is-empty' => ! $phone])>@if ($phone)<a href="tel:{{ $phone }}">{{ $lead->phone }}</a>@else — @endif</dd>
                </div>
                <div>
                    <dt>WhatsApp</dt>
                    <dd @class(['is-empty' => ! $wa])>@if ($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">{{ $lead->whatsapp ?: $lead->phone }}</a>@else — @endif</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd @class(['is-empty' => blank($lead->email)])>@if ($lead->email)<a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@else — @endif</dd>
                </div>
                <div>
                    <dt>Service</dt>
                    <dd @class(['is-empty' => blank($lead->service)])>{{ $lead->service ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Package</dt>
                    <dd @class(['is-empty' => blank($package)])>{{ $package ?: '—' }}</dd>
                </div>
                <div>
                    <dt>City</dt>
                    <dd @class(['is-empty' => blank($lead->city)])>{{ $lead->city ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Budget</dt>
                    <dd @class(['is-empty' => blank($lead->budget)])>{{ $lead->budget ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Source</dt>
                    <dd @class(['is-empty' => blank($lead->source)])>{{ $lead->source ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Page</dt>
                    <dd @class(['is-empty' => blank($lead->source_page)])>{{ $lead->source_page ?: '—' }}</dd>
                </div>
            </dl>
            <div class="lead-message">
                <h3>Message</h3>
                <p @class(['is-empty' => blank($lead->message)])>{{ filled($lead->message) ? $lead->message : 'No message was left with this enquiry.' }}</p>
            </div>
        </article>

        <aside class="admin-card lead-manage">
            <h2>Manage</h2>
            @can('admin.leads.manage')
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}" data-lead-save data-current-status="{{ $lead->status }}">
                    @csrf
                    @method('PUT')
                    <div class="admin-field">
                        <label class="admin-label" for="lead-status">Status</label>
                        <select id="lead-status" class="admin-select" name="status">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($lead->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="lead-follow-up">Follow-up date</label>
                        <input id="lead-follow-up" class="admin-input" type="datetime-local" name="follow_up_at" value="{{ optional($lead->follow_up_at)->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="lead-assignee">Assign to</label>
                        <select id="lead-assignee" class="admin-select" name="assigned_to">
                            <option value="">Unassigned</option>
                            @foreach ($admins as $admin)
                                <option value="{{ $admin->id }}" @selected($lead->assigned_to === $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label class="admin-label" for="lead-notes">Internal notes</label>
                        <textarea id="lead-notes" class="admin-textarea" name="notes" rows="{{ admin_rows(old('notes', $lead->notes), 6) }}">{{ old('notes', $lead->notes) }}</textarea>
                    </div>
                    <div class="lead-manage-actions">
                        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
                        @unless ($lead->trashed())
                            <button class="admin-btn admin-btn-danger" type="submit" form="lead-delete" data-confirm="Move this lead to trash?">Delete</button>
                        @endunless
                    </div>
                </form>
                @unless ($lead->trashed())
                    <form id="lead-delete" method="POST" action="{{ route('admin.leads.destroy', $lead) }}" hidden>
                        @csrf
                        @method('DELETE')
                    </form>
                @endunless
            @else
                <dl class="lead-facts">
                    <div>
                        <dt>Status</dt>
                        <dd>{{ $lead->status }}</dd>
                    </div>
                    <div>
                        <dt>Follow-up</dt>
                        <dd @class(['is-empty' => ! $followUp])>{{ $followUp ? $followUp->timezone(config('app.timezone'))->format('d M Y, h:i A') : '—' }}</dd>
                    </div>
                    <div class="is-wide">
                        <dt>Internal notes</dt>
                        <dd @class(['is-empty' => blank($lead->notes)])>{{ $lead->notes ?: '—' }}</dd>
                    </div>
                </dl>
            @endcan
        </aside>
    </div>

</section>
@endsection
