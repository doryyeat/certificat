<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;

class ClientTypeControllerTest extends TestCase
{
    public function test_it_sets_client_type_cookie_for_valid_value(): void
    {
        $response = $this->postJson(route('set-client-type'), [
            'clientType' => 'client',
        ]);

        $response
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertCookie('client_type', 'client');
    }

    public function test_it_returns_validation_error_for_invalid_client_type(): void
    {
        $response = $this->postJson(route('set-client-type'), [
            'clientType' => 'invalid',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['clientType']);
    }
}
