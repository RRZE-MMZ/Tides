<?php

namespace Tests\Feature\Http\Controllers\Backend;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsIndexTest extends TestCase
{
    use RefreshDatabase;

    public Setting $setting;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->signInRole('superadmin');

        $this->setting = Setting::create(
            ['name' => 'opencast',
                'data' => [
                    'url' => 'localhost:8080',
                    'username' => 'admin',
                    'password' => 'opencast',
                ],
            ]
        );
    }

    /** @test */
    public function it_denies_access_for_admins(): void
    {
        auth()->logout();

        $this->signInRole('admin');

        $this->get(route('settings.portal.index'))->assertForbidden();
    }

    /** @test */
    public function it_list_settings_for_different_components(): void
    {
        $this->get(route('settings.portal.index'))
            ->assertOk()
            ->assertSee('Portal')
            ->assertSee('Opencast')
            ->assertSee('Streaming')
            ->assertSee('Elasticsearch');
    }
}