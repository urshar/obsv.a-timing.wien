@extends('layouts.app')
@section('title', 'Edit age group')

@section('content')
    <x-ui.page-title title="Edit age group" subtitle="Update this age group."/>

    <div class="mb-4">
        <a href="{{ route('meets.structure.show', $meet) }}">
            <x-ui.button variant="secondary">Back</x-ui.button>
        </a>
    </div>

    <x-ui.card>
        <x-ui.card-header title="Age group"/>
        <x-ui.card-body>
            <form method="POST" action="{{ route('meets.structure.age_groups.update', [$meet, $ageGroup]) }}"
                  class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.field label="Name" name="name">
                        <x-ui.input name="name" :value="old('name', $ageGroup->name)" required/>
                    </x-ui.field>

                    <x-ui.field label="Code" name="code">
                        <x-ui.input name="code" :value="old('code', $ageGroup->code)" placeholder="e.g. 1464"/>
                    </x-ui.field>

                    <x-ui.field label="Gender" name="gender">
                        <x-ui.select name="gender">
                            <option value="" @selected(old('gender', $ageGroup->gender ?? '') === '')>All</option>
                            <option value="F" @selected(old('gender', $ageGroup->gender) === 'F')>Women</option>
                            <option value="M" @selected(old('gender', $ageGroup->gender) === 'M')>Men</option>
                            <option value="X" @selected(old('gender', $ageGroup->gender) === 'X')>Mixed</option>
                        </x-ui.select>
                    </x-ui.field>

                    <x-ui.field label="Sport classes" name="handicap">
                        <x-ui.input name="handicap" :value="old('handicap', $ageGroup->handicap)"
                                    placeholder="e.g. 1,2,3 or 14"/>
                    </x-ui.field>

                    <x-ui.field label="Min age" name="min_age">
                        <x-ui.input name="min_age" type="number" min="0" max="200"
                                    :value="old('min_age', $ageGroup->min_age)"/>
                    </x-ui.field>

                    <x-ui.field label="Max age" name="max_age">
                        <x-ui.input name="max_age" type="number" min="0" max="200"
                                    :value="old('max_age', $ageGroup->max_age)"/>
                    </x-ui.field>
                </div>

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="secondary" type="reset">Reset</x-ui.button>
                    <x-ui.button type="submit">Save</x-ui.button>
                </div>
            </form>
        </x-ui.card-body>
    </x-ui.card>
@endsection
