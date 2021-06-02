<?php

namespace Tests\Feature\Backend;

use App\Http\Livewire\IngestOpencast;
use App\Jobs\IngestVideoFileToOpencast;
use App\Models\Clip;
use App\Services\OpencastService;
use Facades\Tests\Setup\ClipFactory;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Setup\WorksWithOpencastClient;
use Tests\TestCase;

class IngestOpencastTest extends TestCase
{
    use RefreshDatabase, WorksWithOpencastClient;

    private OpencastService $opencastService;
    private MockHandler $mockHandler;
    private Clip $clip;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        app()->setLocale('en');

        $this->clip = ClipFactory::ownedBy($this->signIn())->create();

        $this->mockHandler = $this->swapOpencastClient();

        $this->opencastService = app(OpencastService::class);

        $this->mockHandler->append($this->mockHealthResponse());

    }
    /** @test */
    public function it_contains_a_file_upload_livewire_component_in_clip_edit_page(): void
    {
        $this->get(route('clips.edit', $this->clip))
            ->assertSeeLivewire('ingest-opencast');
    }

    /** @test */
    public function it_dispatches_an_ingest_job_after_video_upload(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('video.mp4', 1000);

        Livewire::test(IngestOpencast::class,['clip'=>$this->clip])
            ->set('videoFile',$file)
            ->call('submitForm');

        Queue::assertPushed(IngestVideoFileToOpencast::class);
    }

    /** @test */
    public function it_dont_dispatches_an_ingest_job_when_file_is_not_a_video(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('video.pdf', 1000);

        Livewire::test(IngestOpencast::class,['clip'=>$this->clip])
            ->set('videoFile',$file)
            ->call('submitForm')
            ->assertSee('The video file field is required.');

        Queue::assertNotPushed(IngestVideoFileToOpencast::class);
    }

    /** @test */
    public function it_dont_dispatches_an_ingest_job_when_file_is_bigger_that_two_gigabytes(): void
    {
        Queue::fake();

        $file = UploadedFile::fake()->create('video.mp4', 9097152);

        Livewire::test(IngestOpencast::class,['clip'=>$this->clip])
            ->set('videoFile',$file)
            ->call('submitForm')
            ->assertSee('The video file field is required.');

        Queue::assertNotPushed(IngestVideoFileToOpencast::class);
    }
}