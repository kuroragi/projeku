#!/usr/bin/env bash
# ==============================================================================
# Script: deploy.sh
# Fungsi: Melakukan git add, menanyakan pesan commit, lalu git push
# ==============================================================================

set -eo pipefail

# Warna untuk output terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Masuk ke direktori root repository
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo -e "${BLUE}=== Git Deploy / Push Script ===${NC}"

# Peringatan jika dijalankan di dalam Docker container
if [ -f /.dockerenv ]; then
    echo -e "${YELLOW}[PERINGATAN] Anda sedang berada di dalam Docker container.${NC}"
    if ! command -v ssh >/dev/null 2>&1; then
        echo -e "${RED}[ERROR] Perintah 'ssh' tidak tersedia di dalam container ini.${NC}"
        echo -e "${YELLOW}SSH key Anda berada di komputer host. Jalankan 'npm run push' di terminal host (luar Docker).${NC}"
        exit 1
    fi
fi

# Pastikan berada di dalam repositori git
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo -e "${RED}[ERROR] Direktori ini bukan repositori git!${NC}"
    exit 1
fi

# Dapatkan nama branch saat ini dengan fallback aman
BRANCH="$(git branch --show-current 2>/dev/null || true)"
if [ -z "$BRANCH" ] || [ "$BRANCH" = "HEAD" ]; then
    BRANCH="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")"
fi

echo -e "Branch saat ini: ${GREEN}${BRANCH}${NC}\n"

# Pastikan identitas git commit telah disetel
if [ -z "$(git config user.name || true)" ] || [ -z "$(git config user.email || true)" ]; then
    echo -e "${YELLOW}[INFO] Menyetel identitas git default...${NC}"
    git config user.name "kurokami mint"
    git config user.email "uum1612@gmail.com"
fi

# Periksa apakah ada perubahan berkas
if [ -z "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}[INFO] Tidak ada perubahan berkas yang perlu di-commit.${NC}"
    
    # Tanyakan apakah tetap ingin push commit yang sudah ada tapi belum di-push
    AHEAD="$(git status -sb | grep -o 'ahead [0-9]*' || true)"
    if [ -n "$AHEAD" ]; then
        echo -e "${YELLOW}[INFO] Ada commit lokal yang belum di-push ($AHEAD).${NC}"
        PUSH_NOW=""
        if [ -r /dev/tty ]; then
            read -r -p "Ingin melakukan git push sekarang? (y/n): " PUSH_NOW < /dev/tty || true
        else
            read -r -p "Ingin melakukan git push sekarang? (y/n): " PUSH_NOW || true
        fi

        if [[ "$PUSH_NOW" =~ ^[Yy]$ ]]; then
            echo -e "${BLUE}Melakukan git push ke origin/${BRANCH}...${NC}"
            git push origin "$BRANCH"
            echo -e "${GREEN}[BERHASIL] Push selesai!${NC}"
        fi
    fi
    exit 0
fi

# Tampilkan ringkasan status perubahan
echo -e "${YELLOW}Daftar perubahan berkas:${NC}"
git status --short
echo ""

# Tahap 1: Git Add
echo -e "${BLUE}[1/3] Melakukan git add...${NC}"
git add -A
echo -e "${GREEN}✓ Semua perubahan telah ditambahkan (staged).${NC}\n"

# Tahap 2: Input Pesan Commit & Git Commit
COMMIT_MSG="${1:-}"

while [ -z "$(echo "$COMMIT_MSG" | xargs)" ]; do
    if [ -r /dev/tty ]; then
        read -r -p "Masukkan nama / pesan commit: " COMMIT_MSG < /dev/tty || true
    else
        read -r -p "Masukkan nama / pesan commit: " COMMIT_MSG || true
    fi

    if [ "$COMMIT_MSG" = "batal" ] || [ "$COMMIT_MSG" = "exit" ] || [ "$COMMIT_MSG" = "q" ]; then
        echo -e "\n${YELLOW}Proses dibatalkan oleh pengguna.${NC}"
        exit 0
    fi

    if [ -z "$(echo "$COMMIT_MSG" | xargs)" ]; then
        echo -e "${RED}Pesan commit tidak boleh kosong. (Ketik 'batal' untuk keluar)${NC}"
    fi
done

echo -e "\n${BLUE}[2/3] Melakukan git commit...${NC}"
git commit -m "$COMMIT_MSG"
echo -e "${GREEN}✓ Berhasil commit: \"$COMMIT_MSG\"${NC}\n"

# Tahap 3: Git Push
echo -e "${BLUE}[3/3] Melakukan git push origin $BRANCH...${NC}"
if git push origin "$BRANCH"; then
    echo -e "\n${GREEN}=== [BERHASIL] Seluruh perubahan berhasil di-push ke branch '${BRANCH}'! ===${NC}"
else
    echo -e "\n${RED}=== [GAGAL] Terjadi kesalahan saat melakukan git push. ===${NC}"
    echo -e "${YELLOW}Kemungkinan penyebab:${NC}"
    echo -e "1. Jika dijalankan di dalam Docker container: SSH key tidak terbaca di dalam container. Jalankan di terminal HOST."
    echo -e "2. Remote branch memiliki commit baru: jalankan 'git pull origin $BRANCH --rebase' terlebih dahulu."
    echo -e "3. Periksa koneksi SSH GitHub: jalankan 'ssh -T git@github.com' di terminal host."
    exit 1
fi
