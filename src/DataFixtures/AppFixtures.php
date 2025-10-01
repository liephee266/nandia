<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // {
        //     "email": "lieloumloum@gmail.com",
        //     "plainPassword": "L2V2nw[x=K!86q",
        //     "pseudo": "Orphée Lié"
        // }
        $user = new \App\Entity\Users();
        $user->setEmail('lieloumloum@gmail.com')
             ->setPassword(password_hash('L2V2nw[x=K!86q', PASSWORD_BCRYPT))
             ->setPseudo('Orphée Lié');
        $manager->persist($user);
        $manager->flush();
    }
}
