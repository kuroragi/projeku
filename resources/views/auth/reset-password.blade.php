<x-layout title="Atur Ulang Kata Sandi">
    <div class="flex min-h-[calc(100vh-56px)] items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-[12px] border border-border-strong bg-surface-2 p-6 shadow-2xl">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-semibold text-text">Atur Kata Sandi</h1>
                <p class="mt-1 text-xs text-text-muted">Masukkan kata sandi baru (minimal 12 karakter)</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <x-input 
                    label="Alamat Email" 
                    type="email" 
                    name="email" 
                    id="email" 
                    :value="old('email', $request->email)" 
                    required 
                    readonly
                    :error="$errors->first('email')"
                />

                <x-input 
                    label="Kata Sandi Baru" 
                    type="password" 
                    name="password" 
                    id="password" 
                    required 
                    autofocus
                    placeholder="Minimal 12 karakter"
                    :error="$errors->first('password')"
                />

                <x-input 
                    label="Konfirmasi Kata Sandi" 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    required 
                    placeholder="Ketik ulang kata sandi"
                />

                <div class="pt-2">
                    <x-button type="submit" variant="primary" class="w-full">
                        Simpan Kata Sandi
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layout>

