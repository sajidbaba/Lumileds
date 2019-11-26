<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Setting;
use AppBundle\Form\Setting\SettingsType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/setting")
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class SettingController extends Controller
{
    /**
     * @Rest\Route("/", name="setting.list")
     *
     * @Template()
     *
     * @param Request $request
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository(Setting::class)->findAll();

        $form = $this->createForm(SettingsType::class, ['settings' => $settings]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $em->flush();

            $this->addFlash('success', 'Settings have been saved!');

            return $this->redirectToRoute('setting.list');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
