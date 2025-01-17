<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Logable;
use App\Events\UserExpired;
use App\Mail\ExpiredUsersFound;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class CheckForExpiredUsers extends Command
{
    use Logable;
    use Notifiable;

    protected $signature = 'app:check-for-expired-users';

    protected $description = 'Checks for expired users, fires up and event, and notifies portal admins';

    public function handle(): int
    {
        $this->commandLog(message: 'Check for expired users');
        $expiredUsers = User::expired();

        if ($expiredUsers->count() == 0) {
            $this->commandLog(message: 'No expired users found');

            return Command::SUCCESS;
        }

        $this->commandLog(message: "{$expiredUsers->count()} expired users found");
        $expiredUsers->each(function (User $user) {
            UserExpired::dispatch($user);
        });

        $portalSettings = Setting::portal();
        $sendAddress = $portalSettings->data['admin_main_address'];
        $this->commandLog(message: "Notifying portal admins [{$sendAddress}] via mail!");
        Mail::to($sendAddress)->send(new ExpiredUsersFound($expiredUsers));

        return Command::SUCCESS;
    }
}
