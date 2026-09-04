<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Yuklangan rasmni avtomatik WebP formatiga o'tkazish, siqish va saqlash
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $folder = 'dachas/images',
        int $maxWidth = 1600,
        int $quality = 82
    ): string {
        // Agar GD kutubxonasi mavjud bo'lmasa, oddiy saqlashga o'tadi
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return $file->store($folder, 'public');
        }

        try {
            $sourcePath = $file->getRealPath();
            $mime = $file->getMimeType();

            // 1. Manba rasmni yuklash
            $image = match ($mime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
                'image/png' => @imagecreatefrompng($sourcePath),
                'image/webp' => @imagecreatefromwebp($sourcePath),
                'image/gif' => @imagecreatefromgif($sourcePath),
                default => null,
            };

            if (!$image) {
                return $file->store($folder, 'public');
            }

            // 2. EXIF orientatsiyasini tuzatish (Telefon orqali tushirilgan rasmlar ag'darilib qolmasligi uchun)
            if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'])) {
                $exif = @exif_read_data($sourcePath);
                if (!empty($exif['Orientation'])) {
                    $image = match ($exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            }

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // 3. Asosiy rasmni o'lchamini moslashtirish (Max: $maxWidth)
            $mainImage = $this->resizeImage($image, $origWidth, $origHeight, $maxWidth);

            // 4. Fayl nomlarini shakllantirish
            $uniqueName = 'dacha_' . Str::random(16) . '_' . time();
            $mainFileName = $uniqueName . '.webp';
            $thumbFileName = 'thumb_' . $uniqueName . '.webp';

            $storageDir = storage_path('app/public/' . trim($folder, '/'));
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            $mainFilePath = $storageDir . '/' . $mainFileName;
            $thumbFilePath = $storageDir . '/' . $thumbFileName;

            // 5. Asosiy WebP rasmni saqlash
            imagewebp($mainImage, $mainFilePath, $quality);
            if ($mainImage !== $image) {
                imagedestroy($mainImage);
            }

            // 6. Kichik rasm (Thumbnail - 400px) yaratish va saqlash
            $thumbImage = $this->resizeImage($image, $origWidth, $origHeight, 400);
            imagewebp($thumbImage, $thumbFilePath, 78);
            if ($thumbImage !== $image) {
                imagedestroy($thumbImage);
            }

            imagedestroy($image);

            return trim($folder, '/') . '/' . $mainFileName;
        } catch (\Throwable $e) {
            Log::warning('WebP optimization fallback: ' . $e->getMessage());
            return $file->store($folder, 'public');
        }
    }

    /**
     * Rasmni proporsiyasini saqlagan holda o'lchamini o'zgartirish
     */
    protected function resizeImage($source, int $width, int $height, int $maxDimension)
    {
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $source;
        }

        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) round(($height / $width) * $maxDimension);
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) round(($width / $height) * $maxDimension);
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // PNG / WebP shaffoflik (transparency) ni saqlash
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }
}
