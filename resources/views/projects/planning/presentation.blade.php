<!DOCTYPE html>
<html lang="id" class="h-full bg-bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page->title }} — Mode Presentasi</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body 
    class="h-full bg-bg text-text antialiased selection:bg-accent selection:text-on-accent overflow-hidden font-sans"
    x-data="presentationMode({
        totalSlides: {{ count($slides) }},
        exitUrl: '{{ route('projects.pages.show', [$project, $page]) }}'
    })"
    @mousemove="pokeControls()"
    @keydown.window="handleKeydown($event)"
    @touchstart="pokeControls()"
>
    <!-- Kontrol Mengambang (Floating Controls) - Hilang otomatis setelah 2 detik -->
    <div 
        x-show="showControls" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="no-print fixed top-4 inset-x-0 z-50 flex items-center justify-between px-6 pointer-events-none"
        style="display: none;"
    >
        <!-- Tombol Keluar & Judul Halaman -->
        <div class="pointer-events-auto flex items-center gap-3 rounded-full border border-border bg-surface-2/90 px-4 py-2 shadow-xl backdrop-blur">
            <a 
                :href="exitUrl" 
                class="flex items-center gap-1.5 text-xs font-medium text-text-muted hover:text-text transition"
                title="Kembali ke Editor (Esc)"
            >
                <x-icon name="arrow-left" :size="14" />
                <span>Keluar</span>
            </a>
            <span class="h-3 w-px bg-border"></span>
            <span class="text-xs font-semibold text-text truncate max-w-xs sm:max-w-md">{{ $page->title }}</span>
        </div>

        <!-- Tombol Aksi Presentasi -->
        <div class="pointer-events-auto flex items-center gap-2 rounded-full border border-border bg-surface-2/90 px-3 py-1.5 shadow-xl backdrop-blur">
            <!-- Navigasi Slide -->
            <button 
                @click="prevSlide()" 
                :disabled="currentIndex === 0"
                class="p-1.5 text-text-muted hover:text-text disabled:opacity-30 disabled:hover:text-text-muted transition" 
                title="Slide Sebelumnya (Panah Kiri)"
            >
                <x-icon name="arrow-left" :size="16" />
            </button>
            <span class="text-xs font-mono font-medium text-text px-1" x-text="(currentIndex + 1) + ' / ' + totalSlides"></span>
            <button 
                @click="nextSlide()" 
                :disabled="currentIndex === totalSlides - 1"
                class="p-1.5 text-text-muted hover:text-text disabled:opacity-30 disabled:hover:text-text-muted transition" 
                title="Slide Selanjutnya (Panah Kanan / Spasi)"
            >
                <x-icon name="arrow-right" :size="16" />
            </button>

            <span class="h-3 w-px bg-border mx-1"></span>

            <!-- Cetak PDF -->
            <button 
                @click="printPdf()" 
                class="inline-flex items-center gap-1 p-1.5 text-xs text-text-muted hover:text-text transition" 
                title="Cetak PDF"
            >
                <x-icon name="printer" :size="16" />
            </button>

            <!-- Layar Penuh -->
            <button 
                @click="toggleFullscreen()" 
                class="p-1.5 text-text-muted hover:text-text transition" 
                title="Layar Penuh (F)"
            >
                <template x-if="!isFullscreen">
                    <x-icon name="maximize" :size="16" />
                </template>
                <template x-if="isFullscreen">
                    <x-icon name="minimize" :size="16" />
                </template>
            </button>
        </div>
    </div>

    <!-- Area Slide -->
    <main class="h-full w-full flex items-center justify-center p-0">
        @if(empty($slides))
            <div class="text-center p-8">
                <p class="text-xl text-text-muted">Halaman ini belum memiliki slide konten.</p>
                <a href="{{ route('projects.pages.show', [$project, $page]) }}" class="mt-4 inline-block text-accent hover:underline">
                    Kembali ke editor untuk menambahkan blok
                </a>
            </div>
        @else
            @foreach($slides as $slideIndex => $slideBlocks)
                <div 
                    class="slide h-full w-full flex flex-col justify-center px-[6%] py-[4%] select-none overflow-y-auto"
                    x-show="currentIndex === {{ $slideIndex }}"
                    x-cloak
                >
                    <div class="mx-auto w-full max-w-5xl space-y-6">
                        @foreach($slideBlocks as $block)
                            @if($block->type === 'judul')
                                @php
                                    $level = $block->content['level'] ?? 1;
                                @endphp
                                @if($level == 1)
                                    <h1 class="text-[44px] font-semibold leading-tight text-text tracking-tight mb-6">
                                        {{ $block->content['text'] ?? '' }}
                                    </h1>
                                @elseif($level == 2)
                                    <h2 class="text-[32px] font-semibold leading-snug text-text mb-4">
                                        {{ $block->content['text'] ?? '' }}
                                    </h2>
                                @else
                                    <h3 class="text-[26px] font-medium leading-snug text-text mb-3">
                                        {{ $block->content['text'] ?? '' }}
                                    </h3>
                                @endif

                            @elseif($block->type === 'teks')
                                <div class="text-[24px] leading-relaxed text-text/90 whitespace-pre-wrap">
                                    {{ $block->content['text'] ?? '' }}
                                </div>

                            @elseif($block->type === 'gambar')
                                <div class="bg-surface-1 rounded-[12px] p-3 flex flex-col items-center justify-center max-h-[55vh] border border-border">
                                    @php
                                        $imgUrl = $block->content['url'] ?? ($block->content['file_id'] ? route('projects.files.serve', [$project, $block->content['file_id']]) : null);
                                    @endphp
                                    @if($imgUrl)
                                        <img 
                                            src="{{ $imgUrl }}" 
                                            alt="{{ $block->content['caption'] ?? '' }}" 
                                            class="max-h-[48vh] max-w-full object-contain rounded-[8px]"
                                        />
                                    @endif
                                    @if(!empty($block->content['caption']))
                                        <p class="mt-2 text-center text-sm text-text-muted italic">
                                            {{ $block->content['caption'] }}
                                        </p>
                                    @endif
                                </div>

                            @elseif($block->type === 'daftar')
                                @php
                                    $style = $block->content['style'] ?? 'bullet';
                                    $items = $block->content['items'] ?? [];
                                @endphp
                                @if($style === 'numbered')
                                    <ol class="list-decimal list-inside space-y-3 text-[24px] leading-relaxed text-text">
                                        @foreach($items as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <ul class="list-disc list-inside space-y-3 text-[24px] leading-relaxed text-text">
                                        @foreach($items as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </main>

    <!-- Bilah Bawah: Penunjuk Slide & Batang Progres Tipis Aksen -->
    <div class="no-print fixed bottom-0 inset-x-0 z-40">
        <!-- Batang Progres Tipis Aksen -->
        <div class="h-1 w-full bg-surface-3">
            <div 
                class="h-full bg-accent transition-all duration-300 ease-out"
                :style="'width: ' + (((currentIndex + 1) / totalSlides) * 100) + '%'"
            ></div>
        </div>

        <!-- Penunjuk Slide Mini di Sudut Kanan Bawah -->
        <div 
            class="flex items-center justify-end px-6 py-2.5 text-xs font-mono font-medium text-text-faint transition-opacity duration-300"
            :class="showControls ? 'opacity-100' : 'opacity-40'"
        >
            <span x-text="(currentIndex + 1) + ' / ' + totalSlides"></span>
        </div>
    </div>

    <script>
        function presentationMode(config) {
            return {
                currentIndex: 0,
                totalSlides: config.totalSlides || 1,
                exitUrl: config.exitUrl,
                showControls: true,
                controlsTimer: null,
                isFullscreen: false,

                init() {
                    this.pokeControls();
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                },

                pokeControls() {
                    this.showControls = true;
                    if (this.controlsTimer) clearTimeout(this.controlsTimer);
                    this.controlsTimer = setTimeout(() => {
                        this.showControls = false;
                    }, 2000);
                },

                prevSlide() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    }
                    this.pokeControls();
                },

                nextSlide() {
                    if (this.currentIndex < this.totalSlides - 1) {
                        this.currentIndex++;
                    }
                    this.pokeControls();
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error(err);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                    this.pokeControls();
                },

                printPdf() {
                    window.print();
                },

                handleKeydown(e) {
                    this.pokeControls();

                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === ' ') {
                        e.preventDefault();
                        this.nextSlide();
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        this.prevSlide();
                    } else if (e.key === 'f' || e.key === 'F') {
                        e.preventDefault();
                        this.toggleFullscreen();
                    } else if (e.key === 'Escape') {
                        if (!document.fullscreenElement) {
                            window.location.href = this.exitUrl;
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
