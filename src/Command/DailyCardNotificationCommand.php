<?php
// src/Command/DailyCardNotificationCommand.php

namespace App\Command;

use App\Repository\CardRepository;
use App\Repository\UsersRepository;
use App\Service\PushNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande Symfony à planifier via cron (ex: chaque jour à 9h) :
 *
 *   0 9 * * * php /var/www/html/bin/console app:daily-card-notification
 *
 * Envoie une carte aléatoire en notification push à tous les utilisateurs
 * enregistrés dans OneSignal (External User ID = user.id).
 */
#[AsCommand(
    name: 'app:daily-card-notification',
    description: 'Envoie la notification quotidienne avec une carte aléatoire.',
)]
class DailyCardNotificationCommand extends Command
{
    public function __construct(
        private readonly CardRepository          $cardRepo,
        private readonly UsersRepository         $usersRepo,
        private readonly PushNotificationService $push,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Tirer une carte aléatoire (tous thèmes confondus)
        $card = $this->cardRepo->findRandomCard();
        if (!$card) {
            $output->writeln('<error>Aucune carte disponible.</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            '<info>Carte sélectionnée #%d :</info> %s',
            $card->getId(),
            mb_substr($card->getQuestionText(), 0, 80)
        ));

        // Récupérer tous les utilisateurs (OneSignal gère les appareils non enregistrés)
        $users = $this->usersRepo->findAll();
        if (empty($users)) {
            $output->writeln('<comment>Aucun utilisateur en base.</comment>');
            return Command::SUCCESS;
        }

        $this->push->sendToUsers(
            $users,
            '✨ Question du jour',
            $card->getQuestionText(),
            [
                'route'  => '/play_page',
                'type'   => 'daily_card',
                'cardId' => (string) $card->getId(),
            ],
        );

        $output->writeln(sprintf(
            '<info>Notification envoyée à %d utilisateurs.</info>',
            count($users)
        ));

        return Command::SUCCESS;
    }
}
