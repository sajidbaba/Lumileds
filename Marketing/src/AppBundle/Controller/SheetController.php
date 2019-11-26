<?php

namespace AppBundle\Controller;

use AppBundle\Exception\UploadSheetException;
use AppBundle\Form\File\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class SheetController extends Controller
{
    /**
     * @Route("/upload", name="sheet.upload")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function uploadAction(Request $request)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);

        $form = $this->createForm(UploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            try {
                $sheetWorker = $this->get('sheet_worker');
                $queueHash = $sheetWorker->handleUpload($formData['file']);
            } catch (UploadSheetException $e) {
                return $this->redirectToRoute('edit');
            }

            return $this->redirectToRoute('sheet.upload.process', ['hash' => $queueHash]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/upload/{hash}", name="sheet.upload.process")
     * @Method("GET")
     * @Template()
     *
     * @param string $hash
     *
     * @return array
     */
    public function uploadProcessAction(string $hash)
    {
        return [
            'queueHash' => $hash,
        ];
    }
}
