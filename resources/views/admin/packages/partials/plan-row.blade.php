@php
    $label = $plan['label'] ?? '';
    $price = $plan['price'] ?? '';
    $features = $plan['features'] ?? null;
    $featuresText = is_array($features)
        ? collect($features)->map(fn ($item) => is_array($item) ? ($item['name'] ?? $item['title'] ?? '') : $item)->filter()->implode("\n")
        : ($plan['features_text'] ?? '');
    $priceLabel = is_numeric($price) && $price !== '' ? '₹'.number_format((int) $price) : '';
@endphp
<div class="plan-row" data-plan-row>
    <div class="plan-row-head">
        <span class="plan-index" data-plan-index>{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
        <strong data-plan-title>{{ filled($label) ? $label : 'New plan' }}</strong>
        <span class="plan-price" data-plan-price>{{ $priceLabel }}</span>
        <button type="button" class="plan-remove" data-plan-remove>Remove</button>
    </div>
    <div class="plan-grid">
        <div class="admin-field">
            <label class="admin-label">Label</label>
            <input class="admin-input" name="plans[{{ $index }}][label]" value="{{ $label }}" data-plan-label>
        </div>
        <div class="admin-field">
            <label class="admin-label">Price (INR)</label>
            <input class="admin-input" type="number" name="plans[{{ $index }}][price]" value="{{ $price }}" data-plan-amount>
        </div>
        <div class="admin-field">
            <label class="admin-label">Original price</label>
            <input class="admin-input" type="number" name="plans[{{ $index }}][original_price]" value="{{ $plan['original_price'] ?? '' }}">
        </div>
        <div class="admin-field">
            <label class="admin-label">Badge</label>
            <input class="admin-input" name="plans[{{ $index }}][badge]" value="{{ $plan['badge'] ?? '' }}">
        </div>
        <div class="admin-field">
            <label class="admin-label">Duration</label>
            <input class="admin-input" name="plans[{{ $index }}][duration]" value="{{ $plan['duration'] ?? '' }}">
        </div>
        <div class="admin-field">
            <label class="admin-label">Period</label>
            <input class="admin-input" name="plans[{{ $index }}][period]" value="{{ $plan['period'] ?? '' }}">
        </div>
        <div class="admin-field is-span-2">
            <label class="admin-label">Key</label>
            <input class="admin-input" name="plans[{{ $index }}][key]" value="{{ $plan['key'] ?? '' }}">
        </div>
        <div class="admin-field is-wide">
            <label class="admin-label">Features</label>
            <textarea class="admin-textarea" name="plans[{{ $index }}][features_text]" rows="{{ admin_rows($featuresText, 4) }}">{{ $featuresText }}</textarea>
            <p class="form-hint">One item per line.</p>
        </div>
    </div>
</div>
