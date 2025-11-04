<?php

namespace Sovic\Cms\Email;

use Sovic\Cms\Entity\Email;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractEmailManager implements EmailManagerInterface
{
    public function __construct(
        private readonly EmailListInterface   $emailList,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    public function createEmailEditForm(?Email $email = null): FormInterface
    {
        $email = $email ?? new Email();
        $options = [
            'email_list' => $this->emailList,
        ];

        return $this->formFactory->create(\Sovic\Cms\Form\Admin\Email::class, $email, $options);
    }
}
