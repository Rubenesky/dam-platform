<?php

namespace Tests\Feature\Assets;

use App\Models\Asset;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_borrar_asset_con_cloudinary_llama_a_delete_en_cloudinary(): void
    {
        $cloudinary = $this->mock(CloudinaryService::class);
        $cloudinary->shouldReceive('delete')
            ->once()
            ->with('dam-platform/abc123')
            ->andReturn(true);

        $asset = Asset::factory()->create([
            'user_id' => $this->admin->id,
            'path' => 'assets/test.jpg',
            'cloudinary_public_id' => 'dam-platform/abc123',
            'cloudinary_url' => 'https://res.cloudinary.com/test/image/upload/abc123.jpg',
        ]);

        Storage::disk('public')->put('assets/test.jpg', 'fake');

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/assets/{$asset->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_borrar_asset_sin_cloudinary_no_llama_a_delete_en_cloudinary(): void
    {
        $cloudinary = $this->mock(CloudinaryService::class);
        $cloudinary->shouldNotReceive('delete');

        $asset = Asset::factory()->create([
            'user_id' => $this->admin->id,
            'path' => 'assets/test.pdf',
            'cloudinary_public_id' => null,
            'cloudinary_url' => null,
        ]);

        Storage::disk('public')->put('assets/test.pdf', 'fake');

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/assets/{$asset->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_si_cloudinary_falla_el_asset_se_borra_igualmente_de_bd_y_storage(): void
    {
        $cloudinary = $this->mock(CloudinaryService::class);
        $cloudinary->shouldReceive('delete')
            ->once()
            ->andReturn(false);

        $asset = Asset::factory()->create([
            'user_id' => $this->admin->id,
            'path' => 'assets/test.jpg',
            'cloudinary_public_id' => 'dam-platform/abc123',
            'cloudinary_url' => 'https://res.cloudinary.com/test/image/upload/abc123.jpg',
        ]);

        Storage::disk('public')->put('assets/test.jpg', 'fake');

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/assets/{$asset->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing('assets/test.jpg');
    }
}
