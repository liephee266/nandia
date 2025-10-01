<?php
// src/DataFixtures/CardFixtures.php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CardFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des thèmes
        $themes = [
            [
                'name' => 'Couple',
                'colorCode' => '#ec1380',
                'description' => 'Cet espace permet d’explorer la dynamique du couple, ses forces et ses fragilités.',
                'cards' => [
                    "Quelle est la chose la plus folle que tu aimerais faire avec moi ?",
                    "Quel est ton rêve d'avenir en tant que couple ?",
                    "Qu'est-ce que tu attends de moi dans les moments difficiles ?",
                    "Comment définirais-tu une relation idéale ?",
                    "Quelle est la chose la plus importante que tu veux que je sache sur toi ?",
                    "Quel est ton plus grand espoir pour notre relation ?",
                    "Qu'est-ce qui te rend le plus heureux/heureuse avec moi ?",
                    "Quel est ton plus grand défi en tant que couple ?",
                    "Qu'est-ce que tu aimerais que l'on fasse plus souvent ensemble ?",
                    "Quelle est ta promesse pour l'avenir de notre relation ?",
                ],
            ],
            [
                'name' => 'Valeurs',
                'colorCode' => '#3498db',
                'description' => 'Cet espace permet d’explorer les valeurs et croyances fondamentales qui guident notre relation.',
                'cards' => [
                    "Quelle est ta valeur la plus importante dans une relation ?",
                    "Qu'est-ce qui te motive le plus dans la vie ?",
                    "Quelle est ta croyance la plus profonde sur l'amour ?",
                    "Quel est ton principe moral le plus incontournable ?",
                    "Comment définis-tu la fidélité ?",
                    "Quelle est la chose la plus importante pour toi dans la vie ?",
                    "Quelle est ton idée de la liberté dans un couple ?",
                    "Qu'est-ce que tu ne supporterais pas chez un partenaire ?",
                    "Quel est ton rêve de vie idéale ?",
                    "Quelle est ta vision de la réussite personnelle ?",
                ],
            ],
            [
                'name' => 'Sexualité',
                'colorCode' => '#e74c3c',
                'description' => 'Cet espace permet d’explorer la sexualité et l’intimité dans la relation.',
                'cards' => [
                    "Qu'est-ce que l'intimité signifie pour toi ?",
                    "Quel est ton fantasme le plus secret ?",
                    "Qu'est-ce qui te fait te sentir le plus proche de moi ?",
                    "Comment décrirais-tu ta vie sexuelle idéale ?",
                    "Qu'est-ce que tu aimes que je te fasse ?",
                    "Quel est ton moment le plus intime avec moi ?",
                    "Qu'est-ce que tu aimerais que l'on explore ensemble ?",
                    "Qu'est-ce qui te rend vulnérable avec moi ?",
                    "Quel est ton désir le plus profond ?",
                    "Qu'est-ce que tu voudrais que l'on ose ensemble ?",
                ],
            ],
            [
                'name' => 'Passé',
                'colorCode' => '#f39c12',
                'description' => 'Cet espace permet d’explorer le passé et les expériences qui ont façonné notre relation.',
                'cards' => [
                    "Quel souvenir d'enfance te rend nostalgique ?",
                    "Quelle est la chose la plus difficile que tu aies traversée ?",
                    "Quelle émotion ressens-tu le plus souvent ?",
                    "Qu'est-ce que ton passé t'a appris sur toi ?",
                    "Quelle est ton expérience la plus marquante avant moi ?",
                    "Quel est ton plus grand regret ?",
                    "Qu'est-ce que tu as du mal à pardonner ?",
                    "Quelle émotion aimerais-tu que je comprenne mieux ?",
                    "Quel souvenir partagé avec moi est le plus précieux ?",
                    "Qu'est-ce que tu veux guérir ensemble ?",
                ],
            ],
            [
                'name' => 'Projets',
                'colorCode' => '#2ecc71',
                'description' => 'Cet espace permet d’explorer les projets et aspirations pour l’avenir de notre relation.',
                'cards' => [
                    "Quel est ton rêve le plus fou pour nous ?",
                    "Où te vois-tu dans 5 ans avec moi ?",
                    "Quel est le projet que tu aimerais réaliser ensemble ?",
                    "Qu'est-ce que tu veux accomplir avant tout ?",
                    "Quelle est ta vision de notre vie future ?",
                    "Quel est ton plus grand objectif personnel ?",
                    "Qu'est-ce que tu aimerais apprendre ensemble ?",
                    "Quel est ton endroit préféré pour voyager avec moi ?",
                    "Qu'est-ce que tu veux construire avec moi ?",
                    "Quelle est ta définition du succès en couple ?",
                ],
            ],
            [
                'name' => 'Émotions',
                'colorCode' => '#9b59b6',
                'description' => 'Cet espace permet d’explorer les émotions et la gestion des sentiments dans notre relation.',
                'cards' => [
                    "Quelle émotion ressens-tu le plus souvent avec moi ?",
                    "Qu'est-ce qui te rend le plus heureux/heureuse dans notre relation ?",
                    "Quelle est ta plus grande peur dans notre relation ?",
                    "Comment gères-tu la colère ou la frustration ?",
                    "Qu'est-ce qui te fait te sentir aimé(e) ?",
                    "Quelle est ta façon préférée de montrer ton amour ?",
                    "Qu'est-ce qui te rend vulnérable avec moi ?",
                    "Quelle est ta plus grande source de stress actuellement ?",
                    "Qu'est-ce qui te fait te sentir en sécurité avec moi ?",
                    "Quelle est ta plus grande joie partagée avec moi ?",
                ],
            ],
            [
                'name' => 'Intimité',
                'colorCode' => '#1abc9c',
                'description' => 'Cet espace permet d’explorer l’intimité émotionnelle et physique dans notre relation.',
                'cards' => [
                    "Qu'est-ce que l'intimité signifie pour toi ?",
                    "Quel est ton moment le plus intime avec moi ?",
                    "Qu'est-ce qui te fait te sentir le plus proche de moi ?",
                    "Comment décrirais-tu notre connexion émotionnelle ?",
                    "Qu'est-ce que tu aimes que je te fasse pour te sentir aimé(e) ?",
                    "Quel est ton geste d'affection préféré ?",
                    "Qu'est-ce qui te rend vulnérable avec moi ?",
                    "Quel est ton souvenir le plus tendre avec moi ?",
                    "Qu'est-ce que tu voudrais que l'on partage davantage ?",
                    "Quelle est ta définition de l'intimité parfaite ?",
                ],
            ],
            [
                'name' => 'Avenir',
                'colorCode' => '#34495e',
                'description' => 'Cet espace permet d’explorer nos visions et aspirations pour l’avenir de notre relation.',
                'cards' => [
                    "Où te vois-tu dans 10 ans avec moi ?",
                    "Quel est ton rêve le plus fou pour notre avenir ?",
                    "Quel est le projet que tu aimerais réaliser ensemble ?",
                    "Qu'est-ce que tu veux accomplir avant tout ?",
                    "Quelle est ta vision de notre vie future ?",
                    "Quel est ton plus grand objectif personnel ?",
                    "Qu'est-ce que tu aimerais apprendre ensemble ?",
                    "Quel est ton endroit préféré pour voyager avec moi ?",
                    "Qu'est-ce que tu veux construire avec moi ?",
                    "Quelle est ta définition du succès en couple ?",
                ],
            ],
            [
                'name' => 'Jeux',
                'colorCode' => '#d35400',
                'description' => 'Cet espace permet d’explorer le côté ludique et amusant de notre relation.',
                'cards' => [
                    "Quel est ton jeu préféré à jouer ensemble ?",
                    "Quelle est ta blague préférée que je te raconte ?",
                    "Quel est ton souvenir le plus drôle avec moi ?",
                    "Qu'est-ce qui te fait rire le plus dans notre relation ?",
                    "Quel est ton défi préféré que nous avons relevé ensemble ?",
                    "Quelle est ta façon préférée de passer du temps libre avec moi ?",
                    "Qu'est-ce que tu aimerais essayer de nouveau ensemble ?",
                    "Quel est ton moment le plus spontané avec moi ?",
                    "Qu'est-ce qui te rend le plus heureux/heureuse dans nos moments de jeu ?",
                    "Quelle est ta définition du plaisir partagé ?",
                ],
            ],
            [
                'name' => 'Créativité',
                'colorCode' => '#8e44ad',
                'description' => 'Cet espace permet d’explorer la créativité et l’innovation dans notre relation.',
                'cards' => [
                    "Quelle est ta façon préférée d'exprimer ta créativité avec moi ?",
                    "Quel est ton projet créatif préféré que nous avons réalisé ensemble ?",
                    "Qu'est-ce qui t'inspire le plus dans notre relation ?",
                    "Quelle est ta vision la plus folle pour notre avenir créatif ?",
                    "Qu'est-ce que tu aimerais inventer ou créer avec moi ?",
                    "Quel est ton souvenir le plus artistique avec moi ?",
                    "Qu'est-ce qui te rend le plus fier/fière de notre créativité partagée ?",
                    "Quelle est ta définition de l'innovation en couple ?",
                    "Qu'est-ce que tu aimerais apprendre de nouveau ensemble ?",
                    "Quel est ton rêve créatif le plus fou pour nous deux ?",
                ],
            ],
            [
                'name' => 'Attentes',
                'colorCode' => '#7f8c8d',
                'description' => 'Cet espace permet d’explorer les attentes et besoins dans notre relation.',
                'cards' => [
                    "Qu'est-ce que tu attends le plus de moi dans notre relation ?",
                    "Quelle est ta plus grande attente en matière de communication ?",
                    "Qu'est-ce que tu veux que je comprenne mieux sur toi ?",
                    "Quelle est ta façon préférée de recevoir de l'affection ?",
                    "Qu'est-ce que tu attends de moi dans les moments difficiles ?",
                    "Quelle est ta plus grande attente en matière de soutien émotionnel ?",
                    "Qu'est-ce que tu aimerais que l'on fasse plus souvent ensemble ?",
                    "Quelle est ta définition du respect mutuel ?",
                    "Qu'est-ce que tu attends de moi pour te sentir en sécurité ?",
                    "Quelle est ta promesse pour notre avenir ensemble ?",
                ],
            ],
            [
                'name' => 'Croyances',
                'colorCode' => '#16a085',
                'description' => 'Cet espace permet d’explorer les croyances et convictions profondes qui influencent notre relation.',
                'cards' => [
                    "Quelle est ta croyance la plus profonde sur l'amour ?",
                    "Qu'est-ce qui te motive le plus dans la vie ?",
                    "Quel est ton principe moral le plus incontournable ?",
                    "Comment définis-tu la fidélité ?",
                    "Qu'est-ce que tu ne supporterais pas chez un partenaire ?",
                    "Quelle est ta vision de la réussite personnelle ?",
                    "Qu'est-ce que tu crois sur le pardon dans une relation ?",
                    "Quelle est ta croyance la plus forte sur le bonheur ?",
                    "Qu'est-ce que tu penses de l'importance de la communication honnête ?",
                    "Quelle est ta conviction la plus profonde sur le respect mutuel ?",
                ],
            ],
            [
                'name' => 'Passions',
                'colorCode' => '#c0392b',
                'description' => 'Cet espace permet d’explorer les passions et intérêts communs qui enrichissent notre relation.',
                'cards' => [
                    "Quelle est ta passion la plus grande dans la vie ?",
                    "Qu'est-ce que tu aimes faire pour te sentir vivant(e) ?",
                    "Quel est ton hobby préféré que nous partageons ?",
                    "Qu'est-ce qui te rend le plus enthousiaste dans notre relation ?",
                    "Quelle est ta façon préférée de passer du temps libre avec moi ?",
                    "Qu'est-ce que tu aimerais essayer de nouveau ensemble ?",
                    "Quel est ton souvenir le plus passionnant avec moi ?",
                    "Qu'est-ce qui te rend le plus heureux/heureuse dans nos moments partagés ?",
                    "Quelle est ta définition de l'aventure en couple ?",
                    "Qu'est-ce que tu aimerais découvrir ensemble ?",
                ],
            ]
        ];

        foreach ($themes as $themeData) {
            $theme = new Theme();
            $theme->setName($themeData['name']);
            $theme->setColorCode($themeData['colorCode']);

            $manager->persist($theme);

            foreach ($themeData['cards'] as $questionText) {
                $card = new Card();
                $card->setQuestionText($questionText);
                $card->setTheme($theme);
                $card->setDifficultyLevel(rand(1, 3)); // Niveau aléatoire entre 1 et 3
                $card->setIsBonus(false); // Ajustez selon vos besoins

                $manager->persist($card);
            }
        }

        $manager->flush();
    }
}