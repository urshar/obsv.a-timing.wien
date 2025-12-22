<div>@props([
    'class' => '',
])

    <div class="px-4 py-4 border-t border-slate-200 {{ $class }}">
        {{ $slot }}
    </div>

    <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
</div>
