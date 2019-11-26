<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Form\User\UserProfileType;
use AppBundle\Form\User\UserType;
use AppBundle\Services\MailerService;
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
 * @Route("users")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Rest\Get("/", name="users.list")
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listAction(): array
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return [
            'users' => $users,
        ];
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="users.create")
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $mailerService = $this->get(MailerService::class);

        $contributorGroup = $this->getDoctrine()->getRepository(Group::class)->findContributorGroup();

        $user = new User();
        $user->setEnabled(true);

        $form = $this->createForm(UserType::class, $user, ['is_create' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $user->getPlainPassword();
            $userManager->updateUser($user);

            $mailerService->sendUserCreationConfirmation($user, $password);

            $this->addFlash('success', 'user.flash.created');
            return $this->redirectToRoute('users.list');
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
            'contributorGroup' => $contributorGroup,
        ];
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="users.edit", requirements={"id"="\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param User    $user
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, User $user)
    {
        $userManager = $this->get('fos_user.user_manager');
        $contributorGroup = $this->getDoctrine()->getRepository(Group::class)->findContributorGroup();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $this->addFlash('success', 'user.flash.edited');
            return $this->redirectToRoute('users.edit', ['id' => $user->getId()]);
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
            'contributorGroup' => $contributorGroup,
        ];
    }

    /**
     * Displays a edit for current user
     *
     * @Route("/profile", name="users.profile")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function profileAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $this->getUser();

        $editForm = $this->createForm(UserProfileType::class, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userManager->updateUser($user);

            $this->addFlash('success', 'user.flash.edited');
            return $this->redirectToRoute('users.profile', ['id' => $user->getId()]);
        }

        return [
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ];
    }
}
