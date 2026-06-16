<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\S3StorageHelper;
use Illuminate\Console\Command;
use Throwable;

class MakeS3BucketPublic extends Command
{
    protected $signature = 'storage:s3-public';

    protected $description = 'Make the S3/MinIO bucket and all objects publicly readable';

    public function handle(): int
    {
        try {
            $result = S3StorageHelper::preparePublicBucket();

            $this->info("Bucket \"{$result['bucket']}\" is now public.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to make S3 bucket public: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
