@props(['field'])
<span id="career-{{ $field }}-error" class="recruitment-field-error" data-career-error="{{ $field }}" aria-live="polite" @unless($errors->has($field)) hidden @endunless>{{ $errors->first($field) }}</span>
