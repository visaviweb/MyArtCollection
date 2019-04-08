<?php

namespace App\Service;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    private $filesystem;
    private $requestStackContext;
    private $publicAssetBaseUrl;
    private $uploadDir;

    public function __construct(FilesystemInterface $uploadFilesystem, RequestStackContext $requestStackContext, string $uploadDirectory, string $uploadedAssetsBaseUrl)
    {
        $this->filesystem = $uploadFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
        $this->uploadDir = $uploadDirectory;
    }

    public function moveUploadedImage(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $this->sanitizeName($originalFilename . '_U_' . uniqid() . '_.' . $uploadedFile->guessExtension());
        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->filesystem->writeStream(
            $this->uploadDir . '/' . $newFilename,
            $stream,
            ['visibility' => 'public']
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($result === false) {
            throw new \Exception(sprintf('Could not save uploaded file "%s"', $newFilename));
        }
        return $newFilename;
    }

    public function getPublicPath(string $filename): string
    {
        $fullPath = $this->publicAssetBaseUrl . '/' . $this->uploadDir . '/' . $filename;
        // is it adsolute url?
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }
        return $this->requestStackContext
            ->getBasePath() . $fullPath;
    }

    public function getUploadedFilesList()
    {
        return $this->filesystem->listContents($this->uploadDir, false);
    }

    public function hasUnregisteredImages(): bool
    {
        return \count($this->filesystem->listContents($this->uploadDir, false)) > 0;
    }

    public function sanitizeName(string $filename): string
    {
        $replace = array(":", "/", "?", "#", "[", "]", "@");
        return str_replace($replace, '-', $filename);
    }
}
