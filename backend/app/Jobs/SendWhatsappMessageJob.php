<?php
namespace App\Jobs;

use App\Models\WhatsappClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $recipient,
        protected string $message
    ) {}

    public function handle(): void
    {
        try {
            $clientId = $this->getClient();

            if (! $clientId) {
                Log::warning("No WhatsApp client ID available.");
                return;
            }

            $response = Http::withoutVerifying()->post('https://wa.mytime2cloud.com/send-message', [
                'clientId'  => $clientId,
                'recipient' => $this->recipient,
                'text'      => $this->message,
            ]);

            if ($response->successful()) {
                Log::info("Message sent successfully to {$this->recipient}");
            } else {
                Log::error("Failed to send message to {$this->recipient}. Response: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("Exception while sending message to {$this->recipient}: " . $e->getMessage());
        }
    }

    private function getClient(): ?string
    {
        $accounts = WhatsappClient::value('accounts');

        if (is_string($accounts)) {
            $accounts = json_decode($accounts, true);
        }

        if (! is_array($accounts) || empty($accounts[0]['clientId'])) {
            return null;
        }

        return $accounts[0]['clientId'];
    }
}
