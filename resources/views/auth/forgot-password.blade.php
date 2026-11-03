<x-layout title="Lupa Kata Sandi">
    <div class="flex min-h-[calc(100vh-56px)] items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-semibold text-text">Lupa Kata Sandi</h1>
                <p class="mt-1 text-xs text-text-muted">Masukkan email akun Anda untuk menerima tautan reset kata sandi</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Kirim Tautan Reset
                    </x-button>
                </div>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="text-xs text-text-muted hover:text-text transition">
                        &larr; Kembali ke halaman login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>

