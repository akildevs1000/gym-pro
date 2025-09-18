<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Membership;
use Carbon\Carbon;

class ExpireMemberships extends Command
{
    protected $signature = 'memberships:expire-today';
    protected $description = 'Set membership status to 0 if plan_end_date is today';

    public function handle()
    {
        $today = Carbon::today();

        $expired = Membership::whereDate('plan_end_date', $today)
            ->where('status', '!=', 0)
            ->update(['status' => 0]);

        $this->info("Memberships expired today: {$expired}");
    }
}
