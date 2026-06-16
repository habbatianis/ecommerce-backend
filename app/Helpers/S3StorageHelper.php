<?php
declare(strict_types=1);

namespace App\Helpers;

use Aws\S3\S3Client;
use Throwable;

class S3StorageHelper
{
    public static function client(): S3Client
    {
        return new S3Client([
            'version'                 => 'latest',
            'region'                  => config('filesystems.disks.s3.region'),
            'endpoint'                => config('filesystems.disks.s3.endpoint'),
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint'),
            'credentials'             => [
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    public static function bucket(): string
    {
        return (string) config('filesystems.disks.s3.bucket');
    }

    public static function ensureBucketExists(S3Client $client, string $bucket): void
    {
        if (!$client->doesBucketExist($bucket)) {
            $client->createBucket(['Bucket' => $bucket]);
        }
    }

    public static function makeBucketPublic(S3Client $client, string $bucket): void
    {
        $policy = json_encode([
            'Version'   => '2012-10-17',
            'Statement' => [[
                'Effect'    => 'Allow',
                'Principal' => ['AWS' => ['*']],
                'Action'    => ['s3:GetObject'],
                'Resource'  => ["arn:aws:s3:::$bucket/*"],
            ]],
        ], JSON_THROW_ON_ERROR);

        $client->putBucketPolicy([
            'Bucket' => $bucket,
            'Policy' => $policy,
        ]);
    }

    public static function preparePublicBucket(): array
    {
        $bucket = self::bucket();
        $client = self::client();

        self::ensureBucketExists($client, $bucket);
        self::makeBucketPublic($client, $bucket);

        return [
            'bucket'  => $bucket,
            'objects' => 0,
        ];
    }

    public static function tryPreparePublicBucket(): ?array
    {
        try {
            return self::preparePublicBucket();
        } catch (Throwable) {
            return null;
        }
    }
}
