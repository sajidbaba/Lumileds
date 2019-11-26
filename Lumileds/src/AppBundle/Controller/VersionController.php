<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Version;
use AppBundle\Services\Export\SheetExporterService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Rest\Route("/version")
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class VersionController extends Controller
{
    /**
     * Lists all versions.
     *
     * @Rest\Get("/", name="version.list")
     *
     * @Rest\QueryParam(name="cycle", requirements="\d+")
     *
     * @Template()
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     */
    public function listAction(ParamFetcherInterface $paramFetcher): array
    {
        $onlyCycle = $paramFetcher->get('cycle');

        $em = $this->getDoctrine()->getManager();
        $versions = $em->getRepository(Version::class)->getOrdered($onlyCycle);

        return [
            'versions' => $versions,
            'onlyCycle' => $onlyCycle,
        ];
    }

    /**
     * Display versioned table
     *
     * @Rest\Get("/{id}", name="version.view", requirements={"id"="\d+"})
     * @Template()
     *
     * @param Version $version
     *
     * @return array
     */
    public function viewAction(Version $version): array
    {
        $lastVersionId = $this->getDoctrine()->getRepository(Version::class)->getLastVersionId();

        return [
            'version' => $version,
            'lastVersionId' => $lastVersionId,
        ];
    }

    /**
     * Export versioned table
     *
     * @Rest\Get("/export/{id}", name="version.export", requirements={"id"="\d+"})
     *
     * @param int $id
     *
     * @return StreamedResponse
     */
    public function exportAction(int $id): StreamedResponse
    {
        /** @var SheetExporterService $sheetExport */
        $sheetExport = $this->get(SheetExporterService::class);
        $sheetExport->setVersion($id);
        $response = $sheetExport->handleExport();

        return $response;
    }

    /**
     * Deletes a version entity.
     *
     * @Rest\Get("/delete/{id}", name="version.delete", requirements={"id"="\d+"})
     *
     * @param Version $version
     *
     * @return RedirectResponse
     */
    public function deleteAction(Version $version): RedirectResponse
    {
        $this->addFlash('success', 'version.flash.deleted');

        $em = $this->getDoctrine()->getManager();
        $em->remove($version);
        $em->flush();

        return $this->redirectToRoute('version.list');
    }
}
