<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends CrudController<User>
 */
final class UserCrudController extends CrudController
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email')
                ->setFormTypeOption('empty_data', ''),
            TextField::new('plainPassword')
                ->setFormType(PasswordType::class)
                ->setFormTypeOption('empty_data', '')
                ->setFormTypeOption('required', true)
                ->setFormTypeOption('constraints', [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        min: 8,
                        max: 255,
                        minMessage: 'Heslo musí mať aspoň {{ limit }} znakov.',
                    ),
                ])
                ->onlyWhenCreating(),
            ChoiceField::new('roles', 'Role')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Používateľ' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges(),
        ];
    }

    /**
     * @param User $entityInstance
     */
    #[Override]
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $plainPassword = $entityInstance->getPlainPassword();
        if (is_string($plainPassword)) {
            $passwordHasher = $this->hasherFactory->getPasswordHasher($entityInstance);
            $entityInstance->setPassword($passwordHasher->hash($plainPassword));
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
