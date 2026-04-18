{{-- Wicom-style sidebar: icons, collapsible groups, active route highlighting (Alpine) --}}
@php
    $user = auth()->user();
    $isHome = request()->routeIs('home');
@endphp

<div
    x-data="{
        overview: {{ $isHome || request()->is('/') ? 'true' : 'false' }},
        portals: {{ ($user && ((int) $user->role_id === 6 || (int) $user->role_id === 7)) ? 'true' : 'false' }},
        workspace: {{ (Route::has('dashboard') || Route::has('my.profile')) ? 'true' : 'false' }},
    }"
    class="space-y-1"
>
    <ul class="space-y-0.5 px-2">
        <li>
            <a href="{{ url('/') }}"
               class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->is('/') && ! $isHome ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fa-solid fa-house w-5 shrink-0 text-center text-gray-500 group-hover:text-blue-600" aria-hidden="true"></i>
                {{ __('Home') }}
            </a>
        </li>

        @auth
            @if (Route::has('home'))
                <li>
                    <a href="{{ route('home') }}"
                       class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isHome ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-table-columns w-5 shrink-0 text-center text-gray-500 group-hover:text-blue-600" aria-hidden="true"></i>
                        {{ ___('common.Dashboard') }}
                    </a>
                </li>
            @endif

            @if (Route::has('dashboard') || Route::has('my.profile'))
                <li class="rounded-lg">
                    <button type="button"
                        @click="workspace = !workspace"
                        class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-800 transition hover:bg-gray-50">
                        <span class="flex min-w-0 items-center gap-3">
                            <i class="fa-solid fa-briefcase w-5 shrink-0 text-center text-gray-500" aria-hidden="true"></i>
                            <span class="truncate">{{ __('Workspace') }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="workspace ? 'rotate-180' : ''" aria-hidden="true"></i>
                    </button>
                    <ul x-show="workspace" class="mt-0.5 space-y-0.5 border-l border-gray-100 pb-1 pl-2 ml-4" x-cloak>
                        @if (Route::has('dashboard'))
                            <li>
                                <a href="{{ route('dashboard') }}"
                                   class="sidebar-link flex items-center gap-2 rounded-lg py-2 pl-3 pr-2 text-sm transition {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.*') ? 'bg-blue-50 font-medium text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                    {{ ___('common.dashboard') }}
                                </a>
                            </li>
                        @endif
                        @if (Route::has('my.profile'))
                            <li>
                                <a href="{{ route('my.profile') }}"
                                   class="sidebar-link flex items-center gap-2 rounded-lg py-2 pl-3 pr-2 text-sm transition {{ request()->routeIs('my.*') ? 'bg-blue-50 font-medium text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                    {{ ___('common.my_profile') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if ($user && ((int) $user->role_id === 6 || (int) $user->role_id === 7))
                <li class="rounded-lg">
                    <button type="button" @click="portals = !portals"
                        class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-800 hover:bg-gray-50">
                        <span class="flex min-w-0 items-center gap-3">
                            <i class="fa-solid fa-door-open w-5 shrink-0 text-center text-gray-500" aria-hidden="true"></i>
                            <span class="truncate">{{ __('Portals') }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="portals ? 'rotate-180' : ''" aria-hidden="true"></i>
                    </button>
                    <ul x-show="portals" class="mt-0.5 space-y-0.5 border-l border-gray-100 pb-1 pl-2 ml-4" x-cloak>
                        @if ((int) $user->role_id === 6)
                            <li>
                                <a href="{{ spa_url('student-panel') }}" class="sidebar-link flex items-center gap-2 rounded-lg py-2 pl-3 pr-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                    <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                    {{ __('Student portal') }}
                                </a>
                            </li>
                        @endif
                        @if ((int) $user->role_id === 7)
                            <li>
                                <a href="{{ spa_url('parent-panel') }}" class="sidebar-link flex items-center gap-2 rounded-lg py-2 pl-3 pr-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                    <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-gray-300"></span>
                                    {{ __('Parent portal') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        @else
            @if (Route::has('login'))
                <li>
                    <a href="{{ route('login') }}"
                       class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('login') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-right-to-bracket w-5 shrink-0 text-center text-gray-500 group-hover:text-blue-600" aria-hidden="true"></i>
                        {{ ___('common.Login') }}
                    </a>
                </li>
            @endif
            @if (Route::has('register.page'))
                <li>
                    <a href="{{ route('register.page') }}"
                       class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('register.page') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                        <i class="fa-solid fa-user-plus w-5 shrink-0 text-center text-gray-500 group-hover:text-blue-600" aria-hidden="true"></i>
                        {{ ___('common.Register') }}
                    </a>
                </li>
            @endif
        @endauth
    </ul>
</div>
