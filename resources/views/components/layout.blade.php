@props([
    'title' => 'projeku',
])

<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#0F1115">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — projeku</title>

    <!-- Alpine.js & Tailwind CSS 4 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg text-text font-sans antialiased selection:bg-accent selection:text-on-accent">
    <div class="flex min-h-screen flex-col">
        @auth
            <!-- Top Navbar -->
            <header class="flex h-14 w-full items-center justify-between border-b border-border bg-surface-1 px-4 sm:px-6 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <a href="/projects" class="flex items-center gap-2 font-semibold text-text hover:text-accent transition">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-[6px] bg-accent text-on-accent text-xs font-bold">
                            P
                        </span>
                        <span class="text-sm tracking-wide">projeku</span>
                    </a>

                    @if(auth()->user()->is_admin)
                        <span class="h-4 w-[1px] bg-border"></span>
                        <a href="/admin/users" class="text-xs font-medium text-text-muted hover:text-text transition flex items-center gap-1.5">
                            <x-icon name="users" :size="14" />
                            <span>Kelola Pengguna</span>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-3" x-data="{ userMenu: false }">
                    <span class="text-xs text-text-muted hidden sm:inline">{{ auth()->user()->name }}</span>

                    <button 
                        @click="userMenu = !userMenu" 
                        class="flex items-center gap-2 rounded-full p-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >
                        <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#C4B5FD]/20 text-xs font-semibold text-[#C4B5FD]">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    </button>

                    <div 
                        x-show="userMenu" 
                        @click.outside="userMenu = false" 
                        x-transition
                        class="absolute right-4 sm:right-6 top-12 z-40 mt-1 w-48 rounded-[8px] border border-border-strong bg-surface-2 p-1 shadow-xl"
                        style="display: none;"
                    >
                        <div class="px-2.5 py-2 border-b border-border">
                            <p class="text-xs font-medium text-text truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-text-faint truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-[6px] px-2.5 py-1.5 text-xs text-danger hover:bg-danger-soft transition">
                                <x-icon name="logout" :size="14" />
                                <span>Keluar (Logout)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endauth

        <!-- Flash Toast Notification -->
        @if(session('status') || session('success') || $errors->any())
            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-init="setTimeout(() => show = false, 5000)"
                class="fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-[8px] border border-border-strong bg-surface-3 p-3.5 shadow-xl transition-all {{ $errors->any() ? 'border-l-[3px] border-l-danger' : 'border-l-[3px] border-l-success' }}"
            >
                <div class="{{ $errors->any() ? 'text-danger' : 'text-success' }} shrink-0">
                    <x-icon :name="$errors->any() ? 'alert-triangle' : 'check'" :size="20" class="stroke-[2]" />
                </div>
                <div class="flex-1 text-xs text-text">
                    @if($errors->any())
                        <p class="font-medium text-danger">{{ $errors->first() }}</p>
                    @else
                        <p class="font-medium text-text">{{ session('status') ?? session('success') }}</p>
                    @endif
                </div>
                <button @click="show = false" class="text-text-faint hover:text-text transition shrink-0">
                    <x-icon name="close" :size="16" />
                </button>
            </div>
        @endif

        <!-- Main Content Area -->
        <main class="flex-1">
            {{ $slot }}
        </main>
    </div>
</body>
</html>

