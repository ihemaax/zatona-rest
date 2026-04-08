<?php

namespace App\Jobs;

use App\Services\WapilotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappTextJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 4;
    public int $timeout = 30;
    public int $backoff = 20;

    public function __construct(
        public string $phone,
        public string $text
    ) {
        $this->onQueue('notifications');
    }

    public function handle(WapilotService $wapilot): void
    {
        $wapilot->sendTextToPhone($this->phone, $this->text);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('whatsapp.job.failed', [
            'phone' => $this->phone,
            'message' => $exception->getMessage(),
        ]);
    }
}
