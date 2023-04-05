<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Service\Enum\CommentState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly SluggerInterface $slugger
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $amsterdam = new Conference();
        $amsterdam
            ->setCity('Amsterdam')
            ->setYear('2019')
            ->setIsInternational(true)
            ->computeSlug($this->slugger);
        $manager->persist($amsterdam);

        $paris = new Conference();
        $paris->setCity('Paris')->setYear('2020')->setIsInternational(false);
        $manager->persist($paris);

        $comment1 = new Comment();
        $comment1
            ->setConference($amsterdam)
            ->setAuthor('Mykyta')
            ->setEmail('mykyta@example.com')
            ->setState(CommentState::PUBLISHED)
            ->setText('This was a great conference.')
        ;
        $manager->persist($comment1);

        $comment2 = new Comment();
        $comment2
            ->setConference($amsterdam)
            ->setAuthor('Lucas')
            ->setEmail('lucas@example.com')
            ->setText('I think this one is going to be moderated.')
        ;
        $manager->persist($comment2);

        $admin = new Admin();
        $admin
            ->setRoles(['ROLE_ADMIN'])
            ->setUsername('admin')
            ->setPassword($this->passwordHasherFactory->getPasswordHasher(Admin::class)->hash('admin'));
        $manager->persist($admin);

        $manager->flush();
    }
}
