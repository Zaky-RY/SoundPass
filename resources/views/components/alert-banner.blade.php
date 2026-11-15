@props([
    'type' => 'success',
    'message' => null,
])

@php
    $configs = [
        'success' => [
            'bg' => 'bg-emerald-50 border-emerald-300 text-emerald-900',
            'iconBg' => 'bg-emerald-100 text-emerald-600',
            'title' => 'Berhasil',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />',
        ],
        'error' => [
            'bg' => 'bg-rose-50 border-rose-300 text-rose-900',
            'iconBg' => 'bg-rose-100 text-rose-600',
            'title' => 'Terjadi Kesalahan',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 border-amber-300 text-amber-900',
            'iconBg' => 'bg-amber-100 text-amber-600',
            'title' => 'Peringatan',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        ],
        'info' => [
            'bg' => 'bg-indigo-50 border-indigo-300 text-indigo-900',
            'iconBg' => 'bg-indigo-100 text-indigo-600',
            'title' => 'Informasi',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
    ];

    $cfg = $configs[$type] ?? $configs['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition.duration.300ms class="mb-6 rounded-xl border p-4 shadow-sm {{ $cfg['bg'] }}" role="alert">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $cfg['iconBg'] }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $cfg['icon'] !!}
                </svg>
            </span>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="text-sm font-semibold">{{ $cfg['title'] }}</p>
            <p class="mt-0.5 text-sm leading-relaxed opacity-90">
                {{ $message ?? $slot }}
            </p>
        </div>
        <button type="button" @click="show = false" class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-200/50 hover:text-slate-800 transition" aria-label="Tutup notifikasi">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
