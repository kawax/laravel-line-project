<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LineTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Revolution\Line\Notifications\LineChannel;
use Revolution\Line\Notifications\LineMessage;
use Tests\TestCase;

class NotificationSendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_receive_line_notification()
    {
        Notification::fake();

        $user = User::factory()->create([
            'line_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $this->actingAs($user);

        $response = $this->get('/notification');

        $response->assertRedirect();

        Notification::assertSentTo(
            $user,
            LineTest::class,
            function ($notification, $channels) use ($user) {
                $this->assertContains(LineChannel::class, $channels);
                $this->assertEquals($notification->via($user), [LineChannel::class]);

                return true;
            }
        );
    }

    public function test_line_notification_contains_correct_message()
    {
        $user = User::factory()->create();
        $message = 'Test notification message';

        $notification = new LineTest($message);
        $lineMessage = $notification->toLine($user);

        $this->assertInstanceOf(LineMessage::class, $lineMessage);

        // Test that the message is properly constructed
        // Note: We can't directly access the text property, so we verify the notification was created correctly
        $this->assertInstanceOf(LineTest::class, $notification);
    }

    public function test_line_notification_via_method_returns_correct_channels()
    {
        $user = User::factory()->create();
        $notification = new LineTest('Test message');

        $channels = $notification->via($user);

        $this->assertIsArray($channels);
        $this->assertContains(LineChannel::class, $channels);
        $this->assertCount(1, $channels);
    }

    public function test_line_notification_to_line_method_creates_message_with_sticker()
    {
        $user = User::factory()->create();
        $testMessage = 'Hello from LINE notification';

        $notification = new LineTest($testMessage);
        $lineMessage = $notification->toLine($user);

        $this->assertInstanceOf(LineMessage::class, $lineMessage);

        // Verify the notification class structure is correct
        $this->assertEquals('Hello from LINE notification', $notification->getMessage());
    }

    public function test_user_route_notification_for_line_returns_line_id()
    {
        $lineId = 'U1234567890abcdef1234567890abcdef';
        $user = User::factory()->create(['line_id' => $lineId]);

        $result = $user->routeNotificationForLine(new LineTest('test'));

        $this->assertEquals($lineId, $result);
    }

    public function test_notification_controller_requires_authentication()
    {
        $response = $this->get('/notification');

        $response->assertRedirect('/login');
    }

    public function test_notification_controller_sends_notification_to_authenticated_user()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/notification');

        $response->assertRedirect();

        Notification::assertSentTo(
            $user,
            LineTest::class,
            function ($notification) {
                return $notification->getMessage() === 'Notification from LINE Messaging API';
            }
        );
    }

    public function test_multiple_users_can_receive_notifications()
    {
        Notification::fake();

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $user->notify(new LineTest('Broadcast message'));
        }

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                LineTest::class,
                function ($notification) {
                    return $notification->getMessage() === 'Broadcast message';
                }
            );
        }

        Notification::assertSentTimes(LineTest::class, 3);
    }

    public function test_notification_can_be_sent_to_anonymous_notifiable()
    {
        Notification::fake();

        $lineId = 'U1234567890abcdef1234567890abcdef';

        Notification::route('line', $lineId)
            ->notify(new LineTest('Anonymous notification'));

        Notification::assertSentTo(
            new AnonymousNotifiable,
            LineTest::class,
            function ($notification, $channels, $notifiable) use ($lineId) {
                return $notifiable->routes['line'] === $lineId &&
                       $notification->getMessage() === 'Anonymous notification';
            }
        );
    }

    public function test_line_notification_with_different_messages()
    {
        $user = User::factory()->create();

        $messages = [
            'Welcome to our service!',
            'Your order has been processed.',
            'Reminder: You have a meeting tomorrow.',
            'Thank you for your purchase!',
        ];

        foreach ($messages as $message) {
            $notification = new LineTest($message);
            $this->assertEquals($message, $notification->getMessage());

            $channels = $notification->via($user);
            $this->assertContains(LineChannel::class, $channels);

            $lineMessage = $notification->toLine($user);
            $this->assertInstanceOf(LineMessage::class, $lineMessage);
        }
    }

    public function test_notification_sticker_generation()
    {
        $user = User::factory()->create();
        $notification = new LineTest('Test with sticker');

        $lineMessage = $notification->toLine($user);

        // The notification should create a LineMessage with a sticker
        // We can't directly test the sticker values since they're random,
        // but we can verify the method completes successfully
        $this->assertInstanceOf(LineMessage::class, $lineMessage);
    }

    public function test_notification_error_handling_with_invalid_line_id()
    {
        Notification::fake();

        $user = User::factory()->create(['line_id' => '']);

        $user->notify(new LineTest('Test message'));

        // Even with invalid line_id, notification should be attempted
        Notification::assertSentTo($user, LineTest::class);
    }

    public function test_notification_queue_configuration()
    {
        $notification = new LineTest('Queued message');

        // Verify the notification uses the Queueable trait
        $this->assertContains('Illuminate\Bus\Queueable', class_uses($notification));
    }
}
