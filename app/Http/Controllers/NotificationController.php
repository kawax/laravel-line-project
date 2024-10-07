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
