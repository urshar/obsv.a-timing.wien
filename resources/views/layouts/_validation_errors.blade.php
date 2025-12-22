@if($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">
        <div class="font-semibold mb-2">Validation errors</div>
        <ul class="list-disc pl-5 space-y-1 text-sm">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
