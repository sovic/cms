<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Common\Controller\Trait\JsonRequestTrait;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Sovic\Common\Entity\Setting;
use Sovic\Common\Enum\SettingTypeId;
use Sovic\Common\Project\Settings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingController extends AdminBaseController
{
    use JsonRequestTrait;
    use JsonResponseTrait;

    #[Route(
        '/admin/api/web/setting/{id}/toggle-state',
        name: 'admin:api:web:setting:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
        Settings               $settings,
        TranslatorInterface    $t,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getRequestData($request);
        $setting = $em->getRepository(Setting::class)->find($id);
        if (!$setting) {
            $this->data['message'] = $t->trans('api.not_found', domain: 'settings');

            return $this->sendFail(404);
        }

        if (!isset($data['state']) || $setting->getType() !== SettingTypeId::Boolean) {
            $this->data['message'] = $t->trans('api.toggle_failed', domain: 'settings');

            return $this->sendFail();
        }

        $newState = (bool) $data['state'];
        $setting->setValue($newState ? '1' : '0');
        $em->persist($setting);
        $em->flush();

        $settings->invalidate();

        $this->data['value'] = $settings->get($setting->getGroupId()->value, $setting->getKey());

        return $this->sendSuccess();
    }
}
