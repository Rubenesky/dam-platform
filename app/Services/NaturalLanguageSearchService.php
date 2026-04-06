<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NaturalLanguageSearchService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    public function parseQuery(string $userQuery): array
    {
        $prompt = "Eres un asistente que convierte búsquedas en lenguaje natural a filtros estructurados para una plataforma de gestión de activos digitales.

Los filtros disponibles son:
- search: texto para buscar en el nombre o título del archivo
- type: tipo de archivo ('image' para imágenes, 'application/pdf' para PDFs, 'video' para vídeos)
- status: estado del archivo ('processed' o 'pending')
- date_from: fecha desde (formato Y-m-d)
- date_to: fecha hasta (formato Y-m-d)

Hoy es " . now()->format('Y-m-d') . ".

El usuario busca: \"{$userQuery}\"

Responde SOLO con un JSON con los filtros aplicables. Si un filtro no aplica no lo incluyas.
Ejemplo: {\"type\": \"image\", \"search\": \"paisaje\"}
Solo JSON, sin explicaciones ni markdown.";

        try {
            $response = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('NL Search error', ['response' => $response->body()]);
                return [];
            }

            $text  = $response->json('candidates.0.content.parts.0.text');
            $clean = preg_replace('/```json|```/', '', $text);
            $data  = json_decode(trim($clean), true);

            Log::info('NL Search parsed', ['query' => $userQuery, 'filters' => $data]);

            return $data ?? [];

        } catch (\Exception $e) {
            Log::error('NL Search exception', ['error' => $e->getMessage()]);
            return [];
        }
    }
}