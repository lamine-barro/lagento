@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'icon' => null
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
    $inputClasses = 'input-field';
    
    if ($hasError) {
        $inputClasses .= ' error';
    }
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $inputId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon)
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
            </div>
        @endif
        
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            placeholder="{{ $placeholder }}"
            value="{{ old($name, $value) }}"
            {{ $attributes->merge(['class' => $inputClasses . ($icon ? ' pl-10' : '')]) }}
            @required($required)
            @disabled($disabled)
        />
    </div>
    
    @if($hasError)
        <div class="text-danger text-sm mt-1">
            {{ $error ?? $errors->first($name) }}
        </div>
    @endif
    
    @if($hint)
        <div class="text-gray-500 text-sm mt-1">
            {{ $hint }}
        </div>
    @endif
</div>