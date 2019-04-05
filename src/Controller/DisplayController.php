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
        return array('files' => $uploaderHelper->getUploadedFilesList());
    }
}
