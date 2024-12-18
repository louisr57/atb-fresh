<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" id="forgot-password-form">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- reCAPTCHA -->
        <div class="mt-4" id="recaptcha-container" style="{{ session('status') ? 'display: none;' : '' }}">
            <div class="g-recaptcha" data-sitekey="6LfsH58qAAAAAMCpVghEvlQtQl0hcifb-sNoQU-V"></div>
            <x-input-error :messages="$errors->get('g-recaptcha-response')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button id="submit-btn">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <!-- reCAPTCHA Script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        // Check if the form submission was successful
        @if(session('status'))
            document.getElementById('recaptcha-container').style.display = 'none';
        @endif

        // Reset reCAPTCHA if there were validation errors
        @if($errors->any())
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
            }
        @endif
    </script>
</x-guest-layout>
