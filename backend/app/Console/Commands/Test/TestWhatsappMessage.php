<?php
namespace App\Console\Commands\Test;

use App\Console\Commands\Alert\BirthDayWish;
use App\Console\Commands\Alert\InactiveMembers;
use App\Jobs\SendWhatsappMessageJob;
use App\Templates\Whatsapp\Messages;
use Illuminate\Console\Command;

class TestWhatsappMessage extends Command
{
    // Example: php artisan test:whatsapp 971554501483
    protected $signature   = 'test:whatsapp {whatsapp_number=971554501483}';
    protected $description = 'Send test WhatsApp messages to a specified number using template messages.';

    public function handle()
    {
        $whatsapp = $this->argument('whatsapp_number');

        $this->info("Sending test messages to: {$whatsapp}");

        $tempaltes = [
             ...Messages::TEMPLATES,
            "birth_day_wish"   => (new BirthDayWish)->prepareMessage('John Doe'),
            "inactive_members" => (new InactiveMembers)->prepareMessage('John Doe'),
        ];

        foreach ($tempaltes as $tempalte) {
            SendWhatsappMessageJob::dispatch($whatsapp, $tempalte);
            $this->info("Dispatching job for template: {$tempalte}");
            sleep(1);
        }

        $this->info("Dispatched all WhatsApp message jobs.");

    }
}
