<x-guest-layout>
    <div class="max-w-lg mx-auto py-10 sm:px-6 lg:px-8 bg-white rounded-lg mt-6">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login to Your Account</h2>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                    <label for="remember_me" class="ml-2 block text-sm text-gray-600">Remember me</label>
                </div>
            </div>

            <div class="flex items-center justify-between">
                @if (Route::has('password.request'))
                    <a class="text-sm text-blue-600 hover:text-blue-900" href="{{ route('password.request') }}">
                        Forgot your password?
                    </a>
                @endif

                <x-primary-button class="ml-3">
                    Log in
                </x-primary-button>
            </div>
        </form>

        <div class="mt-6 text-center">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    Don't have an account? Register
                </a>
            @endif
        </div>
    </div>
</x-guest-layout>
