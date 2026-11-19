@extends('layouts.app', ['title' => 'Subir Archivo'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('files.index') }}" class="text-blue-600 hover:text-blue-700 text-sm flex items-center gap-1 mb-4 inline-block">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver a Mis Archivos
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subir Nuevo Archivo</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Tamaño máximo: 50 MB</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Seleccionar archivo
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-blue-500 dark:hover:border-blue-500 transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Arrastra y suelta un archivo aquí, o haz clic para seleccionar
                    </p>
                    <input type="file"
                           name="file"
                           id="file"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                           required
                           accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.txt,.zip,.rar">
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">PNG, JPG, PDF, DOC, ZIP, MP4, MP3... (máx. 50MB)</p>
                </div>
                @error('file')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('files.index') }}"
                   class="flex-1 text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    Subir Archivo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection