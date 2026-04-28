<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\Theme;
use App\Entity\WeeklyChallenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Génère automatiquement le défi de la semaine prochaine.
 *
 * À planifier chaque vendredi :
 *   0 10 * * 5 php bin/console app:generate-weekly-challenge
 */
#[AsCommand(
    name: 'app:generate-weekly-challenge',
    description: 'Génère le défi hebdomadaire pour la semaine prochaine.',
)]
class GenerateWeeklyChallengeCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Calculer les dates de la semaine prochaine (lundi à dimanche)
        $nextMonday = new \DateTimeImmutable('next monday');
        $nextSunday = $nextMonday->modify('+6 days')->modify('23:59:59');

        $weekLabel = $nextMonday->format('Y-\WW');

        // Vérifier si le défi existe déjà
        $existing = $this->em->getRepository(\App\Entity\WeeklyChallenge::class)
            ->findOneBy(['weekLabel' => $weekLabel]);

        if ($existing) {
            $io->warning("Le défi $weekLabel existe déjà.");
            return Command::SUCCESS;
        }

        // Sélectionner un thème (rotation basée sur le numéro de semaine)
        $themes = $this->em->getRepository(Theme::class)->findAll();
        if (empty($themes)) {
            $io->error('Aucun thème disponible.');
            return Command::FAILURE;
        }

        $weekNumber = (int) $nextMonday->format('W');
        $theme = $themes[$weekNumber % count($themes)];

        // Créer le défi
        $challenge = new WeeklyChallenge();
        $challenge->setWeekLabel($weekLabel);
        $challenge->setStartDate($nextMonday);
        $challenge->setEndDate($nextSunday);
        $challenge->setTheme($theme);
        $challenge->setCardTarget(5);
        $challenge->setXpBonus(50);

        // Sélectionner 5 cartes aléatoires du thème
        $cards = $this->em->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->andWhere('c.theme = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('RANDOM()')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        if (count($cards) < 5) {
            $io->warning("Moins de 5 cartes disponibles pour le thème {$theme->getName()}.");
        }

        foreach ($cards as $card) {
            $challenge->addCard($card);
        }

        $this->em->persist($challenge);
        $this->em->flush();

        $io->success([
            "Défi généré : $weekLabel",
            "Thème : {$theme->getName()}",
            "Cartes : " . count($cards),
            "XP Bonus : 50",
            "Période : {$nextMonday->format('d/m')} → {$nextSunday->format('d/m')}",
        ]);

        return Command::SUCCESS;
    }
}
