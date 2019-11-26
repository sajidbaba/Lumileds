<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Rest\Get("/", name="homepage")
     *
     * @Template()
     */
    public function indexAction(): array
    {
        $version = @substr(file_get_contents(__DIR__ . '/../../../.git/refs/heads/master'),0,7);

        return [
            'user' => $this->getUser(),
            'version' => $version,
        ];
    }
}
