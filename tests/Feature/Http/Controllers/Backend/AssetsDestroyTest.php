<?php

namespace Tests\Feature\Http\Controllers\Backend;

use App\Models\Asset;
use Facades\Tests\Setup\ClipFactory;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetsDestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private string $role = '';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('videos');
        Storage::fake('thumbnails');

        $this->role = 'moderator';
    }

    /** @test */
    public function a_moderator_cannot_delete_a_not_owned_clip_asset(): void
    {
        $asset = Asset::factory()->create();

        $this->signInRole($this->role);

        $this->delete($asset->path())->assertForbidden();
    }

    /** @test */
    public function a_moderator_can_delete_an_owned_clip_asset(): void
    {
        $clip = ClipFactory::withAssets(1)
            ->ownedBy($this->signInRole($this->role))
            ->create();

        $this->assertEquals(1, $clip->assets()->count());

        $this->delete(route('assets.destroy', $clip->assets->first()))
            ->assertRedirect($clip->adminPath());

        $this->assertEquals(0, $clip->assets()->count());
    }

    /** @test */
    public function deleting_an_asset_should_also_delete_the_file_from_storage(): void
    {
        $clip = ClipFactory::ownedBy($this->signInRole($this->role))->create();

        $this->post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);

        $asset = $clip->assets()->first();

        $this->assertDatabaseHas('assets', ['path' => $asset->path]);

        Storage::disk('videos')->assertExists($asset->path);

        $asset->delete();

        $this->assertModelMissing($asset);

        Storage::disk('videos')->assertMissing($asset->path);
    }

    /** @test */
    public function an_admin_can_delete_a_not_owned_clip_asset(): void
    {
        $asset = Asset::factory()->create();

        $this->signInRole('admin');

        $this->delete($asset->path());

        $this->assertModelMissing($asset);
    }

    /** @test */
    public function deleting_an_asset_should_delete_a_clip_poster(): void
    {
        $clip = ClipFactory::ownedBy($this->signInRole($this->role))->create();

        $this->post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);

        $clip->refresh();

        $image = $clip->posterImage;

        $this->delete($clip->assets()->first()->path());

        Storage::disk('thumbnails')->assertMissing($image);
    }

    /** @test */
    public function deleting_an_asset_should_update_clip_poster_image_column(): void
    {
        $clip = ClipFactory::ownedBy($this->signInRole($this->role))->create();

        $this->post(route('admin.clips.asset.transferSingle', $clip), ['asset' => FileFactory::videoFile()]);

        $this->delete($clip->assets()->first()->path());

        $clip->refresh();

        $this->assertNull($clip->posterImage);
    }
}