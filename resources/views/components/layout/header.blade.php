<header class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-slate-900 text-white grid place-items-center font-semibold">
                    SA
                </div>
                <div>
                    <div class="text-lg font-semibold leading-tight">sport.a-timing.wien</div>
                    <div class="text-sm text-slate-500">Admin</div>
                </div>
            </div>

            <nav class="flex flex-wrap items-center gap-2">
                <a href="{{ route('continents.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 {{ request()->routeIs('continents.*') ? 'bg-slate-900 text-white hover:bg-slate-900' : '' }}">
                    Continents
                </a>
                <a href="{{ route('nations.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 {{ request()->routeIs('nations.*') ? 'bg-slate-900 text-white hover:bg-slate-900' : '' }}">
                    Nations
                </a>
                <a href="{{ route('regions.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 {{ request()->routeIs('regions.*') ? 'bg-slate-900 text-white hover:bg-slate-900' : '' }}">
                    Regions
                </a>

                <div class="w-px h-6 bg-slate-200 mx-1"></div>

                <a href="{{ route('nations.import.show') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 {{ request()->routeIs('nations.import.*') ? 'bg-slate-900 text-white hover:bg-slate-900' : '' }}">
                    Import Nations
                </a>
                <a href="{{ route('regions.import.show') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-slate-100 {{ request()->routeIs('regions.import.*') ? 'bg-slate-900 text-white hover:bg-slate-900' : '' }}">
                    Import Regions
                </a>
            </nav>
        </div>
    </div>
</header>
