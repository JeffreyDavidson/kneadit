<?php

namespace App\Services\Support;

use ErrorException;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores customer-uploaded photos without the metadata a camera embeds in them.
 *
 * Phone photos carry EXIF blocks with GPS coordinates, device details and
 * timestamps. Storing the upload as sent would publish a customer's home or
 * workplace location with the gallery. Decoding the pixels and encoding them
 * again writes a fresh file with none of that, so metadata can never leak
 * through a format quirk. The EXIF orientation is applied first, because
 * dropping it would store phone photos sideways.
 */
class PrivateImageStore
{
    private const int MAX_EDGE = 2560;

    private const int QUALITY = 85;

    /**
     * @return string Path of the stored file on the disk, relative to its root.
     *
     * @throws ValidationException when the upload is not a readable JPEG, PNG or WebP image.
     */
    public function store(UploadedFile $upload, string $directory, string $disk = 'public'): string
    {
        $contents = (string) $upload->getContent();
        $type = getimagesizefromstring($contents)[2] ?? null;

        $extension = match ($type) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => $this->unreadable(),
        };

        $image = $this->scaled($this->oriented($this->decode($contents), $type, $upload));

        $path = sprintf('%s/%s.%s', $directory, Str::random(40), $extension);
        Storage::disk($disk)->put($path, $this->encode($image, $type));

        return $path;
    }

    private function decode(string $contents): GdImage
    {
        try {
            $image = imagecreatefromstring($contents);
        } catch (ErrorException) {
            $this->unreadable();
        }

        return $image instanceof GdImage ? $image : $this->unreadable();
    }

    private function oriented(GdImage $image, int $type, UploadedFile $upload): GdImage
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $orientation = exif_read_data($upload->getPathname())['Orientation'] ?? 1;
        } catch (ErrorException) {
            return $image;
        }

        return match ($orientation) {
            2 => $this->flipped($image, IMG_FLIP_HORIZONTAL),
            3 => imagerotate($image, 180, 0) ?: $image,
            4 => $this->flipped($image, IMG_FLIP_VERTICAL),
            5 => $this->flipped(imagerotate($image, -90, 0) ?: $image, IMG_FLIP_HORIZONTAL),
            6 => imagerotate($image, -90, 0) ?: $image,
            7 => $this->flipped(imagerotate($image, 90, 0) ?: $image, IMG_FLIP_HORIZONTAL),
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }

    private function flipped(GdImage $image, int $mode): GdImage
    {
        imageflip($image, $mode);

        return $image;
    }

    private function scaled(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (max($width, $height) <= self::MAX_EDGE) {
            return $image;
        }

        $newWidth = $width >= $height
            ? self::MAX_EDGE
            : (int) round($width * self::MAX_EDGE / $height);

        return imagescale($image, $newWidth) ?: $image;
    }

    private function encode(GdImage $image, int $type): string
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();

        match ($type) {
            IMAGETYPE_PNG => imagepng($image),
            IMAGETYPE_WEBP => imagewebp($image, null, self::QUALITY),
            default => imagejpeg($image, null, self::QUALITY),
        };

        return (string) ob_get_clean();
    }

    private function unreadable(): never
    {
        throw ValidationException::withMessages([
            'photo' => 'We could not read that image. Please upload a JPG, PNG or WebP photo.',
        ]);
    }
}
