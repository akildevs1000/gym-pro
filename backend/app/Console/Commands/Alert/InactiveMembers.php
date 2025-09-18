<?php
namespace App\Console\Commands\Alert;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\AttendanceLog;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InactiveMembers extends Command
{
    protected $signature   = 'alert:inactive-members';
    protected $description = 'Alert all members who were inactive (no attendance logs) in the last 7 days.';

    public function handle()
    {
        $log = Log::channel('inactive-members');
        $log->info("---- Inactive Members ----");

        $sevenDaysAgo  = Carbon::now()->subDays(7);
        $members       = Member::all();
        $inactiveCount = 0;

        foreach ($members as $member) {
            $hasLogs = AttendanceLog::where('member_id', $member->id)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->exists();

            if (! $hasLogs) {
                $logMessage = "Member ID {$member->id} - {$member->name} inactive since {$sevenDaysAgo->toDateString()}.";
                $log->info($logMessage);
                $inactiveCount++;

                if (! empty($member->whatsapp_number)) {
                    SendWhatsappMessageJob::dispatch($member->whatsapp_number, $this->prepareMessage($member->name));
                    $log->info("WhatsApp message queued for {$member->whatsapp_number}");
                } else {
                    $log->info("No WhatsApp number for member ID {$member->id}");
                }
            }
        }

        $log->info("Total Inactive Members: {$inactiveCount}");
        $log->info("Inactive members logged and notified successfully.");
        $log->info("---- End Log ----\n");

    }

    public function prepareMessage($name)
    {
        return "Hello $name, we noticed you have no attendance logs in the past 7 days. \n\n Regards, \n Gym Pro";
    }
}
