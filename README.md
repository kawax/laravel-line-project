# LINE SDK for Laravel sample project

https://github.com/invokable/laravel-line-sdk

## Sample Code Explanations

This project demonstrates the main features of LINE integration with Laravel applications. The following sections explain the key components with code examples.

### 1. OAuth Authentication with LINE Accounts

The application uses Laravel Socialite to handle LINE OAuth authentication. Users can log in with their LINE accounts and the system stores their profile information.

#### Routes Configuration (`routes/web.php`)
```php
Route::get('login', [LoginController::class, 'login'])->name('login');
Route::get('callback', [LoginController::class, 'callback']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
```

#### Login Controller (`app/Http/Controllers/LoginController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function login()
    {
        return Socialite::driver('line-login')->with([
            'prompt' => 'consent',
            'bot_prompt' => 'normal',
        ])->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->missing('code')) {
            Log::info('Login callback called without code parameter', [
                'request_data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('login')->with('error', 'Authorization failed. Please try logging in again.');
        }

        $user = Socialite::driver('line-login')->user();

        $loginUser = User::updateOrCreate([
            'line_id' => $user->id,
        ], [
            'name' => 'User',
            'avatar' => $user->avatar,
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);

        auth()->login($loginUser, true);

        return to_route('dashboard');
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/');
    }
}
```

The `login()` method redirects users to LINE's OAuth authorization page with specific parameters:
- `prompt => 'consent'`: Forces the consent screen to appear
- `bot_prompt => 'normal'`: Allows users to add the bot as a friend

The `callback()` method handles the OAuth response, creates or updates the user record, and logs them into the application.

### 2. LINE Notifications using Laravel Notifications

Laravel's notification system is used to send messages through LINE. This approach provides a clean, object-oriented way to handle LINE messaging.

#### Notification Controller (`app/Http/Controllers/NotificationController.php`)
```php
<?php

namespace App\Http\Controllers;

use App\Notifications\LineTest;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->user()->notify(new LineTest('Notification from LINE Messaging API'));

        return back();
    }
}
```

#### LINE Notification Class (`app/Notifications/LineTest.php`)
```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Revolution\Line\Notifications\LineChannel;
use Revolution\Line\Notifications\LineMessage;

class LineTest extends Notification
{
    use Queueable;

    public function __construct(protected string $message)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return [
            LineChannel::class,
        ];
    }

    public function toLine(object $notifiable): LineMessage
    {
        return LineMessage::create()
            ->text($this->message)
            ->sticker(446, random_int(1988, 2027));
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
```

The notification system allows you to:
- Send text messages with `->text()`
- Include stickers with `->sticker(packageId, stickerId)`
- Queue notifications for better performance using the `Queueable` trait
- Target specific users through the `routeNotificationForLine()` method in the User model

### 3. Sending Messages with PushMessage

For direct message sending without using the notification system, you can use LINE's PushMessage API directly through the Bot facade.

#### Push Controller (`app/Http/Controllers/PushController.php`)
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LINE\Clients\MessagingApi\Model\PushMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use Revolution\Line\Facades\Bot;

class PushController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $message = new TextMessage(['text' => 'PushMessage test', 'type' => MessageType::TEXT]);

        $push = new PushMessageRequest([
            'to' => $request->user()->line_id,
            'messages' => [$message],
        ]);

        Bot::pushMessage($push);

        return back();
    }
}
```

PushMessage is useful when you need to:
- Send messages immediately without going through the notification system
- Have fine-grained control over message construction
- Send messages to users who haven't interacted with your bot recently

### 4. Receiving Messages from LINE

The webhook functionality for receiving messages from LINE is provided by the `laravel-line-sdk` package. You should customize the `MessageListener.php` file for your specific project needs.

#### Message Listener (`app/Listeners/Line/MessageListener.php`)
```php
<?php

namespace App\Listeners\Line;

use LINE\Clients\MessagingApi\ApiException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\StickerMessageContent;
use LINE\Webhook\Model\TextMessageContent;
use Revolution\Line\Facades\Bot;

class MessageListener
{
    protected string $token;

    public function handle(MessageEvent $event): void
    {
        $message = $event->getMessage();
        $this->token = $event->getReplyToken();

        match ($message::class) {
            TextMessageContent::class => $this->text($message),
            StickerMessageContent::class => $this->sticker($message),
        };
    }

    protected function text(TextMessageContent $message): void
    {
        Bot::reply($this->token)->text($message->getText());
    }

    protected function sticker(StickerMessageContent $message): void
    {
        Bot::reply($this->token)->sticker(
            $message->getPackageId(),
            $message->getStickerId()
        );
    }
}
```

**Important Notes:**
- The webhook endpoint is automatically provided by the `laravel-line-sdk` package
- This sample implementation simply echoes back received messages
- For more complex message processing, consider dispatching a Job from the listener:

```php
public function handle(MessageEvent $event): void
{
    // Dispatch a job for complex processing
    ProcessLineMessage::dispatch($event);
    
    // Send immediate acknowledgment if needed
    Bot::reply($event->getReplyToken())->text('Message received! Processing...');
}
```

This approach prevents webhook timeouts while allowing complex business logic to run in the background.
