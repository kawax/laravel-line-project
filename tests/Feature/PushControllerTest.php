<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Revolution\Line\Facades\Bot;
use Tests\TestCase;

class PushControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_requires_authentication()
    {
        $response = $this->get('/push');

        $response->assertRedirect('/login');
    }

    public function test_push_sends_message_to_authenticated_user()
    {
        // Mock the Bot facade
        Bot::shouldReceive('pushMessage')
            ->once()
            ->with(\Mockery::on(function ($pushRequest) {
                // Verify the push request has the correct structure
                return $pushRequest->getTo() === 'U1234567890abcdef1234567890abcdef' &&
                       count($pushRequest->getMessages()) === 1 &&
                       $pushRequest->getMessages()[0]->getText() === 'PushMessage test';
            }));

        // Create a user with a valid line_id
        $user = User::factory()->create([
            'line_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $this->actingAs($user);

        $response = $this->get('/push');

        $response->assertRedirect();
    }
}
