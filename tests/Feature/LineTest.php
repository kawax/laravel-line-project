<?php

namespace Tests\Feature;

use App\Listeners\Message\TextMessageListener;
use App\Notifications\LineNotifyTest;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Response;
use Mockery as m;
use Revolution\Line\Messaging\Bot;
use Tests\TestCase;

class LineTest extends TestCase
{
    public function testTextMessageListener()
    {
        $event = m::mock(TextMessage::class);
        $event->shouldReceive('getReplyToken')
            ->once()
            ->andReturn('tokens');
        $event->shouldReceive('getText')
            ->once()
            ->andReturn('text');

        $response = m::mock(Response::class);
        $response->shouldReceive('isSucceeded')
            ->once()
            ->andReturnFalse();
        $response->shouldReceive('getHTTPStatus')
            ->once()
            ->andReturn(400);
        $response->shouldReceive('getJSONDecodedBody')
            ->once()
            ->andReturn([]);

        Bot::shouldReceive('reply->withSender->text')
            ->once()
            ->andReturn($response);

        Log::shouldReceive('error')->once();

        Notification::fake();

        $listener = new TextMessageListener();
        $listener->handle($event);

        Notification::assertSentTo(
            new AnonymousNotifiable, LineNotifyTest::class
        );
    }
}
