<?php
// src/DataFixtures/CardFixturesMaster.php
//
// Fichier maître — 25 cartes par thème × 13 thèmes = 325 cartes.
// Questions ancrées dans le vécu, formulées pour créer de vraies conversations.
//
// Usage : php bin/console doctrine:fixtures:load --append --group=CardFixturesMaster

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CardFixturesMaster extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themeRepo = $manager->getRepository(Theme::class);

        // Structure : ['text' => '...', 'diff' => 1|2|3, 'is_bonus' => true (optionnel)]
        // diff 1 = facile · diff 2 = moyen · diff 3 = profond/difficile

        $data = [

            // ─────────────────────────────────────────────────────────────────
            // ATTENTES
            // ─────────────────────────────────────────────────────────────────
            'Attentes' => [
                ['text' => "Qu'est-ce que tu avais imaginé de notre relation au début, que tu as dû réviser ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu attendais de moi que j'ai dépassé ?", 'diff' => 1],
                ['text' => "Qu'est-ce que tu apprécies le plus dans la façon dont on communique ?", 'diff' => 1],
                ['text' => "Qu'est-ce que tu avais besoin d'entendre récemment et que tu n'as pas entendu ?", 'diff' => 2],
                ['text' => "Est-ce que tu as l'impression qu'on est sur la même longueur d'onde en ce moment ?", 'diff' => 2],
                ['text' => "Y a-t-il une attente implicite que tu as de moi dont on n'a jamais vraiment parlé ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu aimerais qu'on fasse plus souvent ensemble — concrètement ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu attends de moi lors d'un conflit — que tu n'as jamais dit explicitement ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu attends de moi quand tu traverses quelque chose de difficile — présence, solutions, silence ?", 'diff' => 2],
                ['text' => "Est-ce qu'il t'arrive de te sentir incompris·e par moi — dans quelle situation ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que j'attends de toi et qui te pèse ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens parfois obligé·e d'être une certaine version de toi avec moi ?", 'diff' => 3],
                ['text' => "Y a-t-il une attente sur laquelle tu as renoncé sans me le dire ?", 'diff' => 3],
                ['text' => "Quelle est la chose que tu attendrais d'un·e partenaire idéal·e et que tu n'as jamais demandée ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu attends de notre relation que tu n'as pas encore eu le courage de formuler ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on gère bien les attentes déçues — ou est-ce qu'on les laisse s'accumuler ?", 'diff' => 3],
                ['text' => "Y a-t-il une attente que tu as abandonnée sur notre relation — et est-ce que c'était un deuil ou un soulagement ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu n'as jamais osé demander par peur de la réponse ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose que tu fais pour moi par obligation plutôt que par envie ?", 'diff' => 2],
                ['text' => "Y a-t-il une attente que tu as de toi-même dans cette relation et que tu n'arrives pas à tenir ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu espérais que notre relation t'apporte et qu'elle t'a finalement apporté autrement ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses qu'on se dit suffisamment ce dont on a besoin l'un·e de l'autre ?", 'diff' => 2],
                ['text' => "Y a-t-il une période où tu as senti qu'on n'était plus alignés sur ce qu'on voulait ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui te donnerait l'impression que notre relation a atteint quelque chose d'important ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'attends plus de moi — et est-ce que tu l'as accepté ou tu le portes encore ?", 'diff' => 3],
            ],

            // ─────────────────────────────────────────────────────────────────
            // AVENIR
            // ─────────────────────────────────────────────────────────────────
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
                ['text' => "Si tu devais choisir un seul objectif commun pour cette année, ce serait lequel ?", 'diff' => 2],
                ['text' => "Quelle version de notre quotidien t'attire le plus dans 5 ans ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu veux accomplir seul·e avant qu'on avance ensemble vers autre chose ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a une décision importante qu'on devrait prendre et qu'on reporte ?", 'diff' => 3],
                ['text' => "Quelle est la chose dont tu as le plus peur de regretter dans 20 ans ?", 'diff' => 3],
                ['text' => "Si tu pouvais nous projeter dans 3 ans, quelle serait ta plus grande fierté ?", 'diff' => 2],
                ['text' => "Y a-t-il un rêve que tu as mis en veilleuse et que tu n'as pas renoncé à atteindre ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu imagines pour nos vieux jours — concrètement, pas un idéal abstrait ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a quelque chose dans notre futur immédiat qui t'inquiète et dont on ne parle pas ?", 'diff' => 3],
                ['text' => "Y a-t-il un chemin de vie qu'on n'a jamais envisagé et qui te plairait d'explorer ?", 'diff' => 2],
                ['text' => "Quelle est la prochaine étape de notre relation dont tu as envie mais que tu n'as pas encore nommée ?", 'diff' => 2],
                ['text' => "Si tu devais écrire la promesse qu'on se fait pour les dix prochaines années, elle dirait quoi ?", 'diff' => 3],
            ],

            // ─────────────────────────────────────────────────────────────────
            // COUPLE
            // ─────────────────────────────────────────────────────────────────
            'Couple' => [
                ['text' => "Cite une habitude qu'on a développée ensemble et qui te rend heureux·se.", 'diff' => 1],
                ['text' => "Qu'est-ce que tu faisais en premier quand tu avais une bonne nouvelle, avant de me connaître ?", 'diff' => 1],
                ['text' => "Quel petit geste du quotidien de l'autre t'a fait réaliser que tu l'aimais vraiment ?", 'diff' => 1],
                ['text' => "Si tu devais m'expliquer à quelqu'un en trois mots, lesquels choisirais-tu ?", 'diff' => 1],
                ['text' => "Quel moment passé ensemble te revient en tête quand tu penses à nous ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que je fais spontanément qui te fait toujours sourire ?", 'diff' => 1],
                ['text' => "Quelle est la chose la plus drôle qu'on ait vécue ensemble ?", 'diff' => 1],
                ['text' => "Quel est le plat ou l'endroit que tu associes immédiatement à nous deux ?", 'diff' => 1],
                ['text' => "La dernière fois que tu t'es senti·e vraiment soutenu·e par moi, c'était quand ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'oses pas encore me demander ?", 'diff' => 2],
                ['text' => "Comment sais-tu que je t'aime, concrètement — qu'est-ce qui te le montre ?", 'diff' => 2],
                ['text' => "Qu'est-ce qu'on a construit ensemble dont tu es le plus fier·ère ?", 'diff' => 2],
                ['text' => "Y a-t-il une période de notre relation qui t'a le plus fait grandir ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu ferais différemment si on recommençait depuis le début ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu n'avais jamais osé faire avant moi et que tu fais maintenant ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a un désaccord qu'on a eu et dont tu es finalement content·e qu'il ait eu lieu ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu t'es retenu·e de dire pendant une dispute et que tu aurais voulu exprimer ?", 'diff' => 3],
                ['text' => "Quel est le moment où tu as failli douter de nous, et qu'est-ce qui t'a fait rester ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu aimerais que je comprenne sur toi sans avoir à l'expliquer ?", 'diff' => 3],
                ['text' => "Y a-t-il un besoin que j'ai du mal à combler et dont tu n'as jamais vraiment parlé ?", 'diff' => 3],
                ['text' => "Si tu pouvais changer une seule chose dans notre façon de fonctionner ensemble, ce serait quoi ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens libre d'être totalement toi-même avec moi — sinon, où est la limite ?", 'diff' => 3],
                ['text' => "Qu'est-ce que notre relation t'a appris sur toi-même que tu n'aurais pas découvert seul·e ?", 'diff' => 2],
                ['text' => "Y a-t-il un moment précis où tu as su que tu voulais construire quelque chose avec moi ?", 'diff' => 1],
                ['text' => "Quelle est la chose que tu ne voudrais jamais perdre dans notre façon d'être ensemble ?", 'diff' => 2],
            ],

            // ─────────────────────────────────────────────────────────────────
            // CRÉATIVITÉ
            // ─────────────────────────────────────────────────────────────────
            'Créativité' => [
                ['text' => "Si tu pouvais créer quelque chose sans limite de budget ni de compétence, ce serait quoi ?", 'diff' => 1],
                ['text' => "Quel artiste, musicien ou créateur t'a marqué·e le plus profondément ?", 'diff' => 1],
                ['text' => "Y a-t-il une œuvre (film, livre, chanson) qui a changé quelque chose en toi ?", 'diff' => 1],
                ['text' => "Si notre relation était une chanson, laquelle serait-ce — et pourquoi ?", 'diff' => 1],
                ['text' => "Crée une liste de 5 règles absurdes pour vivre ensemble — tu as 2 minutes.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Décris notre vie dans dix ans comme si tu racontais la première scène d'un film.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Si tu devais créer un objet qui me représente, à quoi ressemblerait-il ?", 'diff' => 2],
                ['text' => "Quelle couleur associes-tu à notre relation en ce moment, et pourquoi ?", 'diff' => 2],
                ['text' => "Si notre couple avait un manifeste, quelle serait la première phrase ?", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Invente un rituel hebdomadaire absurde qu'on pourrait vraiment adopter.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Invente un surnom pour notre relation que personne d'autre ne comprendrait.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Si notre histoire était un livre, quel serait le titre du chapitre en cours ?", 'diff' => 1],
                ['text' => "Dessine en 60 secondes quelque chose qui représente ton humeur du moment — l'autre devine.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Invente un mot qui décrit quelque chose qu'on vit ensemble et qui n'a pas encore de nom.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Quelle chanson mettrait-on au générique de fin de notre histoire si c'était un film ?", 'diff' => 2],
                ['text' => "Crée une métaphore pour décrire où on en est dans notre relation en ce moment.", 'diff' => 2],
                ['text' => "Écris en 30 secondes la dédicace que tu mettrais dans le livre de notre vie.", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Si notre couple avait une devise officielle, quelle serait-elle ?", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Décris en 30 secondes la journée parfaite qu'on n'a jamais eu le temps de faire.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Si tu pouvais illustrer notre premier souvenir ensemble, comment il ressemblerait ?", 'diff' => 2],
                ['text' => "Quelle émotion est-ce que notre relation t'inspire en ce moment — sans utiliser un mot d'émotion classique ?", 'diff' => 3],
                ['text' => "Invente une tradition qu'on pourrait commencer cette année.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Si on écrivait une chanson ensemble, elle parlerait de quoi ?", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Quelle image ou scène te vient à l'esprit quand tu penses à notre relation au meilleur de sa forme ?", 'diff' => 2],
                ['text' => "Si tu devais transformer une de nos disputes en comédie, laquelle choisirais-tu — et comment tu la raconterais ?", 'diff' => 3, 'is_bonus' => true],
            ],

            // ─────────────────────────────────────────────────────────────────
            // CROYANCES
            // ─────────────────────────────────────────────────────────────────
            'Croyances' => [
                ['text' => "Quelle croyance sur l'amour as-tu héritée de ta famille — et est-ce que tu y adhères encore ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu croyais impossible avant notre relation ?", 'diff' => 1],
                ['text' => "Quelle est la chose sur laquelle tu n'as jamais changé d'avis, même sous pression ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que tu croyais sur l'amour à 20 ans qui te fait sourire aujourd'hui ?", 'diff' => 1],
                ['text' => "Quelle est la leçon de vie que tu as apprise à la dure et que tu ne voudrais pas réapprendre ?", 'diff' => 1],
                ['text' => "Y a-t-il une croyance sur les hommes ou les femmes que ton expérience avec moi a remise en question ?", 'diff' => 2],
                ['text' => "Quelle est ta conviction la plus profonde sur ce qui fait durer une relation ?", 'diff' => 2],
                ['text' => "Est-ce que tu crois que l'amour suffit — ou qu'il faut autre chose pour tenir sur le long terme ?", 'diff' => 2],
                ['text' => "Est-ce que tu crois au destin — et est-ce que notre rencontre a changé ta réponse ?", 'diff' => 2],
                ['text' => "Y a-t-il une conviction que tu portes seul·e et que tu penses que peu de gens comprendraient ?", 'diff' => 2],
                ['text' => "Quelle est la chose sur laquelle tu as changé d'avis grâce à moi ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses que les gens peuvent vraiment changer en profondeur ?", 'diff' => 2],
                ['text' => "Y a-t-il une peur irrationnelle que tu sais irrationnelle mais que tu ne peux pas t'empêcher d'avoir ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu crois sur toi-même et qui t'empêche d'avancer ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu crois sur toi-même et que notre relation a contredit ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a une croyance que tu gardes pour toi par peur d'être jugé·e ?", 'diff' => 3],
                ['text' => "Y a-t-il une vérité que tu connais sur notre relation mais que tu évites de dire à voix haute ?", 'diff' => 3],
                ['text' => "Quelle est la croyance que tu voudrais transmettre à tes enfants — ou à la génération suivante ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose dans nos façons de voir le monde qui te semble incompatible ?", 'diff' => 3],
                ['text' => "Qu'est-ce que tu crois sur le pardon — as-tu pardonné quelque chose que tu n'as jamais dit ?", 'diff' => 3],
                ['text' => "Y a-t-il une idée reçue sur le couple que tu as longtemps crue et que tu as dû déconstruire ?", 'diff' => 2],
                ['text' => "Quelle est la croyance qui te protège — et est-ce qu'elle te limite aussi ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose que tu crois dur comme fer et que tu n'as jamais vraiment justifié ?", 'diff' => 2],
                ['text' => "Y a-t-il une conviction religieuse, spirituelle ou philosophique qui guide tes décisions sans que je le sache vraiment ?", 'diff' => 3],
                ['text' => "Quelle croyance sur toi-même voudrais-tu te débarrasser définitivement ?", 'diff' => 3],
            ],

            // ─────────────────────────────────────────────────────────────────
            // ÉMOTIONS
            // ─────────────────────────────────────────────────────────────────
            'Émotions' => [
                ['text' => "Quelle est l'émotion que tu exprimes le plus facilement ? Et la plus difficile ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te met de bonne humeur à coup sûr ?", 'diff' => 1],
                ['text' => "Y a-t-il une émotion que tu ressens souvent et pour laquelle tu n'as pas encore de mot ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait pleurer, même si tu n'en parles pas ?", 'diff' => 1],
                ['text' => "Quel est le dernier moment où tu as ressenti une joie vraiment pure ?", 'diff' => 2],
                ['text' => "Quand tu es stressé·e, qu'est-ce que tu as besoin que je fasse — ou ne fasse pas ?", 'diff' => 2],
                ['text' => "Y a-t-il une émotion que tu refoules souvent et que tu aurais besoin d'exprimer plus ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qui te rend triste en ce moment et dont tu n'as pas encore parlé ?", 'diff' => 2],
                ['text' => "Comment tu gères la colère — est-ce que tu l'exprimes, tu la ravales, tu fuis ?", 'diff' => 2],
                ['text' => "Y a-t-il une peur que tu portes seul·e sans oser la partager ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui te rend le plus anxieux·se dans notre relation en ce moment ?", 'diff' => 3],
                ['text' => "Y a-t-il un moment où tu as ressenti que je ne comprenais pas ce que tu vivais ?", 'diff' => 3],
                ['text' => "Quelle émotion t'a surpris·e ressentir dans notre relation, que tu n'avais pas anticipée ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens suffisamment vu·e dans ta douleur — ou est-ce que tu gères souvent seul·e ?", 'diff' => 3],
                ['text' => "Y a-t-il une émotion que tu associes à moi et dont tu ne m'as jamais parlé ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te fait te sentir en sécurité émotionnellement ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses qu'on gère bien les émotions difficiles ensemble — ou est-ce qu'on les évite ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose qui t'émeut profondément et que tu n'as jamais partagé avec moi ?", 'diff' => 2],
                ['text' => "Quelle est l'émotion qui te fait le plus honte d'ressentir ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui te calme le plus vite quand tu es submergé·e ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que je fais qui te blesse, même sans le vouloir ?", 'diff' => 3],
                ['text' => "Est-ce que tu te souviens d'un moment où tu as ressenti de la fierté pour moi — lequel ?", 'diff' => 1],
                ['text' => "Quelle émotion est la plus difficile à accueillir chez l'autre — chez moi en particulier ?", 'diff' => 3],
                ['text' => "Y a-t-il une période de ta vie où tu as été particulièrement déconnecté·e de tes émotions ?", 'diff' => 2],
                ['text' => "Est-ce que tu exprimes plus facilement tes émotions depuis qu'on est ensemble — ou moins ?", 'diff' => 2],
            ],

            // ─────────────────────────────────────────────────────────────────
            // INTIMITÉ
            // ─────────────────────────────────────────────────────────────────
            'Intimité' => [
                ['text' => "À quel moment te sens-tu le plus proche de moi physiquement ?", 'diff' => 1],
                ['text' => "Quelle est la forme de tendresse que tu préfères recevoir ?", 'diff' => 1],
                ['text' => "Y a-t-il un endroit sur toi que tu aimes qu'on touche et qu'on touche rarement ?", 'diff' => 1],
                ['text' => "Quel geste de ma part te donne le sentiment d'être vraiment vu·e ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment de la journée où tu ressens le plus le besoin de connexion avec moi ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on fait ensemble et qui te recharge profondément ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te rend difficile de te laisser aller émotionnellement parfois ?", 'diff' => 2],
                ['text' => "Y a-t-il un sujet sur lequel tu aimerais qu'on soit plus proches sans savoir comment le dire ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te fait sentir en sécurité dans notre relation ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te met le plus à l'aise pour être vulnérable devant moi ?", 'diff' => 1],
                ['text' => "Y a-t-il une limite que tu n'as jamais vraiment exprimée et que tu aurais besoin que je respecte ?", 'diff' => 3],
                ['text' => "Est-ce que tu as l'impression qu'on se touche assez — pas juste physiquement, mais qu'on se rejoint vraiment ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu aimerais partager avec moi mais qui te rend encore vulnérable ?", 'diff' => 3],
                ['text' => "Quel est le souvenir d'intimité avec moi qui te revient le plus souvent ?", 'diff' => 2],
                ['text' => "Est-ce qu'il arrive que tu te sentes seul·e même quand on est ensemble ?", 'diff' => 3],
                ['text' => "Y a-t-il une façon d'être proches qu'on n'a pas encore vraiment explorée ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses qu'on se donne assez de temps vraiment dédié l'un·e à l'autre ?", 'diff' => 2],
                ['text' => "Y a-t-il un moment où tu t'es senti·e rejeté·e par moi, même involontairement ?", 'diff' => 3],
                ['text' => "Qu'est-ce qui crée le plus de distance entre nous quand on n'est pas bien ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu n'oses pas faire ou dire parce que tu as peur de ma réaction ?", 'diff' => 3],
                ['text' => "Quel rituel d'intimité voudrais-tu qu'on crée ou qu'on retrouve ?", 'diff' => 2],
                ['text' => "Est-ce que tu ressens parfois le besoin d'espace — et est-ce que tu le demandes librement ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te fait sentir vraiment aimé·e, au-delà des mots ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que j'ai fait qui t'a touché·e profondément et que tu n'as jamais vraiment dit ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses qu'on est suffisamment curieux·ses l'un·e de l'autre encore aujourd'hui ?", 'diff' => 2],
            ],

            // ─────────────────────────────────────────────────────────────────
            // JEUX
            // ─────────────────────────────────────────────────────────────────
            'Jeux' => [
                ['text' => "Sans réfléchir : dis trois mots qui décrivent l'autre.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Mime une de nos habitudes ou une situation vécue — l'autre doit deviner.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Chacun raconte un mensonge et une vérité sur lui-même — l'autre doit trouver lequel.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Dis le premier mot qui te vient quand tu penses à moi. Maintenant explique-le.", 'diff' => 1],
                ['text' => "Sans regarder — combien de temps on se connaît, exactement ?", 'diff' => 1],
                ['text' => "Si on était un duo de film, on serait qui ?", 'diff' => 1],
                ['text' => "Chacun complète la phrase : 'Tu ne sais probablement pas que je...'", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Nomme une chose que tu ferais mieux que moi — et une que tu ferais moins bien.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Chacun finit la phrase : 'La chose que j'aime le plus chez toi c'est...' — en même temps.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Propose un défi à relever ensemble dans les prochaines 48h.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Imite la façon dont je réagis quand je suis stressé·e.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Décris notre premier rendez-vous du point de vue de l'autre.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Décris notre relation comme si tu la racontais à quelqu'un qui ne nous connaît pas.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Chacun dit ce qu'il pense que l'autre va répondre à la prochaine carte.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Quelle est la chose la plus inattendue que tu aies découverte sur moi ?", 'diff' => 2],
                ['text' => "Raconte en 1 minute la première impression que tu as eue de moi. Sois honnête.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Quelle est la chose la plus folle que tu ferais si tu étais sûr·e de ne pas être jugé·e ?", 'diff' => 2],
                ['text' => "Chacun pose une question qu'il n'a jamais osé poser.", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Proposez chacun une question à ajouter à ce jeu.", 'diff' => 3, 'is_bonus' => true],
                ['text' => "Ferme les yeux — qu'est-ce que tu entends, sens ou ressens en ce moment ? Dis-le sans filtre.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Qu'est-ce que tu ferais si tu savais que tu n'échouerais pas ?", 'diff' => 2],
                ['text' => "L'autre a 30 secondes pour te convaincre d'essayer quelque chose qu'il aime et que tu n'as jamais fait.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Chacun énonce une règle qu'on devrait s'imposer cette semaine.", 'diff' => 2, 'is_bonus' => true],
                ['text' => "Qu'est-ce que l'autre ferait s'il gagnait 10 000 € demain ? Tu as 10 secondes pour répondre à sa place.", 'diff' => 1, 'is_bonus' => true],
                ['text' => "Qui de vous deux est le plus têtu·e — et dans quelle situation ?", 'diff' => 2, 'is_bonus' => true],
            ],

            // ─────────────────────────────────────────────────────────────────
            // PASSÉ
            // ─────────────────────────────────────────────────────────────────
            'Passé' => [
                ['text' => "Quel est le souvenir d'enfance que tu aimes le plus raconter ?", 'diff' => 1],
                ['text' => "Quel est le moment de ta vie dont tu es le plus fier·ère ?", 'diff' => 1],
                ['text' => "Y a-t-il une période de ta vie que tu ne changerais pour rien au monde ?", 'diff' => 1],
                ['text' => "Quel est le meilleur conseil qu'on t'ait jamais donné ?", 'diff' => 1],
                ['text' => "Quelle était ta passion d'enfant que les adultes autour de toi ne prenaient pas au sérieux ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment charnière dans ta vie que peu de gens connaissent vraiment ?", 'diff' => 2],
                ['text' => "Quelle est la décision la plus difficile que tu aies prise — et est-ce que tu la referais ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'as pas dit à quelqu'un et que tu aurais voulu exprimer ?", 'diff' => 2],
                ['text' => "Quelle relation (amitié, famille) t'a le plus marqué·e avant nous ?", 'diff' => 2],
                ['text' => "Y a-t-il une version passée de toi que tu ne reconnais plus aujourd'hui ?", 'diff' => 2],
                ['text' => "Qu'est-ce que tu aurais voulu qu'on te dise quand tu avais 15 ans ?", 'diff' => 2],
                ['text' => "Y a-t-il une erreur que tu as faite et dont tu es finalement reconnaissant·e ?", 'diff' => 2],
                ['text' => "Quel a été le tournant le plus silencieux de ta vie — celui que peu de gens ont vu ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose dans ton passé que tu n'as jamais vraiment digéré ?", 'diff' => 3],
                ['text' => "Quel est ton plus grand regret — et est-ce qu'il influence encore ta façon d'être aujourd'hui ?", 'diff' => 3],
                ['text' => "Y a-t-il une partie de toi que ton passé a blessée et qu'on n'a jamais vraiment abordée ensemble ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose dans ton histoire que tu as peur que je juge si tu me le dis ?", 'diff' => 3],
                ['text' => "Y a-t-il quelqu'un de ton passé à qui tu penses encore et dont tu n'as jamais parlé ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a une période de ta vie que tu aimerais revisiter — pas changer, juste revivre ?", 'diff' => 1],
                ['text' => "Quel souvenir te revient toujours quand tu penses à ta famille ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu as fait par peur et dont tu es fier·ère malgré tout ?", 'diff' => 2],
                ['text' => "Quelle relation t'a le plus appris sur toi-même, avant moi ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses que ton enfance influence encore notre relation aujourd'hui — comment ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu as perdu dans le passé et dont tu portes encore le deuil ?", 'diff' => 3],
                ['text' => "Quel moment de faiblesse de ton passé t'a finalement rendu·e plus fort·e ?", 'diff' => 2],
            ],

            // ─────────────────────────────────────────────────────────────────
            // PASSIONS
            // ─────────────────────────────────────────────────────────────────
            'Passions' => [
                ['text' => "Quelle est la passion que tu avais enfant et que tu n'as jamais vraiment abandonnée ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait perdre la notion du temps quand tu le fais ?", 'diff' => 1],
                ['text' => "Y a-t-il une passion que tu as développée depuis qu'on est ensemble ?", 'diff' => 1],
                ['text' => "Y a-t-il une passion secrète que peu de gens autour de toi connaissent ?", 'diff' => 1],
                ['text' => "Quel talent chez toi penses-tu que je n'ai pas encore vraiment découvert ?", 'diff' => 1],
                ['text' => "Est-ce que tu as l'impression que j'encourage vraiment ce qui te passionne ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais partager avec moi mais que tu penses que je ne comprendrais pas ?", 'diff' => 2],
                ['text' => "Quelle est la passion de l'autre que tu admires le plus, même si elle n'est pas la tienne ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a un domaine dans lequel tu aimerais devenir vraiment expert·e ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu faisais avec passion et que tu as arrêté à cause du regard des autres ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui t'a donné le plus de satisfaction cette année — pas la fierté, la vraie satisfaction ?", 'diff' => 2],
                ['text' => "Quelle est la dernière fois que tu as fait quelque chose juste pour le plaisir, sans objectif ?", 'diff' => 1],
                ['text' => "Est-ce que tu as l'impression d'avoir assez de temps et d'espace pour tes passions dans notre vie ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu aimes profondément et dont tu as peur de parler par crainte d'être jugé·e ?", 'diff' => 3],
                ['text' => "Si tu devais transmettre une seule passion à quelqu'un que tu aimes, laquelle ce serait ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on se laisse assez de liberté l'un·e à l'autre pour nos passions individuelles ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a une passion que tu as mise en pause pour notre vie commune ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu n'oses pas reprendre de peur de prendre trop de place ?", 'diff' => 3],
                ['text' => "Quelle partie de toi tes passions révèlent-elles que le reste de ta vie ne montre pas ?", 'diff' => 3],
                ['text' => "Y a-t-il une activité qu'on n'a jamais faite ensemble et qui te plairait vraiment ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a quelque chose que j'aime et qui t'intrigue même si ce n'est pas ta passion ?", 'diff' => 2],
                ['text' => "Quelle est la passion que tu aurais aimé découvrir plus tôt dans ta vie ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu voudrais apprendre et dont tu n'as jamais parlé ?", 'diff' => 1],
                ['text' => "Est-ce que ta passion principale dit quelque chose de toi que les mots n'arrivent pas à exprimer ?", 'diff' => 3],
                ['text' => "Y a-t-il un endroit dans le monde que ta passion t'a envie de visiter ?", 'diff' => 1],
            ],

            // ─────────────────────────────────────────────────────────────────
            // PROJETS
            // ─────────────────────────────────────────────────────────────────
            'Projets' => [
                ['text' => "Quel est le projet qu'on n'a jamais lancé et qui te tient encore à cœur ?", 'diff' => 1],
                ['text' => "Si tu devais apprendre quelque chose de nouveau cette année, ce serait quoi ?", 'diff' => 1],
                ['text' => "Y a-t-il un endroit dans le monde où tu aimerais qu'on parte s'installer un moment ?", 'diff' => 1],
                ['text' => "Y a-t-il un endroit que tu veux absolument qu'on visite ensemble avant la fin de l'année ?", 'diff' => 1],
                ['text' => "Quel est le projet personnel que tu portes en ce moment et comment je pourrais t'aider ?", 'diff' => 2],
                ['text' => "Y a-t-il un rêve que tu as mis de côté pour des raisons pratiques ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on reporte depuis trop longtemps et qu'on devrait vraiment faire ?", 'diff' => 2],
                ['text' => "Si tu avais six mois complètement libres, tu ferais quoi ?", 'diff' => 2],
                ['text' => "Si tu pouvais changer de carrière demain sans risque, tu ferais quoi ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on avait prévu de faire ensemble et qu'on n'a jamais fait ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a un projet de vie sur lequel tu n'es pas encore sûr·e — déménagement, reconversion, enfants… ?", 'diff' => 2],
                ['text' => "Quel obstacle concret t'empêche d'avancer vers ce que tu veux vraiment ?", 'diff' => 2],
                ['text' => "Quel est le projet qui t'enthousiasme le plus en ce moment dans ta vie perso ou pro ?", 'diff' => 1],
                ['text' => "Y a-t-il un projet qui t'effraie autant qu'il t'attire ?", 'diff' => 3],
                ['text' => "Est-ce que tu as l'impression qu'on se soutient équitablement dans nos ambitions respectives ?", 'diff' => 3],
                ['text' => "Y a-t-il un projet commun sur lequel on n'est pas alignés et qu'on évite d'aborder ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu fais pour nous mais pas pour toi — et est-ce que ça te pèse ?", 'diff' => 3],
                ['text' => "Y a-t-il un projet sur lequel tu aurais besoin de mon aide, mais tu n'as pas osé demander ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a quelque chose que tu veux créer — une entreprise, un espace, quelque chose de concret ?", 'diff' => 2],
                ['text' => "Y a-t-il un projet qu'on devrait arrêter ou abandonner pour se concentrer sur ce qui compte vraiment ?", 'diff' => 3],
                ['text' => "Quel est le projet le plus ambitieux que tu aies déjà mené à bien ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a quelque chose qu'on n'a jamais planifié ensemble et qu'on devrait enfin faire ?", 'diff' => 2],
                ['text' => "Y a-t-il un projet qu'on a partagé et dont tu es particulièrement fier·ère ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te donne de l'énergie quand tu travailles sur un projet — le processus ou le résultat ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on devrait construire ensemble et qu'on n'a pas encore nommé ?", 'diff' => 3],
            ],

            // ─────────────────────────────────────────────────────────────────
            // SEXUALITÉ
            // ─────────────────────────────────────────────────────────────────
            'Sexualité' => [
                ['text' => "Y a-t-il quelque chose qu'on n'a jamais essayé et qui t'intéresse ?", 'diff' => 1],
                ['text' => "Quelle est ta façon préférée de montrer ou recevoir du désir ?", 'diff' => 1],
                ['text' => "Y a-t-il un moment ou un contexte où tu te sens le plus à l'aise physiquement avec moi ?", 'diff' => 1],
                ['text' => "Y a-t-il une ambiance ou un contexte qui te met particulièrement à l'aise ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te fait te sentir désirable ?", 'diff' => 1],
                ['text' => "Y a-t-il un mot ou une phrase que tu aimerais entendre plus souvent ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a quelque chose qu'on faisait avant et qu'on a arrêté, qui te manque ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais me dire sur ce plan-là et que tu n'as jamais osé ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui te coupe le plus facilement l'envie — et est-ce que je le sais ?", 'diff' => 2],
                ['text' => "Est-ce que tu te sens assez libre pour dire non ou exprimer une limite sans craindre de blesser ?", 'diff' => 2],
                ['text' => "Est-ce que tu penses qu'on parle suffisamment de ce qu'on aime ou n'aime pas ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu aimerais essayer mais que tu n'as pas encore proposé ?", 'diff' => 2],
                ['text' => "Qu'est-ce qui crée le plus de distance entre nous sur ce plan-là ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on faisait avec plus de légèreté avant et qu'on a perdu en chemin ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose dans notre intimité physique que tu aimerais qu'on améliore ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens désiré·e comme tu en as besoin — si non, qu'est-ce qui manque ?", 'diff' => 3],
                ['text' => "Y a-t-il une conversation qu'on n'a jamais eue sur ce sujet et qu'il serait temps d'avoir ?", 'diff' => 3],
                ['text' => "Y a-t-il un fantasme que tu n'as jamais partagé — même vaguement ?", 'diff' => 3],
                ['text' => "Est-ce que tu te sens toujours désiré·e de la même façon qu'au début — si non, qu'est-ce qui a changé ?", 'diff' => 3],
                ['text' => "Est-ce qu'il t'arrive de simuler quelque chose pour éviter une conversation — enthousiasme, envie… ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose que tu aimes particulièrement dans notre façon d'être ensemble physiquement ?", 'diff' => 1],
                ['text' => "Est-ce qu'il y a un endroit ou une situation où tu te sens le plus libre avec moi ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose que tu n'as jamais osé demander parce que tu ne savais pas comment le formuler ?", 'diff' => 3],
                ['text' => "Est-ce que la confiance joue un rôle dans ce que tu oses ou non — et où en es-tu là-dessus ?", 'diff' => 3],
                ['text' => "Y a-t-il quelque chose de simple qu'on pourrait faire plus souvent et qui ferait une vraie différence ?", 'diff' => 2],
            ],

            // ─────────────────────────────────────────────────────────────────
            // VALEURS
            // ─────────────────────────────────────────────────────────────────
            'Valeurs' => [
                ['text' => "Quelle est la valeur que tes parents t'ont transmise et que tu portes encore ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose pour lequel tu ne ferais aucune concession ?", 'diff' => 1],
                ['text' => "Qu'est-ce que la loyauté signifie concrètement pour toi ?", 'diff' => 1],
                ['text' => "Y a-t-il quelque chose que la société valorise et que toi tu n'arrives pas à trouver important ?", 'diff' => 1],
                ['text' => "Qu'est-ce qui te rend fier·ère de toi — pas ce que les autres admirent, ce que toi tu valorises ?", 'diff' => 1],
                ['text' => "Si tu devais choisir entre ambition et sécurité, tu choisirais quoi — et ça dépend de quoi ?", 'diff' => 2],
                ['text' => "Y a-t-il une valeur que tu admirais avant et que tu remets en question aujourd'hui ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose qu'on fait comme couple et qui ne correspond pas vraiment à tes valeurs ?", 'diff' => 2],
                ['text' => "Qu'est-ce que la réussite signifie pour toi — est-ce que ça a changé depuis qu'on est ensemble ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on fait comme couple et qui reflète parfaitement nos valeurs communes ?", 'diff' => 2],
                ['text' => "Quelle valeur aimerais-tu qu'on cultive plus dans notre relation ?", 'diff' => 2],
                ['text' => "Est-ce qu'il y a quelque chose que tu observes dans les autres couples et que tu veux absolument éviter ?", 'diff' => 2],
                ['text' => "Y a-t-il une chose sur laquelle tu n'es pas d'accord avec moi et dont tu n'as jamais vraiment parlé ?", 'diff' => 3],
                ['text' => "Quelle est la limite morale que tu ne franchirais jamais, même sous pression ?", 'diff' => 3],
                ['text' => "Est-ce que tu penses qu'on partage les mêmes valeurs fondamentales — et là où on diverge, ça t'inquiète ?", 'diff' => 3],
                ['text' => "Y a-t-il une croyance héritée de ta famille dont tu essaies encore de te défaire ?", 'diff' => 3],
                ['text' => "Y a-t-il une valeur que tu penses ne pas partager complètement avec moi — et est-ce que ça te pèse ?", 'diff' => 3],
                ['text' => "Quelle est la chose pour laquelle tu ne te pardonnerais pas si tu la faisais dans notre relation ?", 'diff' => 3],
                ['text' => "Est-ce qu'il y a un compromis que tu as fait sur tes valeurs pour notre relation et que tu regrettes ?", 'diff' => 3],
                ['text' => "Y a-t-il une valeur que tu as du mal à défendre face à moi ou à ta famille ?", 'diff' => 3],
                ['text' => "Quelle est la chose qui te semble injuste dans notre façon de fonctionner ensemble ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose sur lequel tu ne cèderas jamais — et est-ce que tu me l'as clairement dit ?", 'diff' => 2],
                ['text' => "Est-ce que tu as l'impression d'agir en accord avec tes valeurs au quotidien ?", 'diff' => 2],
                ['text' => "Quelle valeur te définit le plus aujourd'hui que tu n'aurais pas citée il y a cinq ans ?", 'diff' => 2],
                ['text' => "Y a-t-il quelque chose qu'on valorise tous les deux mais qu'on ne met pas assez en pratique ?", 'diff' => 2],
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
