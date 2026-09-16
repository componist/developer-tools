@php
    use Componist\Core\Support\Ui;
@endphp

<input {{ $attributes->merge(['class' => Ui::FIELD]) }} />
