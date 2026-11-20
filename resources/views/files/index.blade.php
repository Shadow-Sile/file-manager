<x-app-layout>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mis archivos</h1>
        <a href="{{ route('files.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Subir archivo
        </a>
    </div>

    @if (session('success'))
        <div role="status" class="mb-4 rounded-md bg-green-100 p-4 text-green-800">{{ session('success') }}</div>
    @endif
    <form method="GET" action="{{ route('files.index') }}" class="mb-4 flex flex-wrap gap-3">
        <input type="search" name="search" aria-label="Buscar archivos" placeholder="Buscar por nombre" value="{{ request('search') }}" maxlength="255" class="rounded-md border-gray-300">
        <select name="type" aria-label="Tipo de archivo" class="rounded-md border-gray-300">
            <option value="">Todos los tipos</option>
            @foreach (['image/' => 'Imágenes', 'application/pdf' => 'PDF', 'text/' => 'Texto', 'video/' => 'Vídeo', 'audio/' => 'Audio'] as $value => $label)
                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-md bg-blue-600 px-4 py-2 text-white">Filtrar</button>
        <a href="{{ route('files.index') }}" class="px-4 py-2">Limpiar</a>
    </form>
    <div class="overflow-hidden shadow-sm sm:rounded-lg">
        <div class="bg-white dark:bg-gray-800 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <div class="col-span-6">Nombre</div>
                    <div class="col-span-3">Tipo</div>
                    <div class="col-span-3">Acciones</div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($files as $file)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-750">
                        <div class="grid grid-cols-12 gap-4 text-sm">
                            <div class="col-span-6">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $file->icon }} {{ $file->original_name }}</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs truncate">{{ $file->formatted_size }}</div>
                            </div>
                            <div class="col-span-3 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                    {{ $file->mime_type }}
                                </span>
                            </div>
                            <div class="col-span-3 flex items-center justify-end">
                                <a href="{{ route('files.show', $file) }}" class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Ver
                                </a>
                                <form action="{{ route('files.destroy', $file) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('¿Eliminar este archivo?')">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-4 text-center">
                        <p class="text-gray-500 dark:text-gray-400">Todavía no hay archivos. Sube el primero.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="mt-4">{{ $files->links() }}</div>
</x-app-layout>