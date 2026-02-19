<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\ControllerAccessTrait;
use Sovic\Cms\Email\EmailSettingsInterface;
use Sovic\Cms\Email\EmailSearchRequest;
use Sovic\Cms\Entity\Email;
use Sovic\Cms\Repository\EmailRepository;
use Sovic\Common\DataList\Enum\VisibilityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

trait EmailControllerTrait
{
    use ControllerAccessTrait;

    protected function isAttributeGranted(string $attribute): bool
    {
        if (in_array($attribute, ['admin:email:list', 'admin:email:edit'])) {
            return $this->isGranted('ROLE_ADMIN');
        }

        return false;
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function isAdminEmailEditGranted(Email $email): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    protected function isAdminEmailListGranted(): bool
    {
        return $this->isGranted('ROLE_ADMIN');
    }

    #[Route(
        '/admin/email/list',
        name: 'admin:email:list',
    )]
    public function email(
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->getRouteAccessDecision('admin:email:list');

        if (!$this->isAdminEmailListGranted()) {
            return $this->render404();
        }

        $page = max(1, (int) $request->query->get('page', 1));

        $sr = new EmailSearchRequest();
        $sr->setVisibilityId(VisibilityId::Public);
        if (!$this->isAdminEmailListGranted()) {
            $sr->setUser($this->getUser());
        }
        $sr->setPage($page);
        $sr->setPaginationRoute('admin:email:list');

        /** @var EmailRepository $repo */
        $repo = $em->getRepository(Email::class);
        $emails = $repo->findBySearchRequest($sr);
        $total = $repo->countBySearchRequest($sr);
        $pagination = $sr->getPagination($total);

        $this->assign('emails', $emails);
        $this->assign('pagination', $pagination);
        $this->assign('query', $sr->toArray());

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
        EmailSettingsInterface $emailList,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->getRouteAccessDecision('admin:email:edit');

        $repo = $em->getRepository(Email::class);
        $email = $repo->find($id);

        if ($email === null) {
            $email = new Email();
        }

        if (!$this->isAdminEmailEditGranted($email)) {
            return $this->render404();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Email::class, $email, ['email_list' => $emailList]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // $email->setEmailId($form->get('emailId')->getData()?->getId());
                $operator = $this->getUser();
                if (!$email->getCreator()) {
                    $email->setCreator($operator);
                }

                $em->persist($email);
                $em->flush();

                $this->addFlash('success', 'Email byl uložen.');

                return $this->redirectToRoute('admin:email:edit', ['id' => $email->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $emailId = $email->getEmailId();

        $this->assign('email', $email);
        $this->assign('form', $form->createView());
        $this->assign('variables', $emailId ? $emailList->getVariablesForEmailId($emailId) : []);

        return $this->render('@CmsBundle/admin/email/edit.html.twig');
    }
}
