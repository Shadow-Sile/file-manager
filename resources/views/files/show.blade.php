@extends('layouts.app', ['title' => $file->original_name])

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('files.index') }}" class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1 mb-2 inline-block">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a Mis Archivos
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $file->original_name }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('files.download', $file) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Descargar
            </a>
            <form action="{{ route('files.destroy', $file) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('¿Estás seguro de eliminar este archivo?')"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="aspect-video bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
            @if (str_starts_with($file->mime_type, 'image'))
                <img src="{{ Storage::url($file->path) }}" alt="{{ $file->original_name }}" class="max-w-full max-h-[600px] object-contain">
            @elseif (str_starts_with($file->mime_type, 'video'))
                <video controls class="max-w-full max-h-[600px]">
                    <source src="{{ Storage::url($file->path) }}" type="{{ $file->mime_type }}">
                    Tu navegador no soporta la reproducción de video.
                </video>
            @elseif (str_starts_with($file->mime_type, 'audio'))
                <audio controls class="w-full max-w-md">
                    <source src="{{ Storage::url($file->path) }}" type="{{ $file->mime_type }}">
                    Tu navegador no soporta la reproducción de audio.
                </audio>
            @elseif (str_starts_with($file->mime_type, 'application/pdf'))
                <iframe src="{{ Storage::url($file->path) }}" class="w-full h-[600px] border-0"></iframe>
            @elseif (str_starts_with($file->mime_type, 'text/'))
                <pre class="w-full h-[600px] p-4 overflow-auto text-sm bg-gray-900 text-green-300">{{ file_get_contents(Storage::path($file->path)) }}</pre>
            @else
                <div class="text-center">
                    <span class="text-8xl">{{ $file->icon }}</span>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">Vista previa no disponible para este tipo de archivo</p>
                    <a href="{{ route('files.download', $file) }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Descargar para ver
                    </a>
                </div>
            @endif
        </div>

        <div class="p-6 border-t border-gray-200 dark:border-gray-700">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre original</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->original_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo MIME</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->mime_type }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tamaño</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->formatted_size }} ({{ $file->size }} bytes)</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Extensión</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->extension ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subido el</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última modificación</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $file->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection