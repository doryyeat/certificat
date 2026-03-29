<?php

namespace App\Events;

use App\Models\Certificate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificatePurchased implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Certificate $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('business.' . $this->certificate->business_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'certificate.purchased';
    }

    public function broadcastWith(): array
    {
        return [
            'certificate_id' => $this->certificate->id,
            'nominal' => $this->certificate->nominal,
            'purchased_at' => $this->certificate->purchased_at,
        ];
    }
}
