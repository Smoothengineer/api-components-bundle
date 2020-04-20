<?php

/*
 * This file is part of the Silverback API Component Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Silverback\ApiComponentBundle\Form\Type\User;

use Silverback\ApiComponentBundle\Entity\User\AbstractUser;
use Silverback\ApiComponentBundle\Entity\User\UserInterface;
use Silverback\ApiComponentBundle\Exception\InvalidArgumentException;
use Silverback\ApiComponentBundle\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ChangePasswordType extends AbstractType
{
    private Security $security;
    private string $userClass;

    public function __construct(Security $security, string $userClass)
    {
        $this->security = $security;
        $this->userClass = $userClass;
        if (!is_subclass_of($this->userClass, UserInterface::class)) {
            throw new InvalidArgumentException(sprintf('The user class `%s` provided to the form `%s` must extend `%s`', $this->userClass, __CLASS__, AbstractUser::class));
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var UserInterface|null $user */
        $user = $builder->getEmptyData();

        $builder
            ->add('username', HiddenType::class, [
                'attr' => ['autocomplete' => 'username'],
                'data' => $user ? $user->getUsername() : null,
                'disabled' => true,
            ])
            ->add('oldPassword', PasswordType::class, [
                'label' => 'Current password',
                'attr' => ['autocomplete' => 'current-password'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The passwords you entered are not the same',
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
            'data_class' => $this->userClass,
            'validation_groups' => ['change_password'],
            'empty_data' => $this->security->getUser(),
        ]);
    }
}
