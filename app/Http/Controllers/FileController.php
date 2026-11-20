<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|in:image/,video/,audio/,application/pdf,text/',
        ]);

        $query = File::where('user_id', auth()->id())->latest();

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('type')) {
            $query->where('mime_type', 'like', $request->type.'%');
        }

        $files = $query->paginate(12)->withQueryString();

        return view('files.index', compact('files'));
    }

    public function create(): View
    {
        return view('files.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->guessExtension() ?? 'bin';
        $mimeType = $uploadedFile->getMimeType() ?? 'application/octet-stream';
        $size = $uploadedFile->getSize();
        $name = Str::uuid().'.'.$extension;
        $path = $uploadedFile->storeAs('uploads/'.auth()->id(), $name, 'local');

        try {
            File::create([
                'user_id' => auth()->id(),
                'name' => $name,
                'original_name' => $originalName,
                'path' => $path,
                'mime_type' => $mimeType,
                'size' => $size,
                'extension' => $extension,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()->route('files.index')->with('success', 'Archivo subido correctamente.');
    }

    public function show(File $file): View
    {
        $this->authorize('view', $file);

        return view('files.show', compact('file'));
    }

    public function download(File $file): StreamedResponse
    {
        $this->authorize('view', $file);

        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(File $file): RedirectResponse
    {
        $this->authorize('delete', $file);

        Storage::disk('local')->delete($file->path);
        $file->delete();

        return redirect()->route('files.index')->with('success', 'Archivo eliminado correctamente.');
    }
}
