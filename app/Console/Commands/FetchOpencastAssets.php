<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Transferable;
use App\Jobs\CreateWowzaSmilFile;
use App\Jobs\TransferAssetsJob;
use App\Mail\AssetsTransferred;
use App\Models\Clip;
use App\Services\OpencastService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FetchOpencastAssets extends Command
{
    use Transferable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opencast:finished-events';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch opencast assets for empty clips';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param OpencastService $opencastService
     * @return int
     */
    public function handle(OpencastService $opencastService): int
    {
        //fetch all clips without video files
        $emptyClips = Clip::doesntHave('assets')->get();

        /*
         * for each empty clip check if there are finished opencast events
         * and publish the video files
         */
        $emptyClips->each(function ($clip) use ($opencastService) {

            //find finished workflows for every clip
            $events = $opencastService->getEventsBySeriesID($clip->series->opencast_series_id);

            $events->each(function ($event) use ($clip, $opencastService) {
                if ($clip->created_at->format('Y-m-d') === Carbon::parse($event['created'])->format('Y-m-d')) {
                    $this->checkOpencastAssetsForClipUpload($clip, $event['identifier'], $opencastService);

                    $this->info('Videos from Clip ' . $clip->title . ' is online');
                }
            });
        });

        return 0;
    }
}