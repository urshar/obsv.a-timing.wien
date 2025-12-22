@props([
    'cancelUrl',
    'confirmAction',
    'token',
    'storagePath',
    'hidden' => [],
    'confirmLabel' => 'Confirm Import',
    'cancelLabel' => 'Cancel',
])

<div class="px-4 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
    <a href="{{ $cancelUrl }}"
       class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
        {{ $cancelLabel }}
    </a>

    <form method="POST" action="{{ $confirmAction }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="storagePath" value="{{ $storagePath }}">

        @foreach($hidden as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach

        <button
            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
            {{ $confirmLabel }}
        </button>
    </form>
</div>
