<?php
// src/DataFixtures/CardFixturesQuality.php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Cartes de qualité — questions ancrées dans le vécu, formulées pour créer
 * de la complicité et de la profondeur dans la relation.
 * ~30 cartes par thème × 13 thèmes = ~390 cartes.
 */
class CardFixturesQuality extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themeRepo = $manager->getRepository(Theme::class);

        $data = [

            'Couple' => [
                // Facile
                ['text' => "Cite une habitude qu'on a développée ensemble et qui te rend heureux·se.", 'diff' => 1],
                ['text' => "Qu'est-ce que tu faisais en premier quand tu avais une bonne nouvelle, avant de me connaître ?", 'diff' => 1],
                ['text' => "Quel petit geste du quotidien de l'autre t'a fait réaliser que tu l'aimais vraiment ?", 'diff' => 1],
                ['text' => "Si tu devais m'expliquer à quelqu'un en trois mots, lesquels choisirais-tu ?", 'diff' => 1],
                ['text' => "Quel moment passé ensemble te revient en tête quand tu penses à nous ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que je fais spontanément qui te fait toujours sourire ?", 'diff' => 1],
                ['text' => "Quelle est la chose la plus drôle qu'on ait vécue ensemble ?", 'diff' => 1],
                ['text' => "Quel est le plat ou l'endroit que tu associes immédiatement à nous deux ?", 'diff' => 1],
                // Moyen
                ['text' => "La dernière fois que tu t'es senti·e vraiment soutenu·e par moi, c'était quand ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'oses pas encore me demander ?", 'diff' => 2],
                ['text' => "Comment sais-tu que je t'aime, concrètement — qu'est-ce qui te le montre ?", 'diff' => 2],
                ['text' => "Qu'est-ce qu'on a construit ensemble dont tu es le plus fier·ère ?", 'diff' => 2],
                ['text' => "Y a-t-il une période de notre relation qui t'a le plus fait grandir ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu ferais différemment si on recommençait depuis le début ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu n'avais jamais osé faire avant moi et que tu fais maintenant ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a un désaccord qu'on a eu et dont tu es finalement content·e qu'il ait eu lieu ?", 'diff' => 2],
                // Difficile
                ['text' => "Y a-t-il quelque chose que tu t'es retenu·e de dire pendant une dispute et que tu aurais voulu exprimer ?", 'diff' => 3],
                ['text' => "Quel est le moment où tu as failli douter de nous, et qu'est-ce qui t'a fait rester ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu aimerais que je comprenne sur toi sans avoir à l'expliquer ?", 'diff' => 3],
                ['text' => "Y a-t-il un besoin que j'ai du mal à combler et dont tu n'as jamais vraiment parlé ?", 'diff' => 3],
                ['text' => "Si tu pouvais changer une seule chose dans notre façon de fonctionner ensemble, ce serait quoi ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens libre d'être totalement toi-même avec moi — sinon, où est la limite ?", 'diff' => 3],
            ],

            'Intimité' => [
                ['text' => "À quel moment te sens-tu le plus proche de moi physiquement ?", 'diff' => 1],
                ['text' => "Quelle est la forme de tendresse que tu préfères recevoir ?", 'diff' => 1],
                ['text' => "Y a-t-il un endroit sur toi que tu aimes qu'on touche et qu'on touche rarement ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te met le plus à l'aise pour être vulnérable devant moi ?", 'diff' => 1],
                ['text' => "Quel geste de ma part te donne le sentiment d'être vraiment vu·e ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment de la journée où tu ressens le plus le besoin de connexion avec moi ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on fait ensemble et qui te recharge profondément ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te rend difficile de te laisser aller émotionnellement parfois ?", 'diff' => 2],
                ['text' => "Y a-t-il un sujet sur lequel tu aimerais qu'on soit plus proches sans savoir comment le dire ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te fait sentir en sécurité dans notre relation ?", 'diff' => 2],
                ['text' => "Y a-t-il une limite que tu n'as jamais vraiment exprimée et que tu aurais besoin que je respecte ?", 'diff' => 3],
                ['text' => "Est-ce que tu as l'impression qu'on se touche assez — pas juste physiquement, mais qu'on se rejoint vraiment ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu aimerais partager avec moi mais qui te rend encore vulnérable ?", 'diff' => 3],
                ['text' => "Quel est le souvenir d'intimité avec moi qui te revient le plus souvent ?", 'diff' => 3],
                ['text' => "Est-ce qu'il arrive que tu te sentes seul·e même quand on est ensemble ?", 'diff' => 3],
            ],

            'Émotions' => [
                ['text' => "Quelle est l'émotion que tu exprimes le plus facilement ? Et la plus difficile ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te met de bonne humeur à coup sûr ?", 'diff' => 1],
                ['text' => "Y a-t-il une émotion que tu ressens souvent et pour laquelle tu n'as pas encore de mot ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait pleurer, même si tu n'en parles pas ?", 'diff' => 1],
                ['text' => "Quand tu es stressé·e, qu'est-ce que tu as besoin que je fasse — ou ne fasse pas ?", 'diff' => 2],
                ['text' => "Y a-t-il une émotion que tu refoules souvent et que tu aurais besoin d'exprimer plus ?", 'diff' => 2],
                ['text' => "Quel est le dernier moment où tu as ressenti une joie vraiment pure ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qui te rend triste en ce moment et dont tu n'as pas encore parlé ?", 'diff' => 2],
                ['text' => "Comment tu gères la colère — est-ce que tu l'exprimes, tu la ravales, tu fuis ?", 'diff' => 2],
                ['text' => "Y a-t-il une peur que tu portes seul·e sans oser la partager ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui te rend le plus anxieux·se dans notre relation en ce moment ?", 'diff' => 3],
                ['text' => "Y a-t-il un moment où tu as ressenti que je ne comprenais pas ce que tu vivais ?", 'diff' => 3],
                ['text' => "Quelle émotion t'a surpris·e ressentir dans notre relation, que tu n'avais pas anticipée ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens suffisamment vu·e dans ta douleur — ou est-ce que tu gères souvent seul·e ?", 'diff' => 3],
            ],

            'Avenir' => [
                ['text' => "Quel est le prochain truc qu'on n'a pas encore fait ensemble et dont tu rêves ?", 'diff' => 1],
                ['text' => "Si tu pouvais planifier notre prochaine grande aventure, ce serait quoi ?", 'diff' => 1],
                ['text' => "Dans dix ans, quelle version de nous deux t'enthousiasme le plus ?", 'diff' => 1],
                ['text' => "Y a-t-il un projet qu'on a évoqué et qu'on n'a pas encore lancé ?", 'diff' => 1],
                ['text' => "Quelle est la peur qui te retient le plus d'avancer vers ce que tu veux ?", 'diff' => 2],
                ['text' => "Si l'argent n'était pas un obstacle, qu'est-ce qu'on ferait en premier ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose dans notre futur sur lequel tu ne t'es jamais vraiment exprimé·e ?", 'diff' => 2],
                ['text' => "Quelle version de toi dans cinq ans te rend fier·ère — et est-ce que je m'y trouve ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu fais aujourd'hui que tu sais que tu regretteras si tu ne changes pas ?", 'diff' => 2],
                ['text' => "Quelle est la grande peur que tu as pour notre futur et dont tu n'as jamais vraiment parlé ?", 'diff' => 3],
                ['text' => "Est-ce que tu sens qu'on va dans la même direction — si non, où est la divergence ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu sacrifies pour notre relation et que tu aimerais récupérer ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui devrait changer dans nos vies dans les deux prochaines années pour que tu te sentes accompli·e ?", 'diff' => 3],
            ],

            'Passé' => [
                ['text' => "Quel est le souvenir d'enfance que tu aimes le plus raconter ?", 'diff' => 1],
                ['text' => "Quel est le moment de ta vie dont tu es le plus fier·ère ?", 'diff' => 1],
                ['text' => "Y a-t-il une période de ta vie que tu ne changerais pour rien au monde ?", 'diff' => 1],
                ['text' => "Quel est le meilleur conseil qu'on t'ait jamais donné ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment charnière dans ta vie que peu de gens connaissent vraiment ?", 'diff' => 2],
                ['text' => "Quelle est la décision la plus difficile que tu aies prise — et est-ce que tu la referais ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'as pas dit à quelqu'un et que tu aurais voulu exprimer ?", 'diff' => 2],
                ['text' => "Quelle relation (amitié, famille) t'a le plus marqué·e avant nous ?", 'diff' => 2],
                ['text' => "Y a-t-il une version passée de toi que tu ne reconnais plus aujourd'hui ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose dans ton passé que tu n'as jamais vraiment digéré ?", 'diff' => 3],
                ['text' => "Quel est ton plus grand regret — et est-ce qu'il influence encore ta façon d'être aujourd'hui ?", 'diff' => 3],
                ['text' => "Y a-t-il une partie de toi que ton passé a blessée et qu'on n'a jamais vraiment abordée ensemble ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose dans ton histoire que tu as peur que je juge si tu me le dis ?", 'diff' => 3],
            ],

            'Valeurs' => [
                ['text' => "Quelle est la valeur que tes parents t'ont transmise et que tu portes encore ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose pour lequel tu ne ferais aucune concession ?", 'diff' => 1],
                ['text' => "Qu'est-ce que la loyauté signifie concrètement pour toi ?", 'diff' => 1],
                ['text' => "Si tu devais choisir entre ambition et sécurité, tu choisirais quoi — et ça dépend de quoi ?", 'diff' => 2],
                ['text' => "Y a-t-il une valeur que tu admirais avant et que tu remets en question aujourd'hui ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on fait comme couple et qui ne correspond pas vraiment à tes valeurs ?", 'diff' => 2],
                ['text' => "Qu'est-ce que la réussite signifie pour toi — est-ce que ça a changé depuis qu'on est ensemble ?", 'diff' => 2],
                ['text' => "Y a-t-il une chose sur laquelle tu n'es pas d'accord avec moi et dont tu n'as jamais vraiment parlé ?", 'diff' => 3],
                ['text' => "Quelle est la limite morale que tu ne franchirais jamais, même sous pression ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on partage les mêmes valeurs fondamentales — et là où on diverge, ça t'inquiète ?", 'diff' => 3],
                ['text' => "Y a-t-il une croyance héritée de ta famille dont tu essaies encore de te défaire ?", 'diff' => 3],
            ],

            'Projets' => [
                ['text' => "Quel est le projet qu'on n'a jamais lancé et qui te tient encore à cœur ?", 'diff' => 1],
                ['text' => "Si tu devais apprendre quelque chose de nouveau cette année, ce serait quoi ?", 'diff' => 1],
                ['text' => "Y a-t-il un endroit dans le monde où tu aimerais qu'on parte s'installer un moment ?", 'diff' => 1],
                ['text' => "Quel est le projet personnel que tu portes en ce moment et comment je pourrais t'aider ?", 'diff' => 2],
                ['text' => "Y a-t-il un rêve que tu as mis de côté pour des raisons pratiques ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on reporte depuis trop longtemps et qu'on devrait vraiment faire ?", 'diff' => 2],
                ['text' => "Si tu avais six mois complètement libres, tu ferais quoi ?", 'diff' => 2],
                ['text' => "Y a-t-il un projet qui t'effraie autant qu'il t'attire ?", 'diff' => 3],
                ['text' => "Est-ce que tu as l'impression qu'on se soutient mutuellement dans nos ambitions individuelles ?", 'diff' => 3],
                ['text' => "Y a-t-il un projet commun sur lequel on n'est pas alignés et qu'on évite d'aborder ?", 'diff' => 3],
            ],

            'Passions' => [
                ['text' => "Quelle est la passion que tu avais enfant et que tu n'as jamais vraiment abandonnée ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait perdre la notion du temps quand tu le fais ?", 'diff' => 1],
                ['text' => "Y a-t-il une passion que tu as développée depuis qu'on est ensemble ?", 'diff' => 1],
                ['text' => "Est-ce que tu as l'impression que j'encourage vraiment ce qui te passionne ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais partager avec moi mais que tu penses que je ne comprendrais pas ?", 'diff' => 2],
                ['text' => "Quelle est la passion de l'autre que tu admires le plus, même si elle n'est pas la tienne ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a une passion que tu as mise en pause pour notre vie commune ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose qui te manque et que tu n'oses pas reprendre de peur de prendre trop de place ?", 'diff' => 3],
                ['text' => "Quelle partie de toi tes passions révèlent-elles que le reste de ta vie ne montre pas ?", 'diff' => 3],
            ],

            'Créativité' => [
                ['text' => "Si tu pouvais créer quelque chose sans limite de budget ni de compétence, ce serait quoi ?", 'diff' => 1],
                ['text' => "Quel artiste, musicien ou créateur t'a marqué·e le plus profondément ?", 'diff' => 1],
                ['text' => "Y a-t-il une œuvre (film, livre, chanson) qui a changé quelque chose en toi ?", 'diff' => 1],
                ['text' => "Si notre relation était une chanson, laquelle serait-ce — et pourquoi ?", 'diff' => 1],
                ['text' => "Invente un rituel hebdomadaire absurde qu'on pourrait vraiment adopter.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Décris notre vie dans dix ans comme si tu racontais la première scène d'un film.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Si tu devais créer un objet qui me représente, à quoi ressemblerait-il ?", 'diff' => 2],
                ['text' => "Quelle couleur associes-tu à notre relation en ce moment, et pourquoi ?", 'diff' => 2],
                ['text' => "Si notre couple avait un manifeste, quelle serait la première phrase ?", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Crée une liste de 5 règles absurdes pour vivre ensemble — tu as 2 minutes.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Décris en 30 secondes la journée parfaite qu'on n'a jamais eu le temps de faire.", 'diff' => 1, 'is_bonus' => true],
            ],

            'Jeux' => [
                ['text' => "Sans réfléchir : dis trois mots qui décrivent l'autre.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Mime une de nos habitudes ou une situation vécue — l'autre doit deviner.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Chacun raconte un mensonge et une vérité sur lui-même — l'autre doit trouver lequel.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Dis le premier mot qui te vient quand tu penses à moi. Maintenant explique-le.", 'diff' => 1],
                ['text' => "Propose un défi à relever ensemble dans les prochaines 48h.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Imite la façon dont je réagis quand je suis stressé·e.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Sans regarder — combien de temps on se connaît, exactement ?", 'diff' => 1],
                ['text' => "Quelle est la chose la plus folle que tu ferais si tu étais sûr·e de ne pas être jugé·e ?", 'diff' => 2],
                ['text' => "Décris notre premier rendez-vous du point de vue de l'autre.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Chacun finit la phrase : 'La chose que j'aime le plus chez toi c'est...' — en même temps.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Proposez chacun une question à ajouter à ce jeu.", 'diff' => 3, 'is_bonus' => true],
            ],

            'Attentes' => [
                ['text' => "Qu'est-ce que tu avais imaginé de notre relation au début, que tu as dû réviser ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu attendais de moi que j'ai dépassé ?", 'diff' => 1],
                ['text' => "Qu'est-ce que tu avais besoin d'entendre récemment et que tu n'as pas entendu ?", 'diff' => 2],
                ['text' => "Est-ce que tu as l'impression qu'on est sur la même longueur d'onde en ce moment ?", 'diff' => 2],
                ['text' => "Y a-t-il une attente implicite que tu as de moi dont on n'a jamais vraiment parlé ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu aimerais qu'on fasse plus souvent ensemble — concrètement ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que j'attends de toi et qui te pèse ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens parfois obligé·e d'être une certaine version de toi avec moi ?", 'diff' => 3],
                ['text' => "Y a-t-il une attente sur laquelle tu as renoncé sans me le dire ?", 'diff' => 3],
                ['text' => "Quelle est la chose que tu attendrais d'un·e partenaire idéal·e et que tu n'as jamais demandée ?", 'diff' => 3],
            ],

            'Croyances' => [
                ['text' => "Quelle croyance sur l'amour as-tu héritée de ta famille — et est-ce que tu y adhères encore ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu croyais impossible avant notre relation ?", 'diff' => 1],
                ['text' => "Quelle est la chose sur laquelle tu n'as jamais changé d'avis, même sous pression ?", 'diff' => 2],
                ['text' => "Y a-t-il une croyance sur les hommes ou les femmes que ton expérience avec moi a remise en question ?", 'diff' => 2],
                ['text' => "Quelle est ta conviction la plus profonde sur ce qui fait durer une relation ?", 'diff' => 2],
                ['text' => "Est-ce que tu crois que l'amour suffit — ou qu'il faut autre chose pour tenir sur le long terme ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu croyais sur toi-même et que notre relation a contredit ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a une croyance sur nous deux que tu gardes pour toi par peur d'être jugé·e ?", 'diff' => 3],
                ['text' => "Y a-t-il une vérité que tu connais sur notre relation mais que tu évites de dire à voix haute ?", 'diff' => 3],
            ],

            'Sexualité' => [
                ['text' => "Y a-t-il quelque chose qu'on n'a jamais essayé et qui t'intéresse ?", 'diff' => 1],
                ['text' => "Quelle est ta façon préférée de montrer ou recevoir du désir ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment ou un contexte où tu te sens le plus à l'aise physiquement avec moi ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a quelque chose qu'on faisait avant et qu'on a arrêté, qui te manque ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais me dire sur ce plan-là et que tu n'as jamais osé ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te coupe le plus facilement l'envie — et est-ce que je le sais ?", 'diff' => 2],
                ['text' => "Est-ce que tu te sens assez libre pour dire non ou exprimer une limite sans craindre de blesser ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose dans notre intimité physique que tu aimerais qu'on améliore ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens désiré·e comme tu en as besoin — si non, qu'est-ce qui manque ?", 'diff' => 3],
                ['text' => "Y a-t-il une conversation qu'on n'a jamais eue sur ce sujet et qu'il serait temps d'avoir ?", 'diff' => 3],
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
