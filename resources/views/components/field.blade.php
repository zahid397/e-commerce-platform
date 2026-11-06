@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false, 'multiline' => false, 'hint' => null])
@php
    $description = trim(($hint ? $name . '-hint ' : '') . ($errors->has($name) ? $name . '-error' : ''));
    $current = \App\Support\OldInput::string($name, $value);
@endphp
<div class="field">
    <label class="field-label" for="{{ $name }}">{{ $label }}@if($required)<span class="required-mark" aria-hidden="true"> *</span>@endif</label>
    @if($multiline)
        <textarea id="{{ $name }}" name="{{ $name }}" @required($required) @if($description) aria-describedby="{{ $description }}" @endif @error($name) aria-invalid="true" @enderror {{ $attributes->class('input textarea') }}>{{ $current }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($type !== 'file' && $type !== 'password') value="{{ $current }}" @endif @required($required) @if($description) aria-describedby="{{ $description }}" @endif @error($name) aria-invalid="true" @enderror {{ $attributes->class('input') }}>
    @endif
    @if($hint)<small class="field-hint" id="{{ $name }}-hint">{{ $hint }}</small>@endif
    @error($name)<p class="field-error" id="{{ $name }}-error">{{ $message }}</p>@enderror
</div>
