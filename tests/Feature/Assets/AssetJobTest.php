<?php

namespace Tests\Feature\Assets;

use App\Jobs\ProcessAssetAI;
use App\Models\Asset;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuando_el_job_falla_el_asset_queda_con_status_error(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $job = new ProcessAssetAI($asset->id);
        $job->failed(new \RuntimeException('Gemini timeout'));

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'error',
        ]);
    }

    public function test_el_status_nunca_queda_como_failed(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $job = new ProcessAssetAI($asset->id);
        $job->failed(new \RuntimeException('Error'));

        $this->assertDatabaseMissing('assets', [
            'id' => $asset->id,
            'status' => 'failed',
        ]);
    }

    public function test_handle_exitoso_deja_el_asset_en_status_processed(): void
    {
        $this->mock(GeminiService::class, function ($mock) {
            $mock->shouldReceive('generateAssetMetadata')->once()->andReturn([
                'title' => 'Test title',
                'description' => 'Test description',
                'tags' => ['tag1', 'tag2'],
            ]);
        });

        $this->mock(DuplicateDetectionService::class, function ($mock) {
            $mock->shouldReceive('findSimilar')->once()->andReturn([]);
        });

        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        (new ProcessAssetAI($asset->id))->handle();

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'status' => 'processed',
        ]);
    }
}
