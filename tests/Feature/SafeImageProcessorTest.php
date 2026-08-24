<?php

namespace Tests\Feature;

use App\Support\SafeImageProcessor;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SafeImageProcessorTest extends TestCase
{
    public function test_valid_image_is_reencoded_with_a_server_selected_extension(): void
    {
        $binary = (string) ImageManager::usingDriver(config('image.driver'))
            ->createImage(20, 20)
            ->encode(new PngEncoder());
        $payload = json_encode([
            'name' => 'avatar.php.png',
            'data' => 'data:image/png;base64,' . base64_encode($binary),
        ], JSON_THROW_ON_ERROR);

        $result = app(SafeImageProcessor::class)->fromJsonPayload($payload, 'avatar');

        $this->assertSame('png', $result['extension']);
        $this->assertSame('image/png', $result['mime_type']);
        $this->assertNotEmpty($result['contents']);
    }

    #[DataProvider('maliciousPayloads')]
    public function test_non_images_and_parser_bypasses_are_rejected(string $payload): void
    {
        $this->expectException(ValidationException::class);

        app(SafeImageProcessor::class)->fromJsonPayload($payload, 'avatar');
    }

    public static function maliciousPayloads(): array
    {
        return [
            'php' => [json_encode(['name' => 'shell.php', 'data' => base64_encode('<?php system($_GET["x"]);')])],
            'svg script' => [json_encode(['name' => 'logo.svg', 'data' => base64_encode('<svg onload="alert(1)"></svg>')])],
            'malformed base64' => [json_encode(['name' => 'logo.png', 'data' => '%%%not-base64%%%'])],
            'mime mismatch' => [json_encode(['name' => 'logo.jpg', 'data' => 'data:image/jpeg;base64,' . base64_encode("\x89PNG\r\n")])],
            'oversized' => [json_encode(['name' => 'large.jpg', 'data' => base64_encode(str_repeat('A', SafeImageProcessor::MAX_BYTES + 1))])],
        ];
    }

    public function test_nginx_serves_storage_only_as_static_content(): void
    {
        foreach (['nginx.conf', 'docker/nginx/default.conf'] as $configuration) {
            $contents = file_get_contents(base_path($configuration));

            $this->assertStringContainsString('location ^~ /storage/', $contents);
            $this->assertStringContainsString('try_files $uri =404;', $contents);
        }
    }
}
