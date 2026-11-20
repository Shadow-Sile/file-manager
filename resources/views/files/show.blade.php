<x-app-layout>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalles del archivo</h1>
        <a href="{{ route('files.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Volver a archivos
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">{{ $file->original_name }}</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detalles</h3>
                    <div class="mt-2 space-y-2">
                        <div class="flex">
                            <div class="w-1/3 text-sm text-gray-500 dark:text-gray-400">Ruta:</div>
                            <div class="w-2/3 text-sm text-gray-900 dark:text-white break-all">{{ $file->path }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-1/3 text-sm text-gray-500 dark:text-gray-400">Tipo:</div>
                            <div class="w-2/3 text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                    {{ $file->mime_type }}
                                </span>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="w-1/3 text-sm text-gray-500 dark:text-gray-400">Tamaño:</div>
                            <div class="w-2/3 text-sm text-gray-900 dark:text-white">{{ $file->formatted_size }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-1/3 text-sm text-gray-500 dark:text-gray-400">Subido:</div>
                            <div class="w-2/3 text-sm text-gray-900 dark:text-white">{{ $file->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</h3>
                    <div class="mt-2 space-y-2">
                        <a href="{{ route('files.download', $file) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Descargar
                        </a>
                        <form action="{{ route('files.destroy', $file) }}" method="POST" style="display: inline;" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('¿Eliminar este archivo?')">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>