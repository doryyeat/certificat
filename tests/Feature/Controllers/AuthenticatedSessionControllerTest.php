<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    public function test_login_page_is_available(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }
}
