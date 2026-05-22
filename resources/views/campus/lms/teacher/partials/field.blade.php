{{--
  Partial: field
  Variables: $name, $label, $type ('text'|'number'|'textarea'), $value, $required=false, $full=false, $rows=3, $placeholder='', $min=null
--}}
@php
    $required    = $required ?? false;
    $full        = $full ?? false;
    $rows        = $rows ?? 3;
    $placeholder = $placeholder ?? '';
    $min         = $min ?? null;
    $style       = $full ? 'width:100%;box-sizing:border-box;' : 'width:100%;box-sizing:border-box;';
@endphp

<div style="{{ $full ? 'grid-column:1/-1;' : '' }}margin-bottom:{{ $full ? '1rem' : '0' }};">
    <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
        {{ $label }}@if($required)<span style="color:#ef4444;margin-left:0.2rem;">*</span>@endif
    </label>

    @if($type === 'textarea')
        <textarea name="{{ $name }}"
                  rows="{{ $rows }}"
                  placeholder="{{ $placeholder }}"
                  style="{{ $style }}border:1px solid #d1d5db;border-radius:0.4rem;padding:0.45rem 0.6rem;font-size:0.875rem;color:#1e293b;resize:vertical;font-family:inherit;"
                  {{ $required ? 'required' : '' }}>{{ $value }}</textarea>
    @else
        <input type="{{ $type }}"
               name="{{ $name }}"
               value="{{ $value }}"
               placeholder="{{ $placeholder }}"
               {{ $min !== null ? 'min='.$min : '' }}
               style="{{ $style }}border:1px solid #d1d5db;border-radius:0.4rem;padding:0.45rem 0.6rem;font-size:0.875rem;color:#1e293b;"
               {{ $required ? 'required' : '' }}>
    @endif

    @error($name)
        <p style="font-size:0.75rem;color:#dc2626;margin-top:0.25rem;">{{ $message }}</p>
    @enderror
</div>
