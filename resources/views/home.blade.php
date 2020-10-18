@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div>
                            @empty(auth()->user()->notify_token)
                                <a href="{{ route('notify.login') }}">LINE Notify: Login</a>
                            @else
                                <a href="{{ route('notify.send')  }}">LINE Notify: Send test message</a>
                            @endempty
                        </div>

                        <div>
                            <a href="{{ route('push') }}">Push message</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
