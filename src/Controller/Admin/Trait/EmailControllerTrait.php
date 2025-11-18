<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Email\EmailListInterface;
use Sovic\Cms\Entity\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AccessDecision;

trait EmailControllerTrait
{
    protected function isAttributeGranted(string $attribute): bool
    {
        if (in_array($attribute, ['admin:email:list', 'admin:email:edit'])) {
            return $this->isGranted('ROLE_ADMIN');
        }

        return false;
    }

    protected function getEmailAccessDecision(string $attribute): void
    {
        $accessDecision = new AccessDecision();
        $accessDecision->isGranted = $this->isAttributeGranted($attribute);

        if (!$accessDecision->isGranted) {
            $e = $this->createAccessDeniedException($accessDecision->getMessage());
            $e->setAttributes([$attribute]);
            $e->setAccessDecision($accessDecision);

            throw $e;
        }
    }

    #[Route(
        '/admin/email/list',
        name: 'admin:email:list',
    )]
    public function email(
        EntityManagerInterface $em,
    ): Response {
        $this->getEmailAccessDecision('admin:email:list');

        $repo = $em->getRepository(Email::class);
        $emails = $repo->findBy([], ['id' => 'DESC']);

        $this->assign('emails', $emails);

        return $this->render('@CmsBundle/admin/email/list.html.twig');
    }

    #[Route(
        '/admin/email/edit/{id}',
        name: 'admin:email:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function detail(
        int                    $id,
        EmailListInterface     $emailList,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->getEmailAccessDecision('admin:email:edit');

        $repo = $em->getRepository(Email::class);
        $email = $repo->find($id);

        if ($email === null) {
            $email = new Email();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Email::class, $email, ['email_list' => $emailList]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email->setEmailId($form->get('emailId')->getData()?->getId());

                $em->persist($email);
                $em->flush();

                $this->addFlash('success', 'Email byl uložen.');

                return $this->redirectToRoute('admin:email:edit', ['id' => $email->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('email', $email);
        $this->assign('form', $form->createView());

        return $this->render('@CmsBundle/admin/email/edit.html.twig');
    }
}
