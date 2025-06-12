<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery as m;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_to_line_provider()
    {
        // Mock the Socialite driver
        Socialite::shouldReceive('driver')
            ->with('line-login')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('with')
            ->with([
                'prompt' => 'consent',
                'bot_prompt' => 'normal',
            ])
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://access.line.me/oauth2/v2.1/authorize'));

        $response = $this->get('/login');

        $response->assertStatus(302);
    }

    public function test_successful_callback_creates_new_user()
    {
        // Mock Socialite user
        $socialiteUser = m::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('U1234567890abcdef1234567890abcdef');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://profile.line-scdn.net/test-avatar.jpg');
        $socialiteUser->shouldReceive('getAttribute')->with('token')->andReturn('access_token_123');
        $socialiteUser->shouldReceive('getAttribute')->with('refreshToken')->andReturn('refresh_token_123');

        // Set up properties directly for easier access
        $socialiteUser->id = 'U1234567890abcdef1234567890abcdef';
        $socialiteUser->avatar = 'https://profile.line-scdn.net/test-avatar.jpg';
        $socialiteUser->token = 'access_token_123';
        $socialiteUser->refreshToken = 'refresh_token_123';

        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with('line-login')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        $this->assertDatabaseMissing('users', [
            'line_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $response = $this->get('/callback?code=test_authorization_code');

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'line_id' => 'U1234567890abcdef1234567890abcdef',
            'name' => 'User',
            'avatar' => 'https://profile.line-scdn.net/test-avatar.jpg',
            'access_token' => 'access_token_123',
            'refresh_token' => 'refresh_token_123',
        ]);
    }

    public function test_successful_callback_updates_existing_user()
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'line_id' => 'U1234567890abcdef1234567890abcdef',
            'name' => 'Old User',
            'avatar' => 'https://old-avatar.jpg',
            'access_token' => 'old_access_token',
            'refresh_token' => 'old_refresh_token',
        ]);

        // Mock Socialite user with updated data
        $socialiteUser = m::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('U1234567890abcdef1234567890abcdef');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://new-avatar.jpg');
        $socialiteUser->shouldReceive('getAttribute')->with('token')->andReturn('new_access_token');
        $socialiteUser->shouldReceive('getAttribute')->with('refreshToken')->andReturn('new_refresh_token');

        // Set up properties directly
        $socialiteUser->id = 'U1234567890abcdef1234567890abcdef';
        $socialiteUser->avatar = 'https://new-avatar.jpg';
        $socialiteUser->token = 'new_access_token';
        $socialiteUser->refreshToken = 'new_refresh_token';

        // Mock Socialite driver
        Socialite::shouldReceive('driver')
            ->with('line-login')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);

        $response = $this->get('/callback?code=test_authorization_code');

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        // User should be updated, not created
        $this->assertDatabaseCount('users', 1);

        $this->assertDatabaseHas('users', [
            'line_id' => 'U1234567890abcdef1234567890abcdef',
            'name' => 'User', // Updated to 'User' as per LoginController logic
            'avatar' => 'https://new-avatar.jpg',
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ]);
    }

    public function test_callback_handles_missing_code_parameter()
    {
        $response = $this->get('/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHas('error', 'Authorization failed. Please try logging in again.');
    }

    public function test_callback_handles_socialite_exception()
    {
        // Mock Socialite to throw an exception
        Socialite::shouldReceive('driver')
            ->with('line-login')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andThrow(new \Exception('Provider error'));

        $response = $this->get('/callback?code=test_authorization_code');

        // The application should handle the exception gracefully
        // Since no explicit exception handling is in the controller,
        // this would result in a 500 error in practice
        $response->assertStatus(500);
    }

    public function test_logout_clears_authentication()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_route_is_accessible()
    {
        Socialite::shouldReceive('driver')
            ->with('line-login')
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('with')
            ->with([
                'prompt' => 'consent',
                'bot_prompt' => 'normal',
            ])
            ->once()
            ->andReturnSelf();

        Socialite::shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://access.line.me/oauth2/v2.1/authorize'));

        $response = $this->get('/login');

        $this->assertTrue($response->isRedirection());
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
