<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    You're logged in!
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @empty(auth()->user()->notify_token)
                        <a href="{{ route('notify.login') }}" class="underline">
                            LINE Notify: Login
                        </a>
                    @else
                        <a href="{{ route('notify.send')  }}" class="underline">
                            LINE Notify: Send test message
                        </a>
                    @endempty
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <a href="{{ route('push') }}" class="underline">
                        Push message
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <a href="{{ route('notification') }}" class="underline">
                        Notification from LINE Messaging API
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
