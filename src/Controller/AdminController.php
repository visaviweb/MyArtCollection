<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;
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
     * @Route("/upload", name="upload", methods={"GET", "POST"})
     * @Template()
     */
    public function uploadAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $form = $this->createFormBuilder()
            ->add('imageFile', FileType::class, [
                'constraints' => [
                    new Image(['maxSize' => '20M']),
                    new NotNull()
                ]
            ])
            ->add('save', SubmitType::class, ['label' => 'Upload'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->moveUploadedImage($uploadedFile);
            }
        }
        return  array(
            'form' => $form->createView(),
            'has_unregistered_images' => $uploaderHelper->hasUnregisteredImages(),
        );
    }
}
