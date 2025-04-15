<?php

namespace App\Services;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;

class GoogleCloudStorageService
{
    protected $bucket;
    protected $storage;
    protected $bucketName;

    public function __construct()
    {
        $keyFilePath = config('filesystems.disks.gcs.key_file');
        $projectId = config('filesystems.disks.gcs.project_id');
        $bucketName = config('filesystems.disks.gcs.bucket');
        $cacertPath = storage_path('app/cacert.pem');

        try {
            $this->storage = new StorageClient([
                'projectId' => $projectId,
                'keyFilePath' => $keyFilePath,
                'httpOptions' => [
                    'verify' => $cacertPath
                ]
            ]);
            
            $this->bucket = $this->storage->bucket($bucketName);
            $this->bucketName = $bucketName;
        } catch (\Exception $e) {
            Log::error('Failed to initialize GCS: ' . $e->getMessage());
            throw $e;
        }
    }

    public function put($path, $contents, $options = [])
    {
        try {
            $object = $this->bucket->upload($contents, [
                'name' => $path,
                'metadata' => $options['metadata'] ?? []
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to upload to GCS: ' . $e->getMessage());
            return false;
        }
    }

    public function get($path)
    {
        try {
            $object = $this->bucket->object($path);
            return $object->downloadAsString();
        } catch (\Exception $e) {
            Log::error('Failed to download from GCS: ' . $e->getMessage());
            return null;
        }
    }

    public function exists($path)
    {
        try {
            $object = $this->bucket->object($path);
            return $object->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($path)
    {
        try {
            $object = $this->bucket->object($path);
            $object->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete from GCS: ' . $e->getMessage());
            return false;
        }
    }

    public function url($path)
    {
        return 'https://storage.googleapis.com/' . $this->bucketName . '/' . $path;
    }

    public function signedUrl($path, $expiration = null)
    {
        try {
            $object = $this->bucket->object($path);
            return $object->signedUrl(
                $expiration ?? new \DateTime('tomorrow')
            );
        } catch (\Exception $e) {
            Log::error('Failed to create signed URL: ' . $e->getMessage());
            return $this->url($path);
        }
    }
}