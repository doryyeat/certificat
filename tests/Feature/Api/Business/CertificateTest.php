<?php

namespace Tests\Feature\Api\Business;

use App\Models\GiftCertificate;
use Tests\TestCase;
use App\Models\Business;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->business = Business::factory()->create([
            'user_id' => $this->user->id,
            'subscription' => 'pro',
            'verified_at' => now(),
        ]);
    }

    /** @test */
    public function business_can_create_certificate()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/business/certificates', [
                'segment_id' => 1,
                'location_id' => 1,
                'nominal' => 5000,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'nominal',
                    'balance',
                    'qr_code',
                    'pdf',
                ]
            ]);

        $this->assertDatabaseHas('certificates', [
            'business_id' => $this->business->id,
            'nominal' => 5000,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function business_cannot_create_certificate_when_limit_reached()
    {
        // Создаем максимальное количество сертификатов для бесплатного плана
        $business = Business::factory()->create([
            'user_id' => User::factory()->create()->id,
            'subscription' => 'free',
        ]);

        GiftCertificate::factory()->count(50)->create([
            'business_id' => $business->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($business->user)
            ->postJson('/api/business/certificates', [
                'segment_id' => 1,
                'location_id' => 1,
                'nominal' => 5000,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Certificate limit reached',
            ]);
    }

    /** @test */
    public function business_can_split_certificate()
    {
        $certificate = GiftCertificate::factory()->create([
            'business_id' => $this->business->id,
            'nominal' => 10000,
            'balance' => 10000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/business/certificates/split", [
                'certificate_id' => $certificate->id,
                'amount' => 5000,
                'recipients' => [
                    ['email' => 'user1@example.com', 'name' => 'User 1'],
                    ['email' => 'user2@example.com'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'parent',
                    'splits',
                ]
            ]);

        $this->assertDatabaseHas('certificate_splits', [
            'parent_id' => $certificate->id,
            'recipient_email' => 'user1@example.com',
        ]);
    }

    /** @test */
    public function business_can_redeem_certificate()
    {
        $certificate = GiftCertificate::factory()->create([
            'business_id' => $this->business->id,
            'nominal' => 5000,
            'balance' => 5000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/business/certificates/{$certificate->id}/redeem", [
                'qr_data' => json_encode(['id' => $certificate->id]),
                'amount' => 1000,
                'location_id' => 1,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'certificate_id' => $certificate->id,
                    'amount' => 1000,
                ]
            ]);

        $this->assertDatabaseHas('certificate_redemptions', [
            'certificate_id' => $certificate->id,
            'amount' => 1000,
        ]);

        $this->assertEquals(4000, $certificate->fresh()->balance);
    }
}
