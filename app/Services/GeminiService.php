<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    }

    public function generateAssetMetadata(string $filename, string $mimeType): array
    {
        $prompt = "Analiza este archivo con nombre '{$filename}' y tipo '{$mimeType}'.
        Genera metadatos en formato JSON con exactamente estas claves:
        - title: título descriptivo corto (máximo 60 caracteres)
        - description: descripción útil (máximo 200 caracteres)
        - tags: array de 3 a 5 etiquetas relevantes en español
        Responde SOLO con el JSON, sin explicaciones ni formato markdown.";

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
                Log::error('Gemini API error', ['response' => $response->body()]);
                return $this->defaultMetadata($filename);
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            Log::info('Gemini response', ['text' => $text]);
            $data = json_decode($text, true);

            if (!$data || !isset($data['title'])) {
                return $this->defaultMetadata($filename);
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Gemini Service exception', ['error' => $e->getMessage()]);
            return $this->defaultMetadata($filename);
        }
    }

    private function defaultMetadata(string $filename): array
    {
        return [
            'title'       => pathinfo($filename, PATHINFO_FILENAME),
            'description' => 'Sin descripción generada.',
            'tags'        => [],
        ];
    }
}