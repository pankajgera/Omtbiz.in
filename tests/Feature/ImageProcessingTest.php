<?php

namespace Tests\Feature;

use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{
    public function test_uploaded_images_can_be_resized_and_encoded(): void
    {
        $manager = ImageManager::usingDriver(config('image.driver'));
        $source = (string) $manager->createImage(800, 600)->encode(new JpegEncoder());
        $image = $manager->decode($source);

        $screen = (clone $image)->scale(height: 500)->encode(new JpegEncoder());
        $thumbnail = (clone $image)->cover(181, 121)->encode(new JpegEncoder());

        $this->assertNotEmpty((string) $screen);
        $this->assertNotEmpty((string) $thumbnail);
        $this->assertSame(667, $manager->decode($screen)->width());
        $this->assertSame(500, $manager->decode($screen)->height());
        $this->assertSame(181, $manager->decode($thumbnail)->width());
        $this->assertSame(121, $manager->decode($thumbnail)->height());
    }
}
