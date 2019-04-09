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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\UploaderHelper;
use App\Service\FileNamingHelper;

/**
 * Admin controller.
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

    /**
     *
     * @Route("/register", name="register", methods={"GET", "POST"})
     * @Template()
     */
    public function registerAction(Request $request, UploaderHelper $uploaderHelper, FileNamingHelper $fileNamingHelper)
    {
        if (is_null($image = $uploaderHelper->getUploadedImageToProcess())) {
            $this->addFlash('error', 'nessun image to register');
            return $this->redirectToRoute('home');
        }
        // TODO: if !image ?
        $artistList = $uploaderHelper->getArtistList();
        $defaultData = $fileNamingHelper->getDataFromFilename($image['filename'], $artistList);
        $form = $this->createFormBuilder($defaultData)
            ->add('artist', ChoiceType::class, array(
                'placeholder' => 'Choose an artist...',
                'choices' => $artistList,
                'required' => false))
            ->add('new_artist', TextType::class, array(
                'required' => false))
            ->add('title', TextType::class, array(
                'required' => false))
            ->add('date', TextType::class, array(
                'required' => false))
            ->add('dimensions', TextType::class, array(
                'label' => 'Dimensions (cm)',
                'required' => false))
            ->add('technique', ChoiceType::class, array(
                'placeholder' => 'Choose a technique...',
                'choices' => array_flip($fileNamingHelper->getTechniques()),
                'required' => false))
            ->add('new_technique', TextType::class, array(
                'required' => false))
            ->add('place', TextType::class, array(
                'required' => false))
            ->add('delete_file', CheckboxType::class, array(
                'label'    => 'Delete this file?',
                'required' => false))
            ->add('Save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $filename = $fileNamingHelper->buildFilename($image['filename'], $image['extension'], $data);
            $dir = $fileNamingHelper->getDirectory($filename);
            $error = '';
            try {
                $newFilename = $uploaderHelper->saveImageWithName($image, $dir, $filename);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('register');
        }

        return array(
            'form' => $form->createView(),
            'image' => $image,
            'image_url' => $uploaderHelper->getPublicPath($image['basename']),
            'artist_dir' => $uploaderHelper->getArtistDir()
        );
    }
}
