<?php

use App\Enums\Content;
use App\Models\Asset;
use App\Models\Clip;
use App\Models\Semester;
use App\Models\Series;
use App\Models\User;
use Facades\Tests\Setup\FileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\get;

uses()->group('unit');

beforeEach(function () {
    $this->clip = Clip::factory()->create();
    Storage::fake('thumbnails');
});

it('has a path', function () {
    expect($this->clip->path())->toBe('/clips/'.$this->clip->slug);
});

it('has an admin path', function () {
    expect($this->clip->adminPath())->toBe('/admin/clips/'.$this->clip->slug);
});

it('has a slug route', function () {
    expect($this->clip->path())->toBe('/clips/'.Str::slug(
        $this->clip->episode.'-'.$this->clip->title.'-'.Semester::current()->get()->first()->acronym
    ));
});

it('has an inceremental slug', function () {
    $anotherClip = Clip::factory()->create(['title' => $this->clip->title, 'episode' => $this->clip->episode]);

    expect($anotherClip->slug)->toBe($this->clip->slug.'-2');
});

it('has a unique slug', function () {
    $clipA = Clip::factory()->create(['title' => 'A test title', 'slug' => 'A test title']);
    $clipB = Clip::factory()->create(['title' => 'A test title', 'slug' => 'A test title']);

    expect($clipB->slug)->not()->toBe($clipA->slug);
});

it('belongs to a series', function () {
    expect($this->clip->series())->toBeInstanceOf(BelongsTo::class);
});

it('has many assets', function () {
    Asset::factory(2)->create(['clip_id' => $this->clip->id]);

    expect($this->clip->assets()->count())->toBe(2);
});

it('has many collections', function () {
    expect($this->clip->collections())->toBeInstanceOf(BelongsToMany::class);
});

it('has only one semester', function () {
    expect($this->clip->semester())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to an organization unit', function () {
    expect($this->clip->organization())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to an image', function () {
    expect($this->clip->image())->toBeInstanceOf(BelongsTo::class);
});

it('has one language', function () {
    expect($this->clip->language())->toBeInstanceOf(BelongsTo::class);
});

it('has one context', function () {
    expect($this->clip->context())->toBeInstanceOf(BelongsTo::class);
});

it('has one format', function () {
    expect($this->clip->format())->toBeInstanceOf(BelongsTo::class);
});

it('has one type', function () {
    expect($this->clip->type())->toBeInstanceOf(BelongsTo::class);
});

it('belongs to an owner', function () {
    expect($this->clip->owner)->toBeInstanceOf(User::class);
});

it('has many presenters using presentable trait', function () {
    expect($this->clip->presenters())->toBeInstanceOf(MorphToMany::class);
});

it('has many documents using documentable trait', function () {
    expect($this->clip->documents())->toBeInstanceOf(MorphToMany::class);
});

it('has many comments', function () {
    expect($this->clip->comments())->toBeInstanceOf(MorphMany::class);
});

it('can add an asset', function () {
    $asset = $this->clip->addAsset([
        'disk' => 'videos',
        'original_file_name' => 'video.mp4',
        'path' => '/videos/',
        'duration' => '100',
        'guid' => Str::uuid(),
        'width' => '1920',
        'height' => '1080',
        'type' => 'video',
    ]);

    expect($asset)->toBeInstanceOf(Asset::class);
});

it('can update it\'s poster image', function () {
    expect($this->clip->posterImage)->toBeNull();

    $file = FileFactory::videoFile();
    $file->storeAs('thumbnails', $this->clip->id.'_poster.png');
    $this->clip->updatePosterImage();

    Storage::assertExists('/thumbnails/'.$this->clip->posterImage);
});

it('can add tags', function () {
    $this->clip->addTags(collect(['php', 'tides']));

    expect($this->clip->tags()->count())->toBe(2);
});

it('can fetch previous and nect clip models if clip belongs to a series', function () {
    $series = Series::factory()->create();
    Clip::factory()->create([
        'title' => 'first clip',
        'series_id' => $series->id,
        'episode' => 1,
    ]);
    $secondClip = Clip::factory()->create([
        'title' => 'second clip',
        'series_id' => $series->id,
        'episode' => 2,
    ]);
    Clip::factory()->create([
        'title' => 'third clip',
        'series_id' => $series->id,
        'episode' => 3,
    ]);

    expect($secondClip->previousNextClipCollection())->toBeInstanceOf(Collection::class);
    expect($secondClip->previousNextClipCollection()->get('previousClip'))->toBeInstanceOf(Clip::class);
    expect($secondClip->previousNextClipCollection()->get('nextClip'))->toBeInstanceOf(Clip::class);
});

it('can return the closed captions asset', function () {
    expect($this->clip->getCaptionAsset())->toBeNull();
});

it('can return the total asset views', function () {
    expect($this->clip->views())->toBe(0);
});

it('has a public scope', function () {
    expect(Clip::public())->toBeInstanceOf(Builder::class);
});

it('has a single clip scope', function () {
    expect(Clip::single())->toBeInstanceOf(Builder::class);
});

test('clip owner can be null', function () {
    $user = User::factory()->create();
    $clip = $user->clips()->create(['title' => 'test', 'slug' => 'test', 'semester_id' => 1]);
    $user->delete();
    $clip = Clip::find($clip->id);

    expect($clip->owner_id)->toBeNull();
});

it('resolves also an id in route', function () {
    get('clips/'.$this->clip->id)->assertForbidden();
    get(route('frontend.clips.show', $this->clip->id))->assertStatus(403);
    get('clips/291')->assertStatus(404);
});

it('fetches assets by type', function () {
    expect($this->clip->getAssetsByType(Content::PRESENTER))->toBeInstanceOf(HasMany::class);
});

it('creates a folder id after model save', function () {
    //second db update will be done in clip observer class
    expect($this->clip->folder_id)->toEqual('TIDES_ClipID_1');
});

it('has a method for returning caption assets', function () {
    expect($this->clip->getCaptionAsset())->toBeNull();
});
