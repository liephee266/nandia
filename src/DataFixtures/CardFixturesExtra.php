<?php
// src/DataFixtures/CardFixturesExtra.php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Extension de CardFixtures : ajoute ~65 cartes supplémentaires (5 par thème)
 * pour atteindre >520 cartes au total.
 *
 * Dépend de CardFixtures (les thèmes doivent déjà exister).
 */
class CardFixturesExtra extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [CardFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // Cartes supplémentaires indexées par nom de thème
        $extra = [
            'Couple' => [
                "Comment exprimes-tu de la reconnaissance envers ton partenaire au quotidien ?",
                "Y a-t-il une habitude que tu aimerais qu'on crée ensemble ?",
                "Comment navigues-tu les périodes où vous n'êtes pas sur la même longueur d'onde ?",
                "Quelle est la chose la plus courageuse que notre couple ait faite ensemble ?",
                "Qu'est-ce qui te prouve que notre amour grandit avec le temps ?",
            ],
            'Valeurs' => [
                "Qu'est-ce que l'intégrité représente pour toi au quotidien ?",
                "Comment définirais-tu le courage dans une relation ?",
                "Y a-t-il une valeur que tu aimerais approfondir cette année ?",
                "Quelle est la valeur que tu admires le plus chez moi ?",
                "Comment distingues-tu les valeurs des préférences personnelles ?",
            ],
            'Sexualité' => [
                "Comment est-ce que tu vis la période après l'intimité ?",
                "Qu'est-ce que la tendresse représente dans notre vie intime ?",
                "Y a-t-il une période de votre relation où vous vous êtes sentis le plus proches physiquement ?",
                "Comment prends-tu soin de ton corps et de ta confiance en lui ?",
                "Est-ce que tu penses que la sexualité évolue avec l'amour ou malgré lui ?",
            ],
            'Passé' => [
                "Quel conseil de vie as-tu reçu et que tu as suivi ou ignoré ?",
                "Y a-t-il un ancêtre ou aïeul qui t'inspire particulièrement ?",
                "Comment ton passé colore-t-il ta façon de voir l'avenir ?",
                "Y a-t-il un souvenir que tu revis mentalement quand tu veux te sentir en paix ?",
                "Quelle période de ta vie voudrais-tu que je comprenne mieux ?",
            ],
            'Projets' => [
                "Quel projet vous ferait sortir complètement de votre zone de confort ?",
                "Si vous deviez construire quelque chose de vos mains, ce serait quoi ?",
                "Y a-t-il un rêve que tu reportes depuis trop longtemps ?",
                "Quel impact local — dans votre quartier ou communauté — voudriez-vous avoir ?",
                "Si vous deviez créer une tradition annuelle, laquelle inventerez-vous ?",
            ],
            'Émotions' => [
                "Qu'est-ce qui te redonne de l'énergie quand tu es à plat émotionnellement ?",
                "Comment exprimes-tu la joie — est-ce spontané ou retenu ?",
                "Y a-t-il une émotion que tu associes à des moments spécifiques de notre relation ?",
                "Qu'est-ce que la paix intérieure signifie pour toi ?",
                "Comment reconnais-tu que tu as besoin d'aide émotionnelle ?",
            ],
            'Intimité' => [
                "Qu'est-ce qu'un moment de complicité parfait représente pour toi ?",
                "Y a-t-il un lieu qui représente pour toi la sécurité et l'intimité ?",
                "Comment est-ce que tu te révèles progressivement dans une relation ?",
                "Qu'est-ce qui rend un silence partagé confortable plutôt qu'inconfortable ?",
                "Y a-t-il quelque chose que tu ne partages qu'avec moi et personne d'autre ?",
            ],
            'Avenir' => [
                "Quelle compétence veux-tu développer dans les deux prochaines années ?",
                "Y a-t-il un mode de vie alternatif que tu as envisagé sérieusement ?",
                "Comment imagines-tu ta relation avec le travail dans 10 ans ?",
                "Quelle tradition familiale veux-tu créer ou maintenir ?",
                "Qu'est-ce que la liberté représentera pour toi à 60 ans ?",
            ],
            'Jeux' => [
                "Quel personnage de film ou de jeu vidéo représente le mieux ton partenaire ?",
                "Si on organisait une chasse au trésor dans notre ville, où commencerait-elle ?",
                "Quel jeu de société symbolise le mieux notre façon de fonctionner ensemble ?",
                "Y a-t-il un jeu d'enfance que tu rêves de refaire en adulte ?",
                "Si notre quotidien était un jeu, à quel niveau serions-nous ?",
            ],
            'Créativité' => [
                "Y a-t-il une couleur qui représente votre relation et pourquoi ?",
                "Si vous tourniez un court métrage sur votre couple, quelle en serait la scène principale ?",
                "Quelle forme prendrait votre amour s'il était une architecture ?",
                "Quel texte — poème, lettre, chanson — t'a marqué(e) et pourquoi ?",
                "Comment la créativité de l'autre t'inspire-t-elle ou te surprend-elle ?",
            ],
            'Attentes' => [
                "Y a-t-il une attente implicite que tu as réalisé avoir sans jamais l'avoir formulée ?",
                "Comment réagis-tu quand une attente importante n'est pas comblée ?",
                "Y a-t-il une attente de toi-même qui te pèse dans notre relation ?",
                "Comment adaptes-tu tes attentes face aux contraintes de la vie ?",
                "Quelle est l'attente la plus importante que tu as concernant notre vieillesse ensemble ?",
            ],
            'Croyances' => [
                "Quelle croyance sur la santé guide tes habitudes de vie ?",
                "Est-ce que tu crois que l'amour romantique peut durer toute une vie ?",
                "Y a-t-il une croyance sur l'éducation que tu as remise en question ?",
                "Quelle est ta croyance sur le courage — est-ce l'absence de peur ou son dépassement ?",
                "Est-ce que tu crois que les crises renforcent ou usent un couple ?",
            ],
            'Passions' => [
                "Quelle passion t'a le plus transformé(e) en tant que personne ?",
                "Est-ce que ta passion principale te rapproche ou t'éloigne parfois de moi ?",
                "Y a-t-il une passion que tu aimerais transmettre à tes enfants ou proches ?",
                "Comment trouves-tu l'équilibre entre tes passions individuelles et notre vie commune ?",
                "Quelle passion nouvelle as-tu envie d'explorer cette année ?",
            ],
        ];

        $themeRepo = $manager->getRepository(Theme::class);

        foreach ($extra as $themeName => $questions) {
            $theme = $themeRepo->findOneBy(['name' => $themeName]);
            if (!$theme) {
                continue; // thème introuvable → on passe
            }

            foreach ($questions as $questionText) {
                $card = new Card();
                $card->setQuestionText($questionText);
                $card->setTheme($theme);
                $card->setType('question');
                $card->setDifficultyLevel(rand(1, 3));
                $card->setIsBonus(false);
                $manager->persist($card);
            }
        }

        $manager->flush();
    }
}
