<?php

namespace App\Twig;

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\UploaderHelper;
use App\Service\FileNamingHelper;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('archived_asset', [$this, 'getArchivedAssetPath']),
            new TwigFunction('title_asset', [$this, 'getTitleInName']),
        ];
    }
    
    public function getTitleInName(string $name, int $length): string
    {
        return $this->container
            ->get(FileNamingHelper::class)
            ->getTitleInName($name, $length);
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getPublicPath($path);
    }

    public function getArchivedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getArchivedPublicPath($path);
    }

    public static function getSubscribedServices()
    {
        return [
            UploaderHelper::class,
            FileNamingHelper::class
        ];
    }
}
