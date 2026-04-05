<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetApiController extends Controller
{
    // GET /api/assets
    public function index(): JsonResponse
    {
        $assets = Asset::with(['user', 'metadata', 'categories'])
                       ->latest()
                       ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $assets->map(function ($asset) {
                return $this->formatAsset($asset);
            }),
            'meta' => [
                'total'        => $assets->total(),
                'per_page'     => $assets->perPage(),
                'current_page' => $assets->currentPage(),
                'last_page'    => $assets->lastPage(),
            ]
        ]);
    }

    // GET /api/assets/{id}
    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['user', 'metadata', 'categories']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatAsset($asset),
        ]);
    }

    // DELETE /api/assets/{id}
    public function destroy(Asset $asset): JsonResponse
    {

        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar assets.',
            ], 403);
        }

        $asset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asset eliminado correctamente.',
        ]);
    }

    // Formatea un asset para la respuesta JSON
    private function formatAsset(Asset $asset): array
    {
        return [
            'id'            => $asset->id,
            'original_name' => $asset->original_name,
            'mime_type'     => $asset->mime_type,
            'size_kb'       => round($asset->size / 1024, 2),
            'status'        => $asset->status,
            'url'           => asset('storage/' . $asset->path),
            'uploaded_by'   => $asset->user->name,
            'metadata'      => $asset->metadata ? [
                'title'        => $asset->metadata->title,
                'description'  => $asset->metadata->description,
                'tags'         => $asset->metadata->tags,
                'ai_generated' => $asset->metadata->ai_generated,
            ] : null,
            'categories' => $asset->categories->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
            ]),
            'created_at' => $asset->created_at->toISOString(),
        ];
    }
}