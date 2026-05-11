<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Интеграционные HTTP-тесты без БД и без RefreshDatabase (доступны при отсутствии pdo_sqlite).
 */
class PublicWebRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_health_endpoint_returns_success(): void
    {
        $response = $this->get('/up');

        $response->assertSuccessful();
    }

    public function test_home_page_returns_success(): void
    {
        $response = $this->get('/');

        $response->assertSuccessful();
    }

    public function test_login_page_returns_success(): void
    {
        $response = $this->get(route('login'));

        $response->assertSuccessful();
    }

    public function test_business_apply_page_returns_success(): void
    {
        $response = $this->get(route('business.apply'));

        $response->assertSuccessful();
    }

    public function test_business_register_request_rejects_empty_payload(): void
    {
        $response = $this->from(route('business.apply'))->post(route('business.register-request'), []);

        $response->assertSessionHasErrors([
            'name',
            'form_of_own',
            'contact',
            'phone',
            'address',
            'bank_info',
            'unp',
            'email',
            'password',
        ]);
    }
}
