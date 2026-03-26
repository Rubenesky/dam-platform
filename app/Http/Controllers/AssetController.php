<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMetadata;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\GeminiService;

class AssetController extends Controller
{
    // Listar todos los assets
    public function index()
    {
        $assets = Asset::with(['user', 'metadata', 'categories'])
                       ->latest()
                       ->paginate(12);

        return view('assets.index', compact('assets'));
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

        // Marcamos el asset como procesado
        $asset->update(['status' => 'processed']);

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

        // Actualizamos los metadatos
        $asset->metadata()->updateOrCreate(
            ['asset_id' => $asset->id],
            [
                'title'        => $request->title,
                'description'  => $request->description,
                'tags'         => $request->tags ? explode(',', $request->tags) : null,
                'ai_generated' => false,
            ]
        );

        return redirect()->route('assets.show', $asset)
                         ->with('success', 'Asset actualizado correctamente.');
    }

    // Borrar el asset
    public function destroy(Asset $asset)
    {
        // Borramos el archivo físico del disco
        Storage::disk('public')->delete($asset->path);

        // Borramos el registro de la BD (cascade borra metadata)
        $asset->delete();

        return redirect()->route('assets.index')
                         ->with('success', 'Asset eliminado correctamente.');
    }
}