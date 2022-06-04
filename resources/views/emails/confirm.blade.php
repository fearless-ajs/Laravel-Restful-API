@component('mail::message')
    # Hello {{$user->name}}

    You changed your email so we need to verify the new email. Please user this this link below:

    @component('mail::button', ['url' => route('verify', $user->verification_token)])
        Button Text
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
