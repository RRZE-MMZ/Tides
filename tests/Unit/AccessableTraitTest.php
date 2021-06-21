<?php

namespace Tests\Unit;

use App\Models\Clip;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessableTraitTest extends TestCase
{
  use RefreshDatabase;

  protected Clip $clip;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->clip = Clip::factory()->create();
    }

    /** @test */
    public function it_has_an_acl_method_for_model(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->clip->acls());
    }

    /** @test */
    public function it_can_add_acls_to_model(): void
    {
        $this->clip->addAcls(collect(['1','2']));

        $this->assertEquals(2, $this->clip->acls()->count());
    }
}