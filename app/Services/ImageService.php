<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Memproses dan menyimpan gambar yang diunggah sesuai spesifikasi:
     * - Batas ukuran 10 MB
     * - Tolak SVG
     * - Cek dimensi sebelum dekode (maksimal 24 megapiksel)
     * - Buang EXIF
     * - Konversi ke WebP kualitas tinggi (maksimal sisi terpanjang 2560 px)
     * - Buat thumbnail (maksimal 300 px)
     *
     * @return array{stored_name: string, size: int, width: int, height: int, thumb_path: string}
     */
    public static function processAndStore(UploadedFile $file, int $projectId): array
    {
        // 1. Tolak SVG
        $mime = $file->getMimeType();
        if ($mime === 'image/svg+xml' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            abort(422, 'Format berkas SVG tidak diizinkan demi keamanan.');
        }

        // 2. Cek dimensi sebelum dekode dengan getimagesize
        $imageInfo = @getimagesize($file->getRealPath());
        if (! $imageInfo) {
            abort(422, 'Berkas yang diunggah bukan format gambar yang valid.');
        }

        [$origWidth, $origHeight, $imageType] = $imageInfo;
        $totalPixels = $origWidth * $origHeight;

        // Batas 24 Megapiksel (misal 6000x4000)
        if ($totalPixels > 24000000) {
            abort(422, 'Dimensi gambar melebihi batas maksimal 24 megapiksel.');
        }

        // 3. Muat gambar menggunakan GD berdasarkan tipe
        $srcImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file->getRealPath()),
            default => null,
        };

        if (! $srcImage) {
            abort(422, 'Gagal memproses gambar. Format yang didukung: JPEG, PNG, WebP.');
        }

        // Aktifkan alpha blending untuk PNG/WebP transparan
        imagealphablending($srcImage, true);
        imagesavealpha($srcImage, true);

        // 4. Hitung skala sisi terpanjang (maksimal 2560 px)
        $maxDimension = 2560;
        $targetWidth = $origWidth;
        $targetHeight = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth >= $origHeight) {
                $targetWidth = $maxDimension;
                $targetHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
            } else {
                $targetHeight = $maxDimension;
                $targetWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
            }
        }

        // Buat kanvas utama
        $mainCanvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($mainCanvas, false);
        imagesavealpha($mainCanvas, true);
        imagecopyresampled($mainCanvas, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

        // Simpan gambar utama dalam format WebP
        $randomBaseName = Str::random(40);
        $projectDir = "projects/{$projectId}";
        $mainFileName = "{$projectDir}/{$randomBaseName}.webp";
        $thumbFileName = "{$projectDir}/thumb_{$randomBaseName}.webp";

        // Buat direktori jika belum ada di disk local
        Storage::disk('local')->makeDirectory($projectDir);

        $mainFullPath = Storage::disk('local')->path($mainFileName);
        $thumbFullPath = Storage::disk('local')->path($thumbFileName);

        imagewebp($mainCanvas, $mainFullPath, 85);
        imagedestroy($mainCanvas);

        // 5. Buat thumbnail (maksimal 300 px)
        $thumbMax = 300;
        if ($origWidth >= $origHeight) {
            $thumbWidth = $thumbMax;
            $thumbHeight = (int) round(($origHeight / $origWidth) * $thumbMax);
        } else {
            $thumbHeight = $thumbMax;
            $thumbWidth = (int) round(($origWidth / $origHeight) * $thumbMax);
        }

        $thumbCanvas = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagealphablending($thumbCanvas, false);
        imagesavealpha($thumbCanvas, true);
        imagecopyresampled($thumbCanvas, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $origWidth, $origHeight);

        imagewebp($thumbCanvas, $thumbFullPath, 80);
        imagedestroy($thumbCanvas);
        imagedestroy($srcImage);

        $storedSize = filesize($mainFullPath);

        return [
            'stored_name' => $mainFileName,
            'size' => $storedSize,
            'width' => $targetWidth,
            'height' => $targetHeight,
            'thumb_path' => $thumbFileName,
        ];
    }
}

