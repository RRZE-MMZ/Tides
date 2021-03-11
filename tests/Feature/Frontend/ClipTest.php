<?php

namespace Tests\Feature\Frontend;

use App\Models\Clip;
use Facades\Tests\Setup\ClipFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_visitor_can_view_a_clip()
    {
        $clip = Clip::factory()->create();

        $this->get($clip->path())->assertSee($clip->title);
    }

    /** @test */
    public function an_authorized_user_cannot_view_edit_button_for_not_owned_clip()
    {
        $clip = ClipFactory::create();

        $this->signIn();

        $this->get($clip->path())->assertDontSee('Back to edit page');
    }
}