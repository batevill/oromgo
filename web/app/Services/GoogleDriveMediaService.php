<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleDriveMediaService
{
    /**
     * Google Drive-ga faylni Yil va Oy papkalariga bo'lib oqimli (Stream) yuklash
     */
    public function upload(UploadedFile $file, string $type = 'image'): array
    {
        $extension = $file->getClientOriginalExtension() ?: ($type === 'video' ? 'mp4' : 'jpg');
        $uniqueName = 'dacha_' . Str::random(16) . '_' . time() . '.' . $extension;

        // Avtomatik joriy yil va oyni aniqlash: masalan, images/2026/9 yoki videos/2026/9
        $rootFolder = $type === 'video' ? 'videos' : 'images';
        $year = date('Y');
        $month = date('n'); // 1, 2, ... 9, 10, 11, 12
        $folder = "{$rootFolder}/{$year}/{$month}";
        $path = "{$folder}/{$uniqueName}";

        $diskName = config('filesystems.default') === 'google' 
            || !empty(config('filesystems.disks.google.clientId'))
            ? 'google' 
            : 'public';

        try {
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk($diskName)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $fileId = null;
            if ($diskName === 'google') {
                try {
                    $adapter = Storage::disk('google')->getAdapter();
                    if (method_exists($adapter, 'getMetadata')) {
                        $meta = $adapter->getMetadata($path);
                        $fileId = is_array($meta) ? ($meta['id'] ?? null) : ($meta->extraMetadata()['id'] ?? null);
                    }
                } catch (\Throwable $e) {
                    Log::info('Google Drive File ID retrieval: ' . $e->getMessage());
                }
            }

            return [
                'disk' => $diskName,
                'path' => $path,
                'file_id' => $fileId,
                'file_name' => $uniqueName,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ];
        } catch (\Throwable $e) {
            Log::error('Media upload error: ' . $e->getMessage());
            // Fallback to local public disk if google drive fails
            $path = $file->store($folder, 'public');
            return [
                'disk' => 'public',
                'path' => $path,
                'file_id' => null,
                'file_name' => basename($path),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ];
        }
    }

    /**
     * Faylni o'chirish
     */
    public function delete(string $path, ?string $diskName = null): bool
    {
        $disk = $diskName ?: (config('filesystems.default') === 'google' || env('FILESYSTEM_DISK') === 'google' ? 'google' : 'public');
        
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            Log::error('Media delete error: ' . $e->getMessage());
        }

        return false;
    }
}
