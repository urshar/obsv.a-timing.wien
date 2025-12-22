@props([
    'continent' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'cancelUrl',
])

<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-ui.field label="Code" name="code">
        <x-ui.input name="code" :value="$continent->code ?? ''" maxlength="10"/>
    </x-ui.field>

    <x-ui.field label="Name (DE)" name="nameDe">
        <x-ui.input name="nameDe" :value="$continent->nameDe ?? ''" maxlength="200"/>
    </x-ui.field>

    <x-ui.field label="Name (EN)" name="nameEn">
        <x-ui.input name="nameEn" :value="$continent->nameEn ?? ''" maxlength="200"/>
    </x-ui.field>

    <div class="flex justify-end gap-2">
        <a href="{{ $cancelUrl }}">
            <x-ui.button variant="secondary">Cancel</x-ui.button>
        </a>
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
    </div>
</form>
