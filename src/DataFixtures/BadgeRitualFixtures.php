<?php

namespace App\DataFixtures;

use App\Entity\Badge;
use App\Entity\Ritual;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixture pour les Badges et Rituals.
 * Exécuter après CardFixtures (dépend de Theme).
 */
class BadgeRitualFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CardFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // ── Badges ────────────────────────────────────────────────────────────

        $badges = [
            // Sessions
            ['slug' => Badge::SLUG_FIRST_SESSION,    'name' => 'Premier pas',        'description' => 'Complète ta première session.',                        'type' => 'sessions', 'threshold' => 1],
            ['slug' => Badge::SLUG_TEN_SESSIONS,     'name' => 'En route !',          'description' => 'Complète 10 sessions.',                                  'type' => 'sessions', 'threshold' => 10],
            ['slug' => Badge::SLUG_FIFTY_SESSIONS,   'name' => 'Passionné',           'description' => 'Complète 50 sessions.',                                  'type' => 'sessions', 'threshold' => 50],
            // Responses
            ['slug' => Badge::SLUG_FIRST_RESPONSE,   'name' => 'Premier jet',          'description' => 'Envoie ta première réponse.',                            'type' => 'responses', 'threshold' => 1],
            ['slug' => Badge::SLUG_TEN_RESPONSES,    'name' => 'En train d\'écrire',   'description' => 'Envoie 10 réponses.',                                    'type' => 'responses', 'threshold' => 10],
            ['slug' => Badge::SLUG_HUNDRED_RESPONSES,'name' => 'Romancier',            'description' => 'Envoie 100 réponses.',                                   'type' => 'responses', 'threshold' => 100],
            // Couple
            ['slug' => Badge::SLUG_COUPLE_JOINED,    'name' => 'En couple !',         'description' => 'Tu as rejoint ton partenaire.',                         'type' => 'couple',    'threshold' => 1],
            // Room
            ['slug' => Badge::SLUG_ROOM_HOST,         'name' => 'Hôte',                 'description' => 'Héberge une salle multi-couples.',                       'type' => 'room',      'threshold' => 1],
            // Streaks
            ['slug' => Badge::SLUG_STREAK_3,         'name' => '3 jours de suite',    'description' => 'Joue 3 jours consécutifs.',                             'type' => 'streak',    'threshold' => 3],
            ['slug' => Badge::SLUG_STREAK_7,         'name' => 'Semaine parfaite',     'description' => 'Joue 7 jours consécutifs.',                             'type' => 'streak',    'threshold' => 7],
            ['slug' => Badge::SLUG_STREAK_30,         'name' => 'Mois de feu',          'description' => 'Joue 30 jours consécutifs.',                          'type' => 'streak',    'threshold' => 30],
        ];

        foreach ($badges as $data) {
            $badge = new Badge();
            $badge->setSlug($data['slug']);
            $badge->setName($data['name']);
            $badge->setDescription($data['description']);
            $badge->setType($data['type']);
            $badge->setThreshold($data['threshold']);
            $manager->persist($badge);
        }

        // ── Rituals ──────────────────────────────────────────────────────────

        /** @var Theme|null $emotionsTheme */
        $emotionsTheme = $manager->getRepository(Theme::class)
            ->findOneBy(['name' => 'Émotions']);

        $rituals = [
            [
                'title'       => 'Check-in du soir',
                'description' => 'Chaque soir avant de dormir, partagez 3 choses : ce qui vous a rendu heureux/se aujourd\'hui, un moment difficile, et une chose pour laquelle vous êtes reconnaissant·e.',
                'type'        => 'daily',
                'theme'       => $emotionsTheme,
            ],
            [
                'title'       => 'Question du jour',
                'description' => 'Tirez une carte question chaque soir et répondez-y ensemble. Prenez le temps d\'écouter vraiment la réponse.',
                'type'        => 'daily',
                'theme'       => null,
            ],
            [
                'title'       => 'Câlin de réconciliation',
                'description' => 'Après une dispute, faites un câlin de 30 secondes avant de parler. Cela active le système d\'apaisement naturel.',
                'type'        => 'conflict',
                'theme'       => $emotionsTheme,
            ],
            [
                'title'       => 'Merci quotidien',
                'description' => 'Dites chaque jour à votre partenaire une chose pour laquelle vous lui êtes reconnaissant·e. Pas de répétition pendant 30 jours.',
                'type'        => 'daily',
                'theme'       => null,
            ],
            [
                'title'       => 'Date night créatif',
                'description' => 'Organisez un rendez-vous surprise une fois par mois avec un budget minimum. L\'important est la créativité, pas le coût.',
                'type'        => 'monthly',
                'theme'       => null,
            ],
        ];

        foreach ($rituals as $data) {
            $ritual = new Ritual();
            $ritual->setTitle($data['title']);
            $ritual->setDescription($data['description']);
            $ritual->setType($data['type']);
            if ($data['theme']) {
                $ritual->setTheme($data['theme']);
            }
            $manager->persist($ritual);
        }

        $manager->flush();
    }
}
