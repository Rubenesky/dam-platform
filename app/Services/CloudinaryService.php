<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME', config('cloudinary.cloud_name'));
        $apiKey    = env('CLOUDINARY_API_KEY', config('cloudinary.api_key'));
        $apiSecret = env('CLOUDINARY_API_SECRET', config('cloudinary.api_secret'));

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->cloudinary = new Cloudinary();
    }

    public function upload(UploadedFile $file): array
    {
        $result = $this->cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder'         => 'dam-platform',
                'resource_type'  => 'auto',
                'use_filename'   => false,
                'unique_filename'=> true,
            ]
        );

        return [
            'public_id' => $result['public_id'],
            'url'       => $result['secure_url'],
            'format'    => $result['format'],
        ];
    }

    public function delete(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}