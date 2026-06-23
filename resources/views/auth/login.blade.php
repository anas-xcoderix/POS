<x-guest-layout>
    <div class="mb-8">
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500 text-lg font-bold text-white shadow-sm">
            PF
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to {{ config('app.name', 'PartFlow') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-ui.form-field label="Email address" name="email" type="email" :value="old('email')" required autofocus />
        @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        <x-ui.form-field label="Password" name="password" type="password" required />
        @error('password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-orange-500 focus:ring-orange-400">
            <span class="text-sm text-slate-600">Remember me</span>
        </label>
        <button type="submit" class="erp-btn-primary w-full !py-3">Sign in</button>
    </form>
    <p class="mt-6 text-center text-xs text-slate-400">Demo: admin@gmail.com / password</p>
</x-guest-layout>
