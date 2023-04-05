<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Service\Enum\CommentState;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class CommentCrudController extends AbstractCrudController
{
    public const BASE_PATH = '/uploads/photos';
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Conference Comment')
            ->setEntityLabelInPlural('Conference Comments')
            ->setSearchFields(['author', 'text', 'email'])
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('conference'));
    }


    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('conference');
        yield TextField::new('author');
        yield EmailField::new('email');
        yield TextareaField::new('text')->hideOnIndex();
        yield ImageField::new('photoFilename')
            ->setBasePath(self::BASE_PATH)
            ->setLabel('Photo')
            ->onlyOnIndex()
        ;
        yield ChoiceField::new('state')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class'        => CommentState::class,
                'choice_label' => function (CommentState $choice, $key, $value) {
                    return $choice->label();
                },
                'choices'      => CommentState::cases(),
            ])
            ->formatValue(function ($value, Comment $entity) {
                return sprintf(
                    '<span class="badge bg-%s">%s</span>',
                    $entity->getState()->color(),
                    $entity->getState()->label()
                );
            })
            ->addCssClass('text-center')
        ;

        $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
            'html5' => true,
            'years' => range(date('Y'), (int)date('Y') + 5),
            'widget' => 'single_text',
        ]);

        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disable', true);
        } else {
            yield $createdAt;
        }
    }

}
