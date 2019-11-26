<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Country;
use AppBundle\Entity\Region;
use AppBundle\Form\Region\RegionType;
use AppBundle\Services\RegionService;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("regions")
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class RegionController extends Controller
{
    /**
     * Lists all region entities.
     *
     * @Rest\Get("/", name="regions.list")
     * @Template()
     */
    public function listAction(): array
    {
        $regions = $this->getDoctrine()->getRepository(Region::class)->getOrderedByName();

        return [
            'regions' => $regions,
        ];
    }

    /**
     * Creates a new region entity.
     *
     * @Route("/new", name="regions.create")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $region = new Region();
        $originalCountries = new ArrayCollection();

        $form = $this->createForm(RegionType::class, $region);
        $regionService = $this->get(RegionService::class);

        if ($regionService->handle($form, $request, $originalCountries)) {
            $this->addFlash('success', 'region.flash.created');

            return $this->redirectToRoute('regions.list');
        }

        return [
            'region' => $region,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing region entity.
     *
     * @Route("/{id}/edit", name="regions.edit", requirements={"id"="\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     * @param Region $region
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, Region $region)
    {
        /** @var Country[]|ArrayCollection $originalCountries */
        $originalCountries = new ArrayCollection();
        foreach ($region->getCountries() as $country) {
            $originalCountries->add($country);
        }

        $deleteForm = $this->getDeleteForm($region);
        $editForm = $this->createForm(RegionType::class, $region);

        $regionService = $this->get(RegionService::class);

        if ($regionService->handle($editForm, $request, $originalCountries)) {
            $this->addFlash('success', 'region.flash.edited');

            return $this->redirectToRoute('regions.list');
        }

        return [
            'region' => $region,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a region entity.
     *
     * @Rest\Delete("/{id}", name="regions.delete", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param Region    $region
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Region $region): RedirectResponse
    {
        $form = $this->getDeleteForm($region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'region.flash.deleted');

            $em = $this->getDoctrine()->getManager();
            $em->remove($region);
            $em->flush();
        }

        return $this->redirectToRoute('regions.list');
    }

    /**
     * Creates a form to delete a region entity.
     *
     * @param Region $region The region entity
     *
     * @return FormInterface
     */
    private function getDeleteForm(Region $region): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('regions.delete', ['id' => $region->getId()]))
            ->setMethod('DELETE')
            ->getForm();
    }
}
