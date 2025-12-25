@props([
    // Required
    'batch',
    'entityType',
    'sourceKey',

    // Optional
    'suggestions' => [],          // array of ['id' => ..., 'label' => ...]
    'isDisabled' => false,        // true => hide controls, show info text
    'disabledText' => 'No mapping required.',

    // Which actions are available?
    'allowCreate' => true,
    'allowIgnore' => true,
    'allowLink' => true,

    // Allow manual link input (target_id) if no suggestions or user prefers manual
    'allowManualLink' => false,
    'manualLinkPlaceholder' => 'Target ID…',

    // Labels
    'createLabel' => 'Create',
    'ignoreLabel' => 'Ignore',
    'linkLabel'   => 'Link',

    // Route override
    'mapRoute' => 'imports.lenex.map',
])

@php
    $suggestions = is_array($suggestions) ? $suggestions : [];
    $hasSuggestions = !empty($suggestions) && count($suggestions) > 0;

    // Optional improvement: unique input name per entity type to avoid error-highlight collisions
    $manualInputName = 'target_id_' . strtolower((string)$entityType);
@endphp

<div class="flex flex-wrap gap-2">
    @if($isDisabled)
        <span class="text-xs text-slate-600 italic">
            {{ $disabledText }}
        </span>
    @else
        @if($allowCreate)
            <form method="POST" action="{{ route($mapRoute, $batch) }}">
                @csrf
                <input type="hidden" name="entity_type" value="{{ $entityType }}"/>
                <input type="hidden" name="source_key" value="{{ $sourceKey }}"/>
                <input type="hidden" name="action" value="create"/>
                <x-ui.button type="submit" variant="secondary">{{ $createLabel }}</x-ui.button>
            </form>
        @endif

        @if($allowIgnore)
            <form method="POST" action="{{ route($mapRoute, $batch) }}">
                @csrf
                <input type="hidden" name="entity_type" value="{{ $entityType }}"/>
                <input type="hidden" name="source_key" value="{{ $sourceKey }}"/>
                <input type="hidden" name="action" value="ignore"/>
                <x-ui.button type="submit" variant="secondary">{{ $ignoreLabel }}</x-ui.button>
            </form>
        @endif

        @if($allowLink && $hasSuggestions)
            <form method="POST" action="{{ route($mapRoute, $batch) }}"
                  class="flex flex-col sm:flex-row items-start sm:items-end gap-2">
                @csrf
                <input type="hidden" name="entity_type" value="{{ $entityType }}"/>
                <input type="hidden" name="source_key" value="{{ $sourceKey }}"/>
                <input type="hidden" name="action" value="link"/>

                <div class="min-w-65">
                    <x-ui.select name="target_id">
                        @foreach($suggestions as $s)
                            <option value="{{ $s['id'] }}">{{ $s['label'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <x-ui.button type="submit">{{ $linkLabel }}</x-ui.button>
            </form>
        @elseif($allowLink && $allowManualLink)
            <form method="POST" action="{{ route($mapRoute, $batch) }}"
                  class="flex flex-col sm:flex-row items-start sm:items-end gap-2">
                @csrf
                <input type="hidden" name="entity_type" value="{{ $entityType }}"/>
                <input type="hidden" name="source_key" value="{{ $sourceKey }}"/>
                <input type="hidden" name="action" value="link"/>

                <div class="min-w-50">
                    <x-ui.input
                        name="{{ $manualInputName }}"
                        type="number"
                        :placeholder="$manualLinkPlaceholder"
                    />
                </div>

                <x-ui.button type="submit">{{ $linkLabel }}</x-ui.button>
            </form>
        @endif
    @endif
</div>

