<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMetadata;
use App\Models\Category;
use App\Services\GeminiService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    use LogsActivity;

    // Listar todos los assets
    public function index(Request $request)
    {
        $query = Asset::with(['user', 'metadata', 'categories']);

        // Búsqueda por nombre
        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('mime_type', 'like', $request->type . '%');
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por categoría
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        $assets = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::whereNull('parent_id')->with('children')->get();

        return view('assets.index', compact('assets', 'categories'));
    }

    // Mostrar formulario de subida
    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('assets.create', compact('categories'));
    }

    // Guardar el asset subido
    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('assets', $filename, 'public');

        $asset = Asset::create([
            'user_id'       => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'filename'      => $filename,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'path'          => $path,
            'status'        => 'pending',
        ]);

        if ($request->has('categories')) {
            $asset->categories()->sync($request->categories);
        }

        // Llamada a Gemini para generar metadatos
        $gemini = new GeminiService();
        $metadata = $gemini->generateAssetMetadata(
            $file->getClientOriginalName(),
            $file->getMimeType()
        );

        AssetMetadata::create([
            'asset_id'     => $asset->id,
            'title'        => $metadata['title'],
            'description'  => $metadata['description'],
            'tags'         => $metadata['tags'],
            'ai_generated' => true,
        ]);

        $asset->update(['status' => 'processed']);

        $this->logActivity('upload', $asset, ['filename' => $asset->original_name]);

        return redirect()->route('assets.show', $asset)
                         ->with('success', 'Asset subido y metadatos generados por IA.');
    }

    // Ver un asset individual
    public function show(Asset $asset)
    {
        $asset->load(['user', 'metadata', 'categories']);
        return view('assets.show', compact('asset'));
    }

    // Mostrar formulario de edición
    public function edit(Asset $asset)
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('assets.edit', compact('asset', 'categories'));
    }

    // Actualizar el asset
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        if ($request->has('categories')) {
            $asset->categories()->sync($request->categories);
        }

        $asset->metadata()->updateOrCreate(
            ['asset_id' => $asset->id],
            [
                'title'        => $request->title,
                'description'  => $request->description,
                'tags'         => $request->tags ? explode(',', $request->tags) : null,
                'ai_generated' => false,
            ]
        );

        $this->logActivity('edit', $asset);

        return redirect()->route('assets.show', $asset)
                         ->with('success', 'Asset actualizado correctamente.');
    }

    // Borrar el asset
    public function destroy(Asset $asset)
    {
        Storage::disk('public')->delete($asset->path);

        $this->logActivity('delete', null, ['filename' => $asset->original_name]);

        $asset->delete();

        return redirect()->route('assets.index')
                         ->with('success', 'Asset eliminado correctamente.');
    }
}