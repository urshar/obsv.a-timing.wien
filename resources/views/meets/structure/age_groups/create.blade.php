@extends('layouts.app')
@section('title', 'Create age group')

@section('content')
    <x-ui.page-title title="Create age group" subtitle="Add a new age group to this meet."/>

    <div class="mb-4">
        <a href="{{ route('meets.structure.show', $meet) }}">
            <x-ui.button variant="secondary">Back</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <x-ui.card-header title="Age group"/>
        <x-ui.card-body>
            <form method="POST" action="{{ route('meets.structure.age_groups.store', $meet) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.field label="Name" name="name">
                        <x-ui.input name="name" :value="old('name')" required/>
                    </x-ui.field>

                    <x-ui.field label="Code" name="code">
                        <x-ui.input name="code" :value="old('code')" placeholder="e.g. 1464"/>
                    </x-ui.field>

                    <x-ui.field label="Gender" name="gender">
                        <x-ui.select name="gender">
                            <option value="" @selected(old('gender', '') === '')>All</option>
                            <option value="F" @selected(old('gender') === 'F')>Women</option>
                            <option value="M" @selected(old('gender') === 'M')>Men</option>
                            <option value="X" @selected(old('gender') === 'X')>Mixed</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Sport classes" name="handicap">
                        <x-ui.input name="handicap" :value="old('handicap')" placeholder="e.g. 1,2,3 or 14"/>
                    </x-ui.field>

                    <x-ui.field label="Min age" name="min_age">
                        <x-ui.input name="min_age" type="number" min="0" max="200" :value="old('min_age')"/>
                    </x-ui.field>

                    <x-ui.field label="Max age" name="max_age">
                        <x-ui.input name="max_age" type="number" min="0" max="200" :value="old('max_age')"/>
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
