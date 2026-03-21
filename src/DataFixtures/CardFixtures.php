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
        // ── Thèmes ────────────────────────────────────────────────────────────
        //
        // Chaque thème contient :
        //   - name, colorCode, description, backgroundImage (asset local Flutter)
        //   - cards[]  : questions type "question" (simultané)
        //   - challenges[] : questions type "challenge" (tour à tour / défi)
        //
        $themes = [
            [
                'name'            => 'Couple',
                'colorCode'       => '#ec1380',
                'description'     => 'Explorez la dynamique de votre couple, ses forces et ses fragilités.',
                'backgroundImage' => 'assets/images/themes/couple.jpg',
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
                'challenges' => [
                    "Dis à ton partenaire une chose qu'il/elle fait qui te touche particulièrement.",
                    "Décris le moment où tu t'es senti(e) le plus proche de ton partenaire.",
                ],
            ],
            [
                'name'            => 'Valeurs',
                'colorCode'       => '#3498db',
                'description'     => 'Explorez les valeurs et croyances fondamentales qui guident votre relation.',
                'backgroundImage' => 'assets/images/themes/valeurs.jpg',
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
                'challenges' => [
                    "Cite une valeur que vous partagez et expliquez comment elle se manifeste dans votre quotidien.",
                    "Exprime une conviction que tu n'as jamais partagée avec ton partenaire.",
                ],
            ],
            [
                'name'            => 'Sexualité',
                'colorCode'       => '#e74c3c',
                'description'     => 'Explorez la sexualité et l\'intimité dans votre relation en toute confiance.',
                'backgroundImage' => 'assets/images/themes/sexualite.jpg',
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
                'challenges' => [
                    "Dis à ton partenaire ce qui te rend le plus à l'aise avec lui/elle physiquement.",
                    "Propose une nouvelle chose que vous n'avez jamais tentée ensemble.",
                ],
            ],
            [
                'name'            => 'Passé',
                'colorCode'       => '#f39c12',
                'description'     => 'Explorez le passé et les expériences qui ont façonné votre relation.',
                'backgroundImage' => 'assets/images/themes/passe.jpg',
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
                'challenges' => [
                    "Partage un souvenir d'enfance que tu n'as jamais raconté à ton partenaire.",
                    "Décris le moment où tu as réalisé que ton partenaire était la bonne personne.",
                ],
            ],
            [
                'name'            => 'Projets',
                'colorCode'       => '#2ecc71',
                'description'     => 'Explorez vos projets et aspirations pour l\'avenir de votre relation.',
                'backgroundImage' => 'assets/images/themes/projets.jpg',
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
                'challenges' => [
                    "Propose un projet commun que vous pourriez lancer dans les 3 prochains mois.",
                    "Décris votre vie idéale dans 10 ans en 60 secondes.",
                ],
            ],
            [
                'name'            => 'Émotions',
                'colorCode'       => '#9b59b6',
                'description'     => 'Explorez les émotions et la gestion des sentiments dans votre relation.',
                'backgroundImage' => 'assets/images/themes/emotions.jpg',
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
                'challenges' => [
                    "Exprime une émotion que tu as du mal à verbaliser en temps normal.",
                    "Dis à ton partenaire ce qu'il/elle fait qui te touche sans qu'il/elle le sache.",
                ],
            ],
            [
                'name'            => 'Intimité',
                'colorCode'       => '#1abc9c',
                'description'     => 'Explorez l\'intimité émotionnelle et physique dans votre relation.',
                'backgroundImage' => 'assets/images/themes/intimite.jpg',
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
                'challenges' => [
                    "Regardez-vous dans les yeux en silence pendant 60 secondes, puis dites ce que vous avez ressenti.",
                    "Faites un câlin de 30 secondes sans parler avant de répondre à la prochaine question.",
                ],
            ],
            [
                'name'            => 'Avenir',
                'colorCode'       => '#34495e',
                'description'     => 'Explorez vos visions et aspirations pour l\'avenir de votre relation.',
                'backgroundImage' => 'assets/images/themes/avenir.jpg',
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
                'challenges' => [
                    "Écris (ou dis) une lettre à votre couple dans 5 ans.",
                    "Planifiez ensemble une chose que vous ferez dans les 30 prochains jours.",
                ],
            ],
            [
                'name'            => 'Jeux',
                'colorCode'       => '#d35400',
                'description'     => 'Explorez le côté ludique et amusant de votre relation.',
                'backgroundImage' => 'assets/images/themes/jeux.jpg',
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
                'challenges' => [
                    "Faites une imitation de votre partenaire — il/elle doit deviner ce que vous imitez.",
                    "Inventez ensemble une règle absurde que vous respecterez pendant toute la soirée.",
                ],
            ],
            [
                'name'            => 'Créativité',
                'colorCode'       => '#8e44ad',
                'description'     => 'Explorez la créativité et l\'innovation dans votre relation.',
                'backgroundImage' => 'assets/images/themes/creativite.jpg',
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
                'challenges' => [
                    "Dessinez ensemble un portrait de votre couple en 3 minutes (papier ou dans l'air).",
                    "Inventez un nouveau mot qui décrit votre relation et définissez-le.",
                ],
            ],
            [
                'name'            => 'Attentes',
                'colorCode'       => '#7f8c8d',
                'description'     => 'Explorez les attentes et besoins dans votre relation.',
                'backgroundImage' => 'assets/images/themes/attentes.jpg',
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
                'challenges' => [
                    "Exprime une attente que tu n'as jamais osé formuler clairement.",
                    "Dis à ton partenaire ce qu'il/elle fait déjà très bien et que tu ne lui dis pas assez.",
                ],
            ],
            [
                'name'            => 'Croyances',
                'colorCode'       => '#16a085',
                'description'     => 'Explorez les croyances et convictions profondes qui influencent votre relation.',
                'backgroundImage' => 'assets/images/themes/croyances.jpg',
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
                'challenges' => [
                    "Partagez une conviction que vous pensiez opposée mais qui se révèle complémentaire.",
                    "Dis une chose que tu croyais sur les relations et que tu as dû réviser grâce à notre couple.",
                ],
            ],
            [
                'name'            => 'Passions',
                'colorCode'       => '#c0392b',
                'description'     => 'Explorez les passions et intérêts communs qui enrichissent votre relation.',
                'backgroundImage' => 'assets/images/themes/passions.jpg',
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
                'challenges' => [
                    "Présentez votre passion principale à votre partenaire comme si c'était la première fois qu'il/elle l'entend.",
                    "Proposez une activité liée à une passion de l'autre que vous n'avez jamais essayée.",
                ],
            ],
        ];

        foreach ($themes as $themeData) {
            $theme = new Theme();
            $theme->setName($themeData['name']);
            $theme->setColorCode($themeData['colorCode']);
            $theme->setDescription($themeData['description']);
            $theme->setBackgroundImage($themeData['backgroundImage']);

            $manager->persist($theme);

            // Cartes de type "question" (réponse simultanée)
            foreach ($themeData['cards'] as $questionText) {
                $card = new Card();
                $card->setQuestionText($questionText);
                $card->setTheme($theme);
                $card->setType('question');
                $card->setDifficultyLevel(rand(1, 3));
                $card->setIsBonus(false);
                $manager->persist($card);
            }

            // Cartes de type "challenge" (tour à tour / défi)
            foreach ($themeData['challenges'] as $challengeText) {
                $card = new Card();
                $card->setQuestionText($challengeText);
                $card->setTheme($theme);
                $card->setType('challenge');
                $card->setDifficultyLevel(2);
                $card->setIsBonus(false);
                $manager->persist($card);
            }
        }

        $manager->flush();
    }
}
