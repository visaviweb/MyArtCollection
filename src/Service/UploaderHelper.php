<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Asset\Context\RequestStackContext;

class UploaderHelper
{
    private $uploadBasePath;
    private $requestStackContext;
    const UPLOAD_DIR = 'upload';
    
    public function __construct(string $uploadBasePath, RequestStackContext $requestStackContext)
    {
        $this->uploadBasePath = $uploadBasePath;
        $this->requestStackContext = $requestStackContext;
    }
    
    public function moveUploadedImage(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename.'_U_'.uniqid().'_.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $this->getUploadPath(),
            $newFilename
        );
        return $newFilename;
    }

    public function getPublicPath(string $filename) : string
    {
        return $this->requestStackContext
            ->getBasePath().'/'.self::UPLOAD_DIR.'/'.$filename;
    }

    public function getUploadedFilesList()
    {
        $finder = new Finder();
        return $finder->files()->in($this->getUploadPath());
    }

    public function hasUnregisteredImages() : boolean
    {
        $finder = new Finder();
        return \count($finder->files()->in($this->getUploadPath())) > 0;
    }

    private function getUploadPath() : string
    {
        return $this->uploadBasePath.'/'.self::UPLOAD_DIR;
    }
}
