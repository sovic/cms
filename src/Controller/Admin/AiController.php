<?php

namespace Sovic\Cms\Controller\Admin;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Ai\ApiKeyEncryptor;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Form\Admin\AiSettingsForm;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use UserBundle\User\UserEntityInterface;

class AiController extends AdminBaseController
{
    #[Route(
        '/admin/ai/settings',
        name: 'admin:ai:settings',
    )]
    public function settings(
        EntityManagerInterface                 $em,
        ApiKeyEncryptor                        $encryptor,
        Request                                $request,
        TranslatorInterface                    $t,
        #[Autowire('%ai_model%')] string       $defaultModel,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();
        if (!$user instanceof UserEntityInterface) {
            throw $this->createAccessDeniedException();
        }

        /** @var UserAiSettingRepository $repo */
        $repo = $em->getRepository(UserAiSetting::class);
        $setting = $repo->findOneByUser($user) ?? new UserAiSetting($user);

        $form = $this->createForm(
            AiSettingsForm::class,
            ['model' => $setting->getModel()],
            ['default_model' => $defaultModel],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();

                $apiKey = trim((string) ($data['api_key'] ?? ''));
                if (!empty($data['remove_key'])) {
                    $setting->setApiKeyEncrypted(null);
                    $setting->setApiKeyHint(null);
                } elseif ($apiKey !== '') {
                    $setting->setApiKeyEncrypted($encryptor->encrypt($apiKey));
                    $setting->setApiKeyHint(ApiKeyEncryptor::hint($apiKey));
                }

                $model = trim((string) ($data['model'] ?? ''));
                $setting->setModel($model !== '' ? $model : null);
                $setting->setUpdatedAt(new DateTimeImmutable());

                $em->persist($setting);
                $em->flush();

                try {
                    $this->addFlash('success', $t->trans('flash.saved', domain: 'ai'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:ai:settings');
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: 'ai'));
            } catch (Throwable) {
            }
        }

        $this->assign('default_model', $defaultModel);
        $this->assign('form', $form->createView());
        $this->assign('has_api_key', $setting->hasApiKey());
        $this->assign('key_hint', $setting->getApiKeyHint());

        return $this->render('@CmsBundle/admin/ai/settings.html.twig');
    }
}
