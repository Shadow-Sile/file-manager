<x-app-layout>
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Files</h1>
        <a href="{{ route('files.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Create File
        </a>
    </div>

    <div class="overflow-hidden shadow-sm sm:rounded-lg">
        <div class="bg-white dark:bg-gray-800 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    <div class="col-span-6">Name</div>
                    <div class="col-span-3">Type</div>
                    <div class="col-span-3">Actions</div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($files as $file)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-750">
                        <div class="grid grid-cols-12 gap-4 text-sm">
                            <div class="col-span-6">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $file->name }}</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs truncate">{{ $file->path }}</div>
                            </div>
                            <div class="col-span-3 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                    {{ $file->type }}
                                </span>
                            </div>
                            <div class="col-span-3 flex items-center justify-end">
                                <a href="{{ route('files.show', $file) }}" class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    View
                                </a>
                                <form action="{{ route('files.destroy', $file) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-4 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No files found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>