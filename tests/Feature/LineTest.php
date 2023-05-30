<?php

namespace Tests\Feature;

use App\Listeners\Line\MessageListener;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\StickerMessageContent;
use LINE\Webhook\Model\TextMessageContent;
use Mockery as m;
use Revolution\Line\Messaging\Bot;
use Tests\TestCase;

class LineTest extends TestCase
{
    public function testTextMessageListener()
    {
        $event = m::mock(MessageEvent::class);
        $event->shouldReceive('getReplyToken')
            ->once()
            ->andReturn('tokens');
        $event->shouldReceive('getMessage')
            ->once()
            ->andReturn(new TextMessageContent([
                'text' => 'test',
            ]));

        Bot::shouldReceive('reply->text')
            ->once();

        $listener = new MessageListener();
        $listener->handle($event);
    }

    public function testStickerMessageListener()
    {
        $event = m::mock(MessageEvent::class);
        $event->shouldReceive('getReplyToken')
            ->once()
            ->andReturn('tokens');
        $event->shouldReceive('getMessage')
            ->once()
            ->andReturn(new StickerMessageContent([
                'packageId' => 1,
                'stickerId' => 1,
            ]));

        Bot::shouldReceive('reply->sticker')
            ->once();

        $listener = new MessageListener();
        $listener->handle($event);
    }
}
