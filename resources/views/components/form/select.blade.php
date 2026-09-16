@php
    use Componist\Core\Support\Ui;
@endphp

<select {{ $attributes->merge(['class' => Ui::FIELD]) }}>
    {{ $slot }}
</select>
