<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class SafeImageProcessor
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function fromJsonPayload(string $payload, string $field): array
    {
        $data = json_decode($payload, true);

        if (!is_array($data) || !isset($data['data']) || !is_string($data['data'])) {
            throw ValidationException::withMessages([$field => 'The image payload is invalid.']);
        }

        $encoded = $data['data'];
        $declaredMime = null;

        if (preg_match('/^data:([^;,]+);base64,(.*)$/s', $encoded, $matches)) {
            $declaredMime = strtolower($matches[1]);
            $encoded = $matches[2];
        }

        $binary = base64_decode($encoded, true);

        if ($binary === false) {
            throw ValidationException::withMessages([$field => 'The image must contain valid base64 data.']);
        }

        return $this->process($binary, $field, $declaredMime);
    }

    public function fromUploadedFile(UploadedFile $file, string $field): array
    {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([$field => 'The image upload failed.']);
        }

        return $this->process($file->getContent(), $field);
    }

    private function process(string $binary, string $field, ?string $declaredMime = null): array
    {
        if ($binary === '' || strlen($binary) > self::MAX_BYTES) {
            throw ValidationException::withMessages([$field => 'The image must not exceed 5 MB.']);
        }

        $details = @getimagesizefromstring($binary);
        $detectedMime = is_array($details) ? ($details['mime'] ?? null) : null;

        if (!isset(self::MIME_EXTENSIONS[$detectedMime])) {
            throw ValidationException::withMessages([$field => 'Only JPEG, PNG, and WebP images are allowed.']);
        }

        if ($declaredMime !== null && $declaredMime !== $detectedMime) {
            throw ValidationException::withMessages([$field => 'The declared image type does not match its contents.']);
        }

        try {
            $image = ImageManager::usingDriver(config('image.driver'))->decode($binary);

            if ($image->width() * $image->height() > 25_000_000) {
                throw ValidationException::withMessages([$field => 'The image dimensions are too large.']);
            }

            $encoded = match ($detectedMime) {
                'image/jpeg' => $image->encode(new JpegEncoder(quality: 90)),
                'image/png' => $image->encode(new PngEncoder()),
                'image/webp' => $image->encode(new WebpEncoder(quality: 90)),
            };
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw ValidationException::withMessages([$field => 'The image could not be decoded safely.']);
        }

        return [
            'contents' => (string) $encoded,
            'extension' => self::MIME_EXTENSIONS[$detectedMime],
            'mime_type' => $detectedMime,
        ];
    }
}
