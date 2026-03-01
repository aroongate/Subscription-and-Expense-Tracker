<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_be_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post('/logout');

        $logoutResponse->assertRedirect('/login');
        $this->assertGuest();
    }
}
