@extends('layouts.app')
@section('title','Edit Meeting')

@section('content')
    <x-ui.page-title
        title="Edit Meeting"
        subtitle="Update base data for this meeting."
    />

    <div class="mb-4 flex items-center justify-between">
        <div class="flex gap-2">
            <a href="{{ route('meets.index') }}">
                <x-ui.button variant="secondary">Back</x-ui.button>
            </a>

            <a href="{{ route('meets.structure.show', $meet) }}">
                <x-ui.button variant="ghost">Structure</x-ui.button>
            </a>
        </div>
    </div>

    <x-ui.card>
        <x-ui.card-header title="Edit" subtitle="Meeting base data"/>

        <x-ui.card-body>
            <form method="POST" action="{{ route('meets.update', $meet) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                    <x-ui.field label="Name" name="name">
                        <x-ui.input name="name" :value="old('name', $meet->name)" placeholder="Meeting name"/>
                    </x-ui.field>

                    <x-ui.field label="Course" name="course" hint="Optional (e.g. LCM/SCM)">
                        <x-ui.input name="course" :value="old('course', $meet->course)" placeholder="SCM"/>
                    </x-ui.field>

                    <x-ui.field label="Start date" name="start_date" compact>
                        <x-ui.input type="date" name="start_date"
                                    :value="old('start_date', optional($meet->start_date)->format('Y-m-d'))"/>
                    </x-ui.field>

                    <x-ui.field label="End date" name="end_date" compact>
                        <x-ui.input type="date" name="end_date"
                                    :value="old('end_date', optional($meet->end_date)->format('Y-m-d'))"/>
                    </x-ui.field>

                    <x-ui.field label="Age date" name="age_date" compact
                                hint="Optional reference date for age calculation">
                        <x-ui.input type="date" name="age_date"
                                    :value="old('age_date', optional($meet->age_date)->format('Y-m-d'))"/>
                    </x-ui.field>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <x-ui.field label="Contact name" name="contact_name" compact>
                        <x-ui.input name="contact_name" :value="old('contact_name', $meet->contact_name)"
                                    placeholder="Optional"/>
                    </x-ui.field>

                    <x-ui.field label="Contact email" name="contact_email" compact>
                        <x-ui.input type="email" name="contact_email"
                                    :value="old('contact_email', $meet->contact_email)" placeholder="Optional"/>
                    </x-ui.field>

                    <x-ui.field label="Contact phone" name="contact_phone" compact>
                        <x-ui.input name="contact_phone" :value="old('contact_phone', $meet->contact_phone)"
                                    placeholder="Optional"/>
                    </x-ui.field>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('meets.index') }}">
                        <x-ui.button type="button" variant="ghost">Cancel</x-ui.button>
                    </a>
                    <x-ui.button type="submit">Save</x-ui.button>
                </div>
            </form>
        </x-ui.card-body>
    </x-ui.card>
@endsection
