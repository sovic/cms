<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Cms\Email\EmailManager;
use Sovic\Cms\Entity\Email;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class EmailController extends AdminBaseController
{
    use JsonResponseTrait;

    #[Route(
        '/admin/api/web/email/{id}/test',
        name: 'admin:api:web:email:send-test',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function test(
        int                    $id,
        EmailManager           $emailManager,
        EntityManagerInterface $em,
        TranslatorInterface    $t,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $email = $em->getRepository(Email::class)->find($id);
        if (!$email) {
            $this->data['message'] = $t->trans('api.not_found', domain: 'email');

            return $this->sendFail(404);
        }

        $emailTo = $this->getUser()->getUserIdentifier();

        try {
            $result = $emailManager->sendTest($email, $emailTo);
        } catch (Throwable $e) {
            return $this->sendError($e->getMessage());
        }

        if (!$result) {
            $this->data['message'] = $t->trans('api.test_failed', domain: 'email');

            return $this->sendFail();
        }

        $this->data['message'] = $t->trans('api.test_sent', ['email_to' => $emailTo], 'email');

        return $this->sendSuccess();
    }
}
