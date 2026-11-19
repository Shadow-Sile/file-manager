<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
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

    public function create()
    {
        return view('files.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $mimeType = $uploadedFile->getClientMimeType();
        $size = $uploadedFile->getSize();
        $name = Str::uuid().'.'.$extension;
        $path = $uploadedFile->storeAs('uploads/'.auth()->id(), $name, 'public');

        File::create([
            'user_id' => auth()->id(),
            'name' => $name,
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
        ]);

        return redirect()->route('files.index')->with('success', 'Archivo subido correctamente.');
    }

    public function show(File $file)
    {
        $this->authorize('view', $file);

        return view('files.show', compact('file'));
    }

    public function download(File $file)
    {
        $this->authorize('view', $file);

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return redirect()->route('files.index')->with('success', 'Archivo eliminado correctamente.');
    }
}
