<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Assets
            </h2>
            @can('create', App\Models\Asset::class)
            @endcan
            @if(auth()->user()->isAdmin() || auth()->user()->isEditor())
            <a href="{{ route('assets.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + Subir asset
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            @if($assets->isEmpty())
            <div class="text-center py-20 text-gray-500 dark:text-gray-400">
                No hay assets todavía.
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($assets as $asset)
                <a href="{{ route('assets.show', $asset) }}"
                   class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition p-4 block">

                    {{-- Previsualización si es imagen --}}
                    @if(str_starts_with($asset->mime_type, 'image/'))
                    <img src="{{ Storage::url($asset->path) }}"
                         alt="{{ $asset->original_name }}"
                         class="w-full h-40 object-cover rounded-lg mb-3">
                    @else
                    <div class="w-full h-40 bg-gray-100 dark:bg-gray-700 rounded-lg mb-3 flex items-center justify-center">
                        <span class="text-4xl">📄</span>
                    </div>
                    @endif

                    <p class="font-medium text-gray-800 dark:text-gray-200 truncate">
                        {{ $asset->metadata?->title ?? $asset->original_name }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ auth()->user()->name }} •
                        {{ number_format($asset->size / 1024, 1) }} KB
                    </p>

                    @if($asset->status === 'pending')
                    <span class="inline-block mt-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                        Pendiente de IA
                    </span>
                    @elseif($asset->status === 'processed')
                    <span class="inline-block mt-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                        Procesado
                    </span>
                    @endif
                </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $assets->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>