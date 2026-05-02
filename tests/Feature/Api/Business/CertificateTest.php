<?php

namespace Tests\Feature\Api\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_certificates_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/api/business/certificates', []);
        $response->assertUnauthorized();
    }

    public function test_business_certificates_split_requires_authentication(): void
    {
        $response = $this->postJson('/api/api/business/certificates/split', []);
        $response->assertUnauthorized();
    }

    public function test_business_certificates_redeem_requires_authentication(): void
    {
        $response = $this->postJson('/api/api/business/certificates/1/redeem', []);
        $response->assertUnauthorized();
    }

    public function test_business_certificates_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/api/business/certificates');
        $response->assertUnauthorized();
    }
}
