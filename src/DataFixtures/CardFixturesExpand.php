<?php
// src/DataFixtures/CardFixturesExpand.php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Complète les thèmes sous-représentés : Croyances, Passions, Attentes,
 * Projets, Sexualité, Jeux, Créativité, Valeurs — cible ~20 cartes chacun.
 */
class CardFixturesExpand extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themeRepo = $manager->getRepository(Theme::class);

        $data = [

            'Croyances' => [
                ['text' => "Y a-t-il quelque chose que tu croyais sur l'amour à 20 ans qui te fait sourire aujourd'hui ?", 'diff' => 1],
                ['text' => "Quelle est la leçon de vie que tu as apprise à la dure et que tu ne voudrais pas réapprendre ?", 'diff' => 1],
                ['text' => "Est-ce que tu crois au destin — et est-ce que notre rencontre a changé ta réponse ?", 'diff' => 2],
                ['text' => "Y a-t-il une conviction que tu portes seul·e et que tu penses que peu de gens comprendraient ?", 'diff' => 2],
                ['text' => "Quelle est la chose sur laquelle tu as changé d'avis grâce à moi ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses que les gens peuvent vraiment changer en profondeur ?", 'diff' => 2],
                ['text' => "Y a-t-il une peur irrationnelle que tu sais irrationnelle mais que tu ne peux pas t'empêcher d'avoir ?", 'diff' => 2],
                ['text' => "Quelle est la croyance que tu voudrais transmettre à tes enfants — ou à la génération suivante ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose dans nos façons de voir le monde qui te semble incompatible ?", 'diff' => 3],
                ['text' => "Y a-t-il une conviction que tu as sur toi-même et qui t'empêche d'avancer ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu crois sur le pardon — est-ce que tu as pardonné quelque chose que tu n'as jamais dit ?", 'diff' => 3],
            ],

            'Passions' => [
                ['text' => "Quelle est la dernière fois que tu as fait quelque chose juste pour le plaisir, sans objectif ?", 'diff' => 1],
                ['text' => "Y a-t-il une passion secrète que peu de gens autour de toi connaissent ?", 'diff' => 1],
                ['text' => "Quel talent chez toi penses-tu que je n'ai pas encore vraiment découvert ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a un domaine dans lequel tu aimerais devenir vraiment expert·e ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu faisais avec passion et que tu as arrêté à cause des autres ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui t'a donné le plus de satisfaction cette année — pas la fierté, la vraie satisfaction ?", 'diff' => 2],
                ['text' => "Est-ce que tu as l'impression d'avoir assez de temps et d'espace pour tes passions dans notre vie ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu aimes profondément et dont tu as peur de parler par crainte d'être jugé·e ?", 'diff' => 3],
                ['text' => "Si tu devais transmettre une seule passion à quelqu'un que tu aimes, laquelle ce serait ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on se laisse assez de liberté l'un·e à l'autre pour nos passions individuelles ?", 'diff' => 3],
            ],

            'Attentes' => [
                ['text' => "Qu'est-ce que tu apprécies le plus dans la façon dont on communique ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu attends de moi lors d'un conflit — que tu n'as jamais dit explicitement ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu attends de moi quand tu traverses quelque chose de difficile — présence, solutions, silence ?", 'diff' => 2],
                ['text' => "Est-ce qu'il t'arrive de te sentir incompris·e par moi — dans quelle situation ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu attends de notre relation que tu n'as pas encore eu le courage de formuler ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on gère bien les attentes déçues — ou est-ce qu'on les laisse s'accumuler ?", 'diff' => 3],
                ['text' => "Y a-t-il une attente que tu as abandonnée sur notre relation — et est-ce que c'était un deuil ou un soulagement ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu n'as jamais osé demander à l'autre parce que tu avais peur de la réponse ?", 'diff' => 3],
            ],

            'Projets' => [
                ['text' => "Y a-t-il un endroit que tu veux absolument qu'on visite ensemble avant la fin de l'année ?", 'diff' => 1],
                ['text' => "Quel est le projet qui t'enthousiasme le plus en ce moment dans ta vie perso ou pro ?", 'diff' => 1],
                ['text' => "Si tu pouvais changer de carrière demain sans risque, tu ferais quoi ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on avait prévu de faire ensemble et qu'on n'a jamais fait ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a un projet de vie (déménagement, reconversion, enfants…) sur lequel tu n'es pas encore sûr·e ?", 'diff' => 2],
                ['text' => "Quel obstacle concret t'empêche d'avancer vers ce que tu veux vraiment ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu fais pour nous mais pas pour toi — et est-ce que ça te pèse ?", 'diff' => 3],
                ['text' => "Est-ce que tu as l'impression qu'on se soutient équitablement dans nos ambitions respectives ?", 'diff' => 3],
                ['text' => "Y a-t-il un projet sur lequel tu aurais besoin de mon aide, mais tu n'as pas osé demander ?", 'diff' => 3],
            ],

            'Sexualité' => [
                ['text' => "Y a-t-il une ambiance ou un contexte qui te met particulièrement à l'aise ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait te sentir désirable ?", 'diff' => 1],
                ['text' => "Y a-t-il un mot ou une phrase que tu aimerais entendre plus souvent ?", 'diff' => 1],
                ['text' => "Est-ce que tu penses qu'on parle suffisamment de ce qu'on aime ou n'aime pas ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais essayer mais que tu n'as pas encore proposé ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui crée le plus de distance entre nous sur ce plan-là ?", 'diff' => 2],
                ['text' => "Y a-t-il un fantasme que tu n'as jamais partagé — même vaguement ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens toujours désiré·e de la même façon qu'au début — si non, qu'est-ce qui a changé ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose qu'on faisait avec plus de légèreté avant et qu'on a perdu en chemin ?", 'diff' => 3],
                ['text' => "Est-ce qu'il t'arrive de simuler quelque chose — enthousiasme, envie — pour éviter une conversation ?", 'diff' => 3],
            ],

            'Jeux' => [
                ['text' => "Raconte en 1 minute la première impression que tu as eue de moi. Sois honnête.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Chacun complète la phrase : 'Tu ne sais probablement pas que je...'", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Nomme une chose que tu ferais mieux que moi — et une que tu ferais moins bien.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Décris notre relation comme si tu la racontais à quelqu'un qui ne nous connaît pas.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Chacun dit ce qu'il pense que l'autre va répondre à la prochaine carte.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Si on était un duo de film, on serait qui ?", 'diff' => 1],
                ['text' => "Quelle est la chose la plus inattendue que tu aies découverte sur moi ?", 'diff' => 2],
                ['text' => "Chacun pose une question qu'il n'a jamais osé poser.", 'diff' => 3, 'is_bonus' => true],
            ],

            'Créativité' => [
                ['text' => "Invente un surnom pour notre relation que personne d'autre ne comprendrait.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Si notre histoire était un livre, quel serait le titre du chapitre en cours ?", 'diff' => 1],
                ['text' => "Dessine en 60 secondes quelque chose qui représente ton humeur du moment — l'autre devine.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Invente un mot qui décrit quelque chose qu'on vit ensemble et qui n'a pas encore de nom.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Quelle chanson mettrait-on au générique de fin de notre histoire si c'était un film ?", 'diff' => 2],
                ['text' => "Crée une métaphore pour décrire où on en est dans notre relation en ce moment.", 'diff' => 2],
                ['text' => "Écris en 30 secondes la dédicace que tu mettrais dans le livre de notre vie.", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Si notre couple avait une devise officielle, quelle serait-elle ?", 'diff' => 3, 'is_bonus' => true],
            ],

            'Valeurs' => [
                ['text' => "Y a-t-il quelque chose que la société valorise et que toi tu n'arrives pas à trouver important ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te rend fier·ère de toi — pas ce que les autres admirent, ce que toi tu valorises ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose qu'on fait comme couple et qui reflète parfaitement nos valeurs communes ?", 'diff' => 2],
                ['text' => "Quelle valeur aimerais-tu qu'on cultive plus dans notre relation ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose que tu observes dans les autres couples et que tu veux absolument éviter ?", 'diff' => 2],
                ['text' => "Y a-t-il une valeur que tu penses ne pas partager complètement avec moi — et est-ce que ça te pèse ?", 'diff' => 3],
                ['text' => "Quelle est la chose pour laquelle tu ne te pardonnerais pas si tu la faisais dans notre relation ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a un compromis que tu as fait sur tes valeurs pour notre relation et que tu regrettes ?", 'diff' => 3],
            ],
        ];

        foreach ($data as $themeName => $cards) {
            $theme = $themeRepo->findOneBy(['name' => $themeName]);
            if (!$theme) {
                continue;
            }

            foreach ($cards as $item) {
                $card = new Card();
                $card->setQuestionText($item['text']);
                $card->setDifficultyLevel($item['diff']);
                $card->setIsBonus($item['is_bonus'] ?? false);
                $card->setType('question');
                $card->setTheme($theme);
                $manager->persist($card);
            }
        }

        $manager->flush();
    }
}
