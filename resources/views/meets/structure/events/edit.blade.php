@extends('layouts.app')
@section('title', 'Edit event')

@section('content')
    <x-ui.page-title title="Edit event" subtitle="Update this event."/>

    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('meets.structure.show', $meet) }}">
            <x-ui.button variant="secondary">Back</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <x-ui.card-header title="Event">
            <div class="text-sm text-slate-600">
                Session {{ $session->session_no }}
                @if($session->date)
                    ·
                    <x-ui.date :value="$session->date"/>
                @endif
            </div>
        </x-ui.card-header>

        <x-ui.card-body>
            <form method="POST" action="{{ route('meets.structure.events.update', [$meet, $event]) }}"
                  class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.field label="Event no" name="event_no">
                        <x-ui.input name="event_no" type="number" min="1"
                                    :value="old('event_no', $event->event_no)" required/>
                    </x-ui.field>

                    <x-ui.field label="Name" name="name">
                        <x-ui.input name="name" :value="old('name', $event->name)" required/>
                    </x-ui.field>

                    <x-ui.field label="Gender" name="gender">
                        <x-ui.select name="gender">
                            <option value="" @selected(old('gender', $event->gender ?? '') === '')>All</option>
                            <option value="F" @selected(old('gender', $event->gender) === 'F')>Women</option>
                            <option value="M" @selected(old('gender', $event->gender) === 'M')>Men</option>
                            <option value="X" @selected(old('gender', $event->gender) === 'X')>Mixed</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Distance (m)" name="distance">
                        <x-ui.input name="distance" type="number" min="1"
                                    :value="old('distance', $event->distance)"/>
                    </x-ui.field>

                    <x-ui.field label="Swim style" name="stroke">
                        <x-ui.select name="stroke">
                            <option value="" @selected(old('stroke', $event->stroke ?? '') === '')>—</option>
                            @foreach($swimStyles as $style)
                                <option value="{{ $style->key }}"
                                    @selected(old('stroke', $event->stroke) === $style->key)>
                                    {{ $style->display() }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Round" name="round">
                        <x-ui.input name="round" :value="old('round', $event->round)"/>
                    </x-ui.field>

                    <div class="sm:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_relay" value="1"
                                @checked(old('is_relay', (bool) $event->is_relay))>
                            Relay
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="secondary" type="reset">Reset</x-ui.button>
                    <x-ui.button type="submit">Save</x-ui.button>
                </div>
            </form>
        </x-ui.card-body>
    </x-ui.card>
@endsection
