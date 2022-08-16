<?php

// TODO
/** @noinspection PhpRouteMissingInspection */
/** @noinspection PhpTemplateMissingInspection */
/** @noinspection PhpTranslationKeyInspection */

namespace SovicCms\Controller;

use SovicCms\Email\EmailManager;
use SovicCms\Form\Type\ForgotPassword;
use SovicCms\Form\Type\NewPassword;
use SovicCms\Form\Type\SignIn;
use SovicCms\Form\Type\SignUp;
use SovicCms\User\UserFactory;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use LogicException;
use SovicCms\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/user", name="user_")
 */
class UserController extends FrontendController
{
    /**
     * @Route("/signin", name="sign_in")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function signIn(AuthenticationUtils $authenticationUtils, TranslatorInterface $translator): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('user_files_index');
        }

        // get the login error if there is one
        $formError = null;
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $formError = $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security');
        }
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $signInForm = $this->createForm(SignIn::class);
        if ($lastUsername) {
            $signInForm->setData(['email' => $lastUsername]);
        }

        $this->assign('form_error', $formError);
        $this->assign('last_username', $lastUsername);
        $this->assign('sign_in_form', $signInForm->createView());

        return $this->render('page/user/sign-in.html.twig');
    }

    /**
     * @Route("/signup", name="sign_up")
     *
     * @param EmailManager $emailManager
     * @param ManagerRegistry $registry
     * @param Request $request
     * @param UserFactory $userFactory
     * @param UserPasswordHasherInterface $passwordHasher
     * @param TranslatorInterface $translator
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function signup(
        EmailManager                $emailManager,
        ManagerRegistry             $registry,
        Request                     $request,
        UserFactory                 $userFactory,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface         $translator
    ): Response {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('user_files_index');
        }

        $form = $this->createForm(SignUp::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->assign('sign_up_form', $form->createView());

            return $this->render('page/user/sign-up.html.twig');
        }

        $data = $form->getData();
        $email = $data['email'];
        if ($userFactory->loadByEmail($email)) {
            $this->addFlash('error', $translator->trans('user.sign_up.email_exists'));

            return $this->redirectToRoute('user_sign_up');
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername(explode('@', $email, 2)[0]);
        $user->setCreatedDate(new DateTimeImmutable());
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $em = $registry->getManager();
        $em->persist($user);
        $em->flush();
        // send activation email
        $user = $userFactory->loadByEmail($data['email']);
        $emailManager->send($user->getRegistrationEmail());
        // set flash message
        $this->addFlash('success', $translator->trans('user.sign_up.success'));

        return $this->redirectToRoute('user_sign_up');
    }

    /**
     * @Route("/activate/{code}", name="activate", requirements={"code": "[A-Za-z0-9]{32}"})
     *
     * @param string $code
     * @param UserFactory $userFactory
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function activate(
        string              $code,
        UserFactory         $userFactory,
        TranslatorInterface $translator
    ): Response {
        $user = $userFactory->loadByActivationCode($code);
        if (null === $user) {
            $this->addFlash('error', $translator->trans('user.activation.invalid_code'));

            return $this->render('page/homepage.index.html.twig');
        }
        $user->activate();
        $this->addFlash('success', $translator->trans('user.activation.activated'));

        return $this->render('page/homepage.index.html.twig');
    }

    /**
     * @Route("/forgot-password", name="forgot_password")
     *
     * @param EmailManager $emailManager
     * @param Request $request
     * @param UserFactory $userFactory
     * @param TranslatorInterface $translator
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function forgotPassword(
        EmailManager        $emailManager,
        Request             $request,
        UserFactory         $userFactory,
        TranslatorInterface $translator
    ): Response {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('user_files_index');
        }
        $form = $this->createForm(ForgotPassword::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userFactory->loadByEmail($data['email']);
            if ($user) {
                $emailManager->send($user->getForgotPasswordEmail());
                // set flash message
                $this->addFlash('success', $translator->trans('user.forgot_password.success'));

                return $this->redirectToRoute('user_forgot_password');
            }
            $this->assign('form_error', $translator->trans('user.forgot_password.invalid_email'));
        }
        $this->assign('forgot_password_form', $form->createView());

        return $this->render('page/user/forgot-password.html.twig');
    }

    /**
     * @Route("/new-password/{code}", name="new_password")
     *
     * @param string $code
     * @param Request $request
     * @param UserFactory $userFactory
     * @param TranslatorInterface $translator
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    public function newPassword(
        string                      $code,
        Request                     $request,
        UserFactory                 $userFactory,
        TranslatorInterface         $translator,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('user_files_index');
        }
        $user = $userFactory->loadByForgotPasswordCode($code);
        if (null === $user) {
            $this->assign('form_error', $translator->trans('user.forgot_password.invalid_code'));

            return $this->render('page/user/forgot-password.html.twig');
        }

        $form = $this->createForm(NewPassword::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user->getEntity()->setPassword($passwordHasher->hashPassword($user->getEntity(), $data['password']));
            $user->getEntity()->setForgotPasswordCode(null);
            $user->activate();
            // set flash message
            $this->addFlash('success', $translator->trans('user.new_password.success'));

            return $this->redirectToRoute('user_sign_in');
        }

        $this->assign('new_password_form', $form->createView());

        return $this->render('page/user/new-password.html.twig');
    }

    /**
     * @Route("/logout", name="logout", methods={"GET"})
     * @throws Exception
     */
    public function logout(): void
    {
        // controller can be blank: it will never be executed!
        throw new LogicException('Don\'t forget to activate logout in security.yaml');
    }
}
