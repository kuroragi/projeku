<x-layout :title="$project->name . ' — Tempat Sampah'">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <!-- Header -->
        <div class="border-b border-border bg-surface-1 px-4 sm:px-6 pt-4 pb-0 -mx-4 sm:-mx-6 -mt-8 mb-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-text">{{ $project->name }}</h1>
                    <p class="text-xs text-text-muted mt-0.5">Tempat sampah projek (otomatis dimusnahkan setelah 30 hari)</p>
                </div>
            </div>

            <!-- Tab Navigasi -->
            <nav class="mt-4 flex space-x-6">
                <a href="{{ route('projects.board', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="board" :size="16" />
                    <span>Board</span>
                </a>
                <a href="{{ route('projects.planning.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="document" :size="16" />
                    <span>Perencanaan</span>
                </a>
                <a href="{{ route('projects.files.index', $project) }}" class="flex items-center gap-2 border-b-2 border-transparent pb-2.5 text-xs font-medium text-text-muted hover:text-text transition">
                    <x-icon name="folder" :size="16" />
                    <span>Berkas</span>
                </a>
                <a href="{{ route('projects.trash.index', $project) }}" class="flex items-center gap-2 border-b-2 border-accent pb-2.5 text-xs font-medium text-text">
                    <x-icon name="trash" :size="16" />
                    <span>Tempat Sampah</span>
                </a>
            </nav>
        </div>

        <div class="space-y-8">
            <!-- Bagian 1: Kartu yang Dihapus -->
            <div>
                <h3 class="text-sm font-semibold text-text mb-3">Kartu yang Dihapus ({{ $trashedCards->count() }})</h3>
                @if($trashedCards->isEmpty())
                    <p class="text-xs text-text-faint italic">Tidak ada kartu di tempat sampah.</p>
                @else
                    <div class="divide-y divide-border rounded-[8px] border border-border bg-surface-2">
                        @foreach($trashedCards as $c)
                            <div class="flex items-center justify-between p-3.5 text-xs">
                                <div>
                                    <h4 class="font-medium text-text">{{ $c->title }}</h4>
                                    <p class="text-[11px] text-text-faint mt-0.5">
                                        Dihapus pada {{ $c->deleted_at->format('d M Y H:i') }} • 
                                        Sisa {{ max(0, 30 - $c->deleted_at->diffInDays(now())) }} hari sebelum musnah otomatis
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('projects.trash.cards.restore', [$project, $c->id]) }}">
                                        @csrf
                                        <x-button type="submit" variant="secondary" size="sm">
                                            Pulihkan
                                        </x-button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.trash.cards.purge', [$project, $c->id]) }}" onsubmit="return confirm('Musnahkan kartu ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" size="sm">
                                            Hapus Permanen
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Bagian 2: Berkas yang Dihapus -->
            <div>
                <h3 class="text-sm font-semibold text-text mb-3">Berkas yang Dihapus ({{ $trashedFiles->count() }})</h3>
                @if($trashedFiles->isEmpty())
                    <p class="text-xs text-text-faint italic">Tidak ada berkas di tempat sampah.</p>
                @else
                    <div class="divide-y divide-border rounded-[8px] border border-border bg-surface-2">
                        @foreach($trashedFiles as $f)
                            <div class="flex items-center justify-between p-3.5 text-xs">
                                <div>
                                    <h4 class="font-medium text-text">{{ $f->original_name }}</h4>
                                    <p class="text-[11px] text-text-faint mt-0.5">
                                        {{ round($f->size / 1024, 1) }} KB • Dihapus pada {{ $f->deleted_at->format('d M Y H:i') }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('projects.trash.files.restore', [$project, $f->id]) }}">
                                        @csrf
                                        <x-button type="submit" variant="secondary" size="sm">
                                            Pulihkan
                                        </x-button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.trash.files.purge', [$project, $f->id]) }}" onsubmit="return confirm('Musnahkan berkas fisik dari server secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" size="sm">
                                            Hapus Permanen
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Bagian 3: Halaman Perencanaan yang Dihapus -->
            <div>
                <h3 class="text-sm font-semibold text-text mb-3">Halaman Perencanaan yang Dihapus ({{ $trashedPages->count() }})</h3>
                @if($trashedPages->isEmpty())
                    <p class="text-xs text-text-faint italic">Tidak ada halaman perencanaan di tempat sampah.</p>
                @else
                    <div class="divide-y divide-border rounded-[8px] border border-border bg-surface-2">
                        @foreach($trashedPages as $p)
                            <div class="flex items-center justify-between p-3.5 text-xs">
                                <div>
                                    <h4 class="font-medium text-text">{{ $p->title }}</h4>
                                    <p class="text-[11px] text-text-faint mt-0.5">
                                        Dihapus pada {{ $p->deleted_at->format('d M Y H:i') }} • 
                                        Sisa {{ max(0, 30 - $p->deleted_at->diffInDays(now())) }} hari sebelum musnah otomatis
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('projects.trash.pages.restore', [$project, $p->id]) }}">
                                        @csrf
                                        <x-button type="submit" variant="secondary" size="sm">
                                            Pulihkan
                                        </x-button>
                                    </form>

                                    <form method="POST" action="{{ route('projects.trash.pages.purge', [$project, $p->id]) }}" onsubmit="return confirm('Musnahkan dokumen ini secara permanen?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" size="sm">
                                            Hapus Permanen
                                        </x-button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>

