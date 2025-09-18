<?php
namespace App\Console\Commands\Alert;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BirthDayWish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:birthday-wish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send birthday wishes to members with WhatsApp numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $log = Log::channel('birthday');

        $log->info("***** Cron started for birthday:wish *****");

        $today = Carbon::now()->format('m-d');

        $members = Member::whereNotNull('whatsapp_number')
            ->where('whatsapp_number', 'like', '971%')
            ->whereRaw("strftime('%m-%d', date_of_birth) = ?", [$today])
            ->get(['first_name', 'whatsapp_number']);

        if ($members->isEmpty()) {
            $this->info('No birthdays today.');
            $log->info('No birthdays today.');
            $log->info("***** Cron ended for birthday:wish *****");
            return;
        }

        foreach ($members as $employee) {

            $message = $this->prepareMessage($employee->first_name);

            SendWhatsappMessageJob::dispatch($employee->whatsapp_number, $message);

            $log->info("Queued for {$employee->whatsapp_number}: $message");

            $this->info("Queued for {$employee->whatsapp_number}");
        }

        $log->info("***** Cron ended for birthday:wish *****");
    }

    public function prepareMessage($name)
    {
        return "🎉 Happy Birthday, $name! 🎂\n\n"
            . "Wishing you a day filled with happiness, laughter, and all the things you love the most!\n"
            . "May this year bring you success, good health, and countless joyful moments.\n\n"
            . "Enjoy your special day! 🥳\n\n"
            . "Regards, Mytime2Cloud";
    }
}
