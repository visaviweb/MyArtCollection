<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\UploaderHelper;

/**
 * Default controller.
 *
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     *
     * @Route("/upload", name="upload", methods={"GET"})
     * @Template()
     */
    public function uploadAction(UploaderHelper $uploaderHelper)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('do_upload'))
            ->getForm();
        return  array(
            'form' => $form->createView(),
            'has_unregistered_images' => $uploaderHelper->hasUnregisteredImages(),
        );
    }
    /**
     *
     * @Route("/doupload", name="do_upload", methods={"POST"})
     */
    public function doUploadAction(Request $request, UploaderHelper $uploaderHelper, ValidatorInterface $validator)
    {
        $uploadedFile = $request->files->get('imageFile');
        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload'
                ]),
                new Image([
                    'maxSize' => '20M'
                ])
            ]
        );
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }
        try {
            $filename = $uploaderHelper->moveUploadedImage($uploadedFile);
        } catch (\Exception $e) {
            return $this->json(array('detail' => $e->getMessage()), 400);
        }
        return $this->json($filename, 201);
    }

}
