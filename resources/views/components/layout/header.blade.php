@php
    use App\Support\Navigation;

    $navHeader = Navigation::header();
@endphp

<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-slate-900 text-white grid place-items-center font-semibold">
                    SA
                </div>
                <div>
                    <div class="text-lg font-semibold leading-tight">
                        <a href="{{ \Illuminate\Support\Facades\Route::has('home') ? route('home') : url('/') }}">
                            sport.a-timing.wien
                        </a>
                    </div>
                    <div class="text-sm text-slate-500">Admin</div>
                </div>
            </div>

            {{-- NAV --}}
            <nav x-data="{ openMenu: null }" class="flex flex-wrap items-center gap-2">
                @php $items = $navHeader ?? null; @endphp

                @if (is_array($items) && count($items))
                    @foreach($items as $item)
                        @php
                            $type = (string)($item['type'] ?? 'route');
                        @endphp

                        @if($type === 'separator')
                            <div class="w-px h-6 bg-slate-200 mx-1"></div>

                        @elseif($type === 'route')
                            <x-layout.nav-link
                                :href="route($item['route'])"
                                :active="nav_is_active($item['active'] ?? [])"
                            >
                                {{ $item['label'] ?? 'Link' }}
                            </x-layout.nav-link>

                        @elseif($type === 'dropdown')
                            @php
                                $childActive = false;
                                foreach (($item['items'] ?? []) as $c) {
                                    if (($c['type'] ?? '') === 'route' && nav_is_active($c['active'] ?? [])) {
                                        $childActive = true;
                                        break;
                                    }
                                }
                            @endphp

                            <x-layout.nav-dropdown
                                :key="($item['key'] ?? 'menu')"
                                :label="($item['label'] ?? 'Menu')"
                                :active="nav_is_active($item['active'] ?? []) || $childActive"
                            >
                                @foreach(($item['items'] ?? []) as $child)
                                    @php $childType = (string)($child['type'] ?? 'route'); @endphp

                                    @if($childType === 'label')
                                        <x-layout.nav-dropdown-label>
                                            {{ $child['label'] ?? '' }}
                                        </x-layout.nav-dropdown-label>

                                    @elseif($childType === 'separator')
                                        <div class="my-1 h-px bg-slate-200"></div>

                                    @elseif($childType === 'route')
                                        <x-layout.nav-dropdown-item
                                            :href="route($child['route'])"
                                            :active="nav_is_active($child['active'] ?? [])"
                                        >
                                            {{ $child['label'] ?? 'Item' }}
                                        </x-layout.nav-dropdown-item>
                                    @endif
                                @endforeach
                            </x-layout.nav-dropdown>
                        @endif
                    @endforeach
                @endif
            </nav>
        </div>
    </div>
</header>
