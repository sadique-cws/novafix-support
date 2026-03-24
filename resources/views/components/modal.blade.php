@props([
    'name',
    'title' => null,
    'maxWidth' => 'max-w-lg',
])

<div>
    <div class="fixed inset-0 z-40 hidden bg-black/40" data-modal-overlay="{{ $name }}"></div>

    <div class="fixed inset-0 z-50 hidden overflow-y-auto" data-modal="{{ $name }}" aria-modal="true" role="dialog">
        <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
            <div class="w-full {{ $maxWidth }} rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="text-sm font-semibold text-gray-900">{{ $title }}</div>
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
                        data-modal-close="{{ $name }}"
                        aria-label="Close">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-5 py-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>

