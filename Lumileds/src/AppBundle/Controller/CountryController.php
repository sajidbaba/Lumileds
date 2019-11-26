<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Country;
use AppBundle\Form\Country\CountryType;
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
 * @Rest\Route("country")
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class CountryController extends Controller
{
    /**
     * Lists all country entities.
     *
     * @Rest\Get("/", name="countries.list")
     * @Template()
     */
    public function listAction(): array
    {
        $countries = $this->getDoctrine()->getRepository(Country::class)->getOrderedByName();

        return [
            'countries' => $countries,
        ];
    }

    /**
     * Creates a new country entity.
     *
     * @Route("/new", name="countries.create")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $country = new Country();

        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->addFlash('success', 'country.flash.created');

            $em = $this->getDoctrine()->getManager();
            $em->persist($country);
            $em->flush();

            return $this->redirectToRoute('countries.list');
        }

        return [
            'country' => $country,
            'form' => $form->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing country entity.
     *
     * @Route("/{id}/edit", name="countries.edit", requirements={"id"="\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     * @param Country $country
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, Country $country)
    {
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->addFlash('success', 'country.flash.edited');

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('countries.list');
        }

        return [
            'country' => $country,
            'form' => $form->createView(),
        ];
    }
}
