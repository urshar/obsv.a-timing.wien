@extends('layouts.app')
@section('title', 'Add session')

@section('content')
    <x-ui.page-title title="Add session" subtitle="Create a new session for this meeting."/>

    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('meets.structure.show', $meet) }}">
            <x-ui.button variant="secondary">Back</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <x-ui.card-header title="Session" subtitle="Basic information"/>
        <x-ui.card-body>
            <form method="POST" action="{{ route('meets.structure.sessions.store', $meet) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.field label="Session no" name="session_no">
                        <x-ui.input name="session_no" type="number" min="1" :value="old('session_no')" required/>
                    </x-ui.field>

                    <x-ui.field label="Date" name="date">
                        <x-ui.input name="date" type="date" :value="old('date')"/>
                    </x-ui.field>

                    <x-ui.field label="Start time" name="start_time">
                        <x-ui.input name="start_time" type="time" :value="old('start_time')"/>
                    </x-ui.field>

                    <x-ui.field label="Name" name="name">
                        <x-ui.input name="name" :value="old('name')" placeholder="Optional"/>
                    </x-ui.field>
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="secondary" type="reset">Reset</x-ui.button>
                    <x-ui.button type="submit">Create</x-ui.button>
                </div>
            </form>
        </x-ui.card-body>
    </x-ui.card>
@endsection
