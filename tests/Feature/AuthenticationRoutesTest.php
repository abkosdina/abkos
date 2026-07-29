<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticationRoutesTest extends TestCase
{
    public function test_authentication_web_routes_are_available(): void
    {
        $this->get('/users/login')->assertOk();
        $this->get('/users/register')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_authentication_views_render_their_content(): void
    {
        $this->get('/login')->assertSee('ورود به مسترام');
        $this->get('/register')->assertSee('نام و نام خانوادگی');
    }
}
