<?php

namespace App\Twig;

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\UploaderHelper;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath'])
        ];
    }
    
    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploaderHelper::class)
            ->getPublicPath($path);
    }

    public static function getSubscribedServices()
    {
        return [
            UploaderHelper::class,
        ];
    }

}