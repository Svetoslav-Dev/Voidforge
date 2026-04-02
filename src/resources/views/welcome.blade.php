@extends('layouts.app', ['title' => 'VoidForgeStore'])

@section('content')
    <section class="card hero">
        <div>
            <h1 style="color: #d89a58;">{{ __('welcome.heading') }}</h1>
            <p class="lead">{{ __('welcome.lead') }}</p>
        </div>

        <div class="grid two">
            <div class="card card-plain">
                <h2 style="color: #4ecba3;">{{ __('welcome.what_you_can_do_title') }}</h2>
                <p class="muted">{{ __('welcome.what_you_can_do') }}</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">{{ __('welcome.your_account_title') }}</h2>
                <p class="muted">{{ __('welcome.your_account') }}</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">{{ __('welcome.payments_title') }}</h2>
                <p class="muted">{{ __('welcome.payments') }}</p>
            </div>

            <div class="card card-plain">
                <h2 style="color: #4ecba3;">{{ __('welcome.legal_title') }}</h2>
                <p class="muted">{{ __('welcome.legal') }}</p>
            </div>
        </div>
    </section>

@endsection
