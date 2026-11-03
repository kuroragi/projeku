<x-layout title="Masuk">
    <div class="flex min-h-[calc(100vh-56px)] items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
            <!-- Header Form -->
            <div class="mb-6 text-center">
                <div class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-[8px] bg-accent text-on-accent text-base font-bold mb-3">
                    P
                </div>
                <h1 class="text-xl font-semibold text-text">Masuk ke projeku</h1>
                <p class="mt-1 text-xs text-text-muted">Aplikasi manajemen projek pribadi & tim</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <x-input 
                    label="Alamat Email" 
                    type="email" 
                    name="email" 
                    id="email" 
                    :value="old('email')" 
                    required 
                    autofocus 
                    placeholder="nama@domain.com"
                    :error="$errors->first('email')"
                />

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-xs font-medium text-text">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" class="text-[11px] text-accent hover:underline">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••••••"
                        class="h-9 w-full rounded-[8px] border border-border bg-surface-3 px-3 text-sm text-text placeholder-text-faint transition focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/40"
                    />
                    @if($errors->has('password'))
                        <p class="text-xs text-danger">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="h-4 w-4 rounded-[4px] border-border bg-surface-3 text-accent focus:ring-accent focus:ring-1"
                        />
                        <span class="text-xs text-text-muted">Ingat saya</span>
                    </label>
                </div>

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Masuk
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>

