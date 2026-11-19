@extends('layouts.app', ['title' => 'Mis Archivos'])

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mis Archivos</h1>

    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
        <form action="{{ route('files.index') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar archivos..."
                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <select name="type" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">Todos los tipos</option>
                <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Imágenes</option>
                <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
                <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>Audio</option>
                <option value="application" {{ request('type') === 'application' ? 'selected' : '' }}>Documentos</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Filtrar</button>
            @if (request('search') || request('type'))
                <a href="{{ route('files.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Limpiar</a>
            @endif
        </form>
    </div>
</div>

@if ($files->isEmpty())
    <div class="text-center py-16">
        <div class="text-6xl mb-4">📁</div>
        <h2 class="text-xl font-medium text-gray-900 dark:text-white mb-2">No hay archivos</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">Comienza subiendo tu primer archivo</p>
        <a href="{{ route('files.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Subir Archivo
        </a>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-8">
        @foreach ($files as $file)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition duration-200 group">
                <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-4xl relative">
                    @if (str_starts_with($file->mime_type, 'image'))
                        <img src="{{ Storage::url($file->path) }}" alt="{{ $file->original_name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-5xl">{{ $file->icon }}</span>
                    @endif

                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                        <a href="{{ route('files.show', $file) }}"
                           class="bg-white/90 dark:bg-gray-800/90 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-900 dark:text-white hover:bg-white dark:hover:bg-gray-700">
                            Ver
                        </a>
                        <a href="{{ route('files.download', $file) }}"
                           class="bg-blue-600 px-3 py-1.5 rounded-lg text-sm font-medium text-white hover:bg-blue-700">
                            Descargar
                        </a>
                    </div>
                </div>

                <div class="p-3">
                    <h3 class="font-medium text-gray-900 dark:text-white text-sm truncate" title="{{ $file->original_name }}">
                        {{ $file->original_name }}
                    </h3>
                    <div class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $file->formatted_size }}</span>
                        <form action="{{ route('files.destroy', $file) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('¿Estás seguro de eliminar este archivo?')"
                                    class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-center">
        {{ $files->links() }}
    </div>
@endif
@endsection