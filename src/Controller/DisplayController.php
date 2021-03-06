<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;
use App\Service\UploaderHelper;

/**
 * Display controller.
 *
 * @Route("/")
 */
class DisplayController extends AbstractController
{

    /**
     *
     * @Route("/", name="home", methods={"GET"})
     * @Template()
     */
    public function indexAction(Request $request, UploaderHelper $uploaderHelper)
    {
        return array('artists' => $uploaderHelper->getArtistNameList());
    }

    /**
     *
     * @Route("/artist/{artist}", name="show_artist", methods={"GET"})
     * @Template()
     */
    public function showArtistAction(string $artist, UploaderHelper $uploaderHelper)
    {
        return array(
            'artist' => $artist,
            'works' => $uploaderHelper->getAllImages($artist)
        );
    }
}
