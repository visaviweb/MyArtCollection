<?php

namespace App\Service;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    private $filesystem;
    private $requestStackContext;
    private $uploadedAssetsBaseUrl;
    private $uploadDir;
    private $artistDir;

    public function __construct(FilesystemInterface $uploadFilesystem, RequestStackContext $requestStackContext, string $uploadDirectory, string $uploadedAssetsBaseUrl, string $artistDir)
    {
        $this->filesystem = $uploadFilesystem;
        $this->requestStackContext = $requestStackContext;
        $this->uploadedAssetsBaseUrl = $uploadedAssetsBaseUrl;
        $this->uploadDir = $uploadDirectory;
        $this->artistDir = $artistDir;
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

    public function saveImageWithName(array $file, string $directory, string $filename)
    {
        $newFilename = $this->artistDir.'/'.$directory.'/'.$filename;
        if (!$this->filesystem->rename($file['path'], $newFilename)) {
            throw new \Exception(sprintf('Could not save file "%s" in "%s"', $newFilename, $directory));
        }
        return $newFilename;
    }

    public function getPublicPath(string $filename): string
    {
        $fullPath = $this->uploadedAssetsBaseUrl . '/' . $this->uploadDir . '/' . $filename;
        // is it adsolute url?
        if (strpos($fullPath, '://') !== false) {
            return $fullPath;
        }
        return $this->requestStackContext
            ->getBasePath() . $fullPath;
    }

    public function getArchivedPublicPath(string $filepath): string
    {
        $fullPath = $this->uploadedAssetsBaseUrl . '/' . $filepath;
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

    public function getAllImages(string $directory = '') : array
    {
        $all = $this->filesystem->listContents($this->artistDir, true);
        $images = array();
        foreach ($all as $file) {
            if ($file['type'] == 'file' && \preg_match('/(jpe?g|png|gif)$/i', $file['extension'])) {
                $images[] = $file;
            }
        }
        return $images;
    }

    public function hasUnregisteredImages(): bool
    {
        return \count($this->filesystem->listContents($this->uploadDir, false)) > 0;
    }

    public function sanitizeName(string $filename): string
    {
        $replace = array(":", "/", "?", "#", "[", "]", "@", '%');
        $with = array('-', '-', '-', '-', '(', ')', '-', '-');
        return str_replace($replace, $with, $filename);
    }

    public function getUploadedImageToProcess() : ?array
    {
        
        $list = $this->filesystem->listContents($this->uploadDir, false);
        foreach ($list as $file) {
            if (strpos($this->filesystem->getMimetype($file['path']), 'image') === 0) {
                return $file;
            }
        }
        return null;
    }

    public function getArtistList()
    {
        $list = array();
        $dirs = $this->filesystem->listContents($this->artistDir, false);
        foreach ($dirs as $dir) {
            if ($dir['type'] == 'dir') {
                $list[$dir['filename']] = $dir['filename'];
            }
        }
        return $list;
    }

    public function getArtistDir()
    {
            return $this->artistDir;
    }
}
