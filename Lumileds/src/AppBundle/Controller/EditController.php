<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CellError;
use AppBundle\Entity\SavedFilter;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Rest\Route("edit")
 */
class EditController extends Controller
{
    /**
     * @Rest\Get("", name="edit")
     *
     * @param EntityManagerInterface $em
     *
     * @Template()
     */
    public function editAction(EntityManagerInterface $em): array
    {
        return [
            'errors' => $em->getRepository(CellError::class)->findUploadErrorMessages(),
            'savedFilter' => $em->getRepository(SavedFilter::class)->findOneBy(['user' => $this->getUser()]),
        ];
    }


}
