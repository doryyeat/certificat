<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Services\Notification\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCertificateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Certificate $certificate;
    protected string $email;

    public function __construct(Certificate $certificate, string $email)
    {
        $this->certificate = $certificate;
        $this->email = $email;
    }

    public function handle(EmailService $emailService): void
    {
        $emailService->sendCertificate($this->certificate, $this->email);
    }
}
