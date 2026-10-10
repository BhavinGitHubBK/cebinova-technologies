@extends('layouts.admin')
@section('title', 'Cache Management')
@section('breadcrumb', 'Admin / Cache')
@section('heading', 'Cache Management')
@section('content')
<div class="cache-page {{ $environment === 'production' ? 'is-production' : '' }}">
    <div class="cache-head">
        <p class="page-note">Manage Laravel application caches and optimization files.</p>
        <div class="cache-head-actions">
            <span class="admin-badge cache-env is-{{ $environment }}" title="APP_ENV">{{ $environment_label }}</span>
            <form method="POST" action="{{ route('admin.cache.all.clear') }}" data-cache-form data-cache-pending="Clearing…" data-cache-title="Clear all Laravel caches?" data-cache-text="This will clear application, configuration, route, view, event, and compiled caches where supported. It will not delete users, leads, customers, promotions, coupons, packages, pricing, website content, or uploaded media." data-cache-confirm="Clear all cache" @if ($environment === 'production') data-cache-tone="production" @endif>
                @csrf
                <button class="admin-btn admin-btn-primary" type="submit" data-cache-label="Clear all cache">Clear all cache</button>
            </form>
        </div>
    </div>

    <section class="cache-summary" aria-label="Cache health">
        @foreach ($summary as $card)
            <article class="admin-card cache-card">
                <p class="cache-card-label">
                    {{ $card['label'] }}
                    @if ($card['help'])
                        <button type="button" class="cache-help" title="{{ $card['help'] }}" aria-label="{{ $card['help'] }}">?</button>
                    @endif
                </p>
                <p class="cache-card-value" data-cache-summary="{{ $card['label'] }}">{{ $card['value'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="admin-card solution-list">
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead>
                    <tr>
                        <th>Cache</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th title="Recorded only when the action is run from this page.">Last cleared</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr data-cache-row="{{ $row['type'] }}">
                            <td>
                                <strong>{{ $row['name'] }}</strong>
                                <button type="button" class="cache-help" title="{{ $row['help'] }}" aria-label="{{ $row['help'] }}">?</button>
                            </td>
                            <td>{{ $row['description'] }}</td>
                            <td>
                                <span class="admin-badge cache-status is-{{ str_replace('_', '-', $row['status']) }}" title="{{ $row['status_help'] }}">{{ $row['status_label'] }}</span>
                            </td>
                            <td data-cache-last>
                                @if ($row['last'])
                                    <span class="cache-when">{{ $row['last']->cleared_at->diffForHumans() }}</span>
                                    <span class="cache-by">By {{ $row['last']->user->name ?? 'Admin' }}</span>
                                @else
                                    Never
                                @endif
                            </td>
                            <td>
                                <div class="cache-actions">
                                    @forelse ($row['actions'] as $action)
                                        <form method="POST" action="{{ route($action['route']) }}" data-cache-form data-cache-pending="{{ $action['pending'] }}" data-cache-title="{{ $action['title'] }}" data-cache-text="{{ $action['text'] }}" data-cache-confirm="{{ $action['confirm'] }}">
                                            @csrf
                                            <button class="admin-btn admin-btn-ghost" type="submit" data-cache-label="{{ $action['kind'] === 'rebuild' ? 'Rebuild' : 'Clear' }}">{{ $action['kind'] === 'rebuild' ? 'Rebuild' : 'Clear' }}</button>
                                        </form>
                                    @empty
                                        <span class="cache-muted">Unavailable</span>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card cache-history">
        <div class="cache-section-head">
            <h2>Recent cache activity</h2>
            @can('admin.activity.view')
                <a href="{{ route('admin.activity-logs.index') }}">View all activity</a>
            @endcan
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table lead-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Cache</th>
                        <th>Admin</th>
                        <th>Time</th>
                        <th>Result</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody data-cache-history>
                    @forelse ($history as $entry)
                        <tr>
                            <td>{{ $entry->action === 'rebuild' ? 'Rebuilt' : 'Cleared' }}</td>
                            <td>{{ $entry->cacheLabel() }}</td>
                            <td>{{ $entry->user->name ?? 'Admin' }}</td>
                            <td>{{ $entry->cleared_at->diffForHumans() }}</td>
                            <td>
                                <span class="admin-badge {{ $entry->success ? 'is-won' : 'is-lost' }}">{{ $entry->success ? 'Success' : 'Failed' }}</span>
                            </td>
                            <td>{{ number_format($entry->duration_ms) }} ms</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No cache actions have been run from this page.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <details class="admin-card cache-info">
        <summary>Cache information</summary>
        <dl>
            @foreach ($system as $label => $value)
                <div>
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </details>
</div>
<style>
    .cache-page { display: grid; gap: 1rem; }
    .cache-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .cache-head .page-note { margin: 0; max-width: 40rem; }
    .cache-head-actions { display: flex; align-items: center; gap: .75rem; }
    .cache-env.is-production { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; }
    .cache-page.is-production .cache-head-actions .admin-btn-primary { box-shadow: inset 0 0 0 1px #c2410c; }
    .cache-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; }
    .cache-card { padding: .85rem .95rem; }
    .cache-card-label, .cache-by { display: flex; align-items: center; gap: .35rem; color: #64748b; font-size: .78rem; }
    .cache-card-value { margin: .35rem 0 0; font-size: 1.05rem; font-weight: 700; color: #0f172a; }
    .cache-when, .cache-by { display: block; }
    .cache-actions { display: flex; flex-wrap: wrap; gap: .4rem; }
    .cache-actions .admin-btn { min-height: 2rem; padding: .35rem .7rem; }
    .cache-help { width: 1.05rem; height: 1.05rem; border: 1px solid #cbd5e1; border-radius: 999px; background: #fff; color: #475569; font-size: .68rem; line-height: 1; cursor: help; }
    .cache-help:focus-visible, .cache-info summary:focus-visible, .cache-page .admin-btn:focus-visible { outline: 2px solid #013a9d; outline-offset: 2px; }
    .cache-status.is-cached { background: #dcfce7; color: #15803d; }
    .cache-status.is-not-cached { background: #e2e8f0; color: #334155; }
    .cache-status.is-available { background: #dbeafe; color: #1d4ed8; }
    .cache-status.is-unknown { background: #fef3c7; color: #92400e; }
    .cache-section-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .75rem; }
    .cache-section-head h2, .cache-info summary { margin: 0; font-size: .95rem; }
    .cache-section-head a { color: #013a9d; font-size: .85rem; font-weight: 600; }
    .cache-info summary { cursor: pointer; }
    .cache-info dl { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem 1rem; margin: .9rem 0 0; }
    .cache-info dt { color: #64748b; font-size: .75rem; }
    .cache-info dd { margin: .15rem 0 0; font-weight: 650; }
    .cache-muted { color: #64748b; font-size: .82rem; }
    .cache-page button:disabled { opacity: .65; cursor: wait; }
    @media (max-width: 1100px) {
        .cache-summary, .cache-info dl { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .cache-summary, .cache-info dl { grid-template-columns: 1fr; }
        .cache-head-actions { width: 100%; justify-content: space-between; }
    }
</style>
@endsection
@push('scripts')
<script>
const cacheText = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[char]));

const cacheToast = (message, icon) => {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: message,
        showConfirmButton: false,
        timer: 2800,
        timerProgressBar: true,
    });
};

const applyCacheState = (data) => {
    (data.summary || []).forEach((card) => {
        const node = document.querySelector(`[data-cache-summary="${CSS.escape(card.label)}"]`);
        if (node) node.textContent = card.value;
    });
    (data.rows || []).forEach((row) => {
        const line = document.querySelector(`[data-cache-row="${CSS.escape(row.type)}"]`);
        if (!line) return;
        const badge = line.querySelector('.cache-status');
        if (badge) {
            badge.className = `admin-badge cache-status is-${String(row.status).replaceAll('_', '-')}`;
            badge.textContent = row.status_label;
            badge.title = row.status_help || '';
        }
        const last = line.querySelector('[data-cache-last]');
        if (last) {
            last.innerHTML = row.when
                ? `<span class="cache-when">${cacheText(row.when)}</span><span class="cache-by">By ${cacheText(row.by || 'Admin')}</span>`
                : 'Never';
        }
    });
    const history = document.querySelector('[data-cache-history]');
    if (!history || !Array.isArray(data.history)) return;
    history.innerHTML = data.history.length ? data.history.map((entry) => `
        <tr>
            <td>${cacheText(entry.action)}</td>
            <td>${cacheText(entry.cache)}</td>
            <td>${cacheText(entry.admin)}</td>
            <td>${cacheText(entry.time)}</td>
            <td><span class="admin-badge ${entry.success ? 'is-won' : 'is-lost'}">${entry.success ? 'Success' : 'Failed'}</span></td>
            <td>${cacheText(entry.duration)}</td>
        </tr>
    `).join('') : '<tr><td colspan="6">No cache actions have been run from this page.</td></tr>';
};

const runCacheAction = async (form, submitter) => {
    const buttons = [...document.querySelectorAll('.cache-page button[type="submit"]')];
    const page = document.querySelector('[data-admin-page]');
    buttons.forEach((button) => { button.disabled = true; });
    if (submitter) submitter.textContent = form.dataset.cachePending || 'Clearing…';
    page?.classList.add('is-loading');
    page?.setAttribute('aria-busy', 'true');
    window.startAdminProgress?.();
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.ok === false) {
            cacheToast(data.message || 'Cache operation failed.', 'error');
            if (data.rows) applyCacheState(data);
            return;
        }
        applyCacheState(data);
        cacheToast(data.message || 'Cache cleared successfully.', 'success');
    } catch (error) {
        cacheToast('Cache operation failed.', 'error');
    } finally {
        page?.classList.remove('is-loading');
        page?.removeAttribute('aria-busy');
        window.finishAdminProgress?.();
        form.dataset.pendingConfirm = '';
        buttons.forEach((button) => {
            button.disabled = false;
            if (button.dataset.cacheLabel) button.textContent = button.dataset.cacheLabel;
        });
    }
};

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-cache-form')) return;
    event.preventDefault();
    event.stopPropagation();
    if (form.dataset.pendingConfirm === '1') return;
    form.dataset.pendingConfirm = '1';
    const ask = typeof Swal === 'undefined'
        ? Promise.resolve(window.confirm(`${form.dataset.cacheTitle}\n\n${form.dataset.cacheText}`))
        : Swal.fire({
            title: form.dataset.cacheTitle,
            text: form.dataset.cacheText,
            icon: form.dataset.cacheTone === 'production' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: form.dataset.cacheConfirm || 'Clear cache',
            cancelButtonText: 'Cancel',
            focusCancel: form.dataset.cacheTone === 'production',
            buttonsStyling: false,
            customClass: { popup: 'admin-swal', confirmButton: 'admin-swal-btn', cancelButton: 'admin-swal-btn is-ghost' },
        }).then((result) => result.isConfirmed);
    ask.then((confirmed) => {
        if (!confirmed) {
            form.dataset.pendingConfirm = '';
            return;
        }
        runCacheAction(form, event.submitter);
    });
}, true);
</script>
@endpush
