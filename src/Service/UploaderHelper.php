<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Finder\Finder;

class UploaderHelper
{
    private $uploadPath;

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }
    
    public function moveUploadedImage(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename.'_U_'.uniqid().'_.'.$uploadedFile->guessExtension();
        $uploadedFile->move(
            $this->uploadPath,
            $newFilename
        );
        return $newFilename;
    }

    public function getUploadedFilesList()
    {
        $finder = new Finder();
        return $finder->files()->in($this->uploadPath);
    }

    public function hasUnregisteredImages()
    {
        $finder = new Finder();
        return \count($finder->files()->in($this->uploadPath)) > 0;
    }
}
