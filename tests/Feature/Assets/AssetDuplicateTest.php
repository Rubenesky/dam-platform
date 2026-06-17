<?php

namespace Tests\Feature\Assets;

use App\Jobs\ProcessAssetAI;
use App\Models\Asset;
use App\Models\AssetMetadata;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetDuplicateTest extends TestCase
{
    use RefreshDatabase;

    private function mockGemini(): void
    {
        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('generateAssetMetadata')->andReturn([
                'title' => 'Paisaje montañoso',
                'description' => 'Foto de montañas nevadas al atardecer',
                'tags' => ['montaña', 'nieve', 'atardecer'],
            ]);
        });
    }

    public function test_cuando_hay_similares_se_persisten_en_similar_assets(): void
    {
        $similares = [
            ['id' => 99, 'similarity' => 85, 'reason' => 'Mismo tipo de paisaje montañoso'],
        ];

        $this->mock(DuplicateDetectionService::class, function ($mock) use ($similares) {
            $mock->shouldReceive('findSimilar')->once()->andReturn($similares);
        });

        $this->mockGemini();

        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        (new ProcessAssetAI($asset->id))->handle();

        $metadata = AssetMetadata::where('asset_id', $asset->id)->first();
        $this->assertNotNull($metadata->similar_assets);
        $this->assertCount(1, $metadata->similar_assets);
        $this->assertEquals(99, $metadata->similar_assets[0]['id']);
        $this->assertEquals(85, $metadata->similar_assets[0]['similarity']);
    }

    public function test_cuando_no_hay_similares_similar_assets_queda_null(): void
    {
        $this->mock(DuplicateDetectionService::class, function ($mock) {
            $mock->shouldReceive('findSimilar')->once()->andReturn([]);
        });

        $this->mockGemini();

        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        (new ProcessAssetAI($asset->id))->handle();

        $metadata = AssetMetadata::where('asset_id', $asset->id)->first();
        $this->assertNull($metadata->similar_assets);
    }
}
