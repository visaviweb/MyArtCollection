<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Asset\Context\RequestStackContext;
use League\Flysystem\FilesystemInterface;

class UploaderHelper
{
    private $filesystem;
    private $requestStackContext;
    private $publicAssetBaseUrl;
    
    public function __construct(FilesystemInterface $uploadFilesystem, RequestStackContext $requestStackContext, string $uploadedAssetsBaseUrl )
    {
        $this->filesystem = $uploadFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->publicAssetBaseUrl = $uploadedAssetsBaseUrl;
    }
    
    public function moveUploadedImage(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename.'_U_'.uniqid().'_.'.$uploadedFile->guessExtension();
        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->filesystem->writeStream(
            $newFilename,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($result === false) {
            throw new \Exception(sprintf('Could not write uploaded file "%s"', $newFilename));
        }
        
        return $newFilename;
    }

    public function getPublicPath(string $filename) : string
    {
        return $this->requestStackContext
            ->getBasePath().$this->publicAssetBaseUrl.'/'.$filename;
    }

    public function getUploadedFilesList()
    {
        
        return $this->filesystem->listContents('', false);
    }

    public function hasUnregisteredImages() : bool
    {
        return \count($this->filesystem->listContents('', false)) > 0;
    }
}
