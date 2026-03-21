<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new Users();
        $user->setEmail('test@example.local')
             ->setPlainPassword('Test1234!')
             ->setPseudo('TestUser');
        $manager->persist($user);
        $manager->flush();
    }
}
