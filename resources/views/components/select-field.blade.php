@props(['name', 'label', 'required' => false, 'hint' => null])
@php($description = trim(($hint ? $name . '-hint ' : '') . ($errors->has($name) ? $name . '-error' : '')))
<div class="field">
    <label class="field-label" for="{{ $name }}">{{ $label }}@if($required)<span class="required-mark" aria-hidden="true"> *</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @required($required) @if($description) aria-describedby="{{ $description }}" @endif @error($name) aria-invalid="true" @enderror {{ $attributes->class('input select') }}>
        {{ $slot }}
    </select>
    @if($hint)<small class="field-hint" id="{{ $name }}-hint">{{ $hint }}</small>@endif
    @error($name)<p class="field-error" id="{{ $name }}-error">{{ $message }}</p>@enderror
</div>
