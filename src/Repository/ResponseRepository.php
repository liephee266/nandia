<?php

namespace App\Repository;

use App\Entity\Response;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Response>
 */
class ResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Response::class);
    }

    /**
     * Retourne les entrées de journal pour un utilisateur :
     * question, réponse éventuelle, date, thème.
     */
    public function findJournalForUser(int $userId): array
    {
        $results = $this->createQueryBuilder('r')
            ->leftJoin('r.sessionCard', 'sc')
            ->leftJoin('sc.card', 'c')
            ->leftJoin('c.theme', 't')
            ->join('r.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(function (Response $r) {
            $card = $r->getSessionCard()?->getCard();
            return [
                'id'           => $r->getId(),
                'questionText' => $card?->getQuestionText(),
                'answerText'   => $r->getAnswerText(),
                'themeName'    => $card?->getTheme()?->getName(),
                'themeColor'   => $card?->getTheme()?->getColorCode(),
                'createdAt'    => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }, $results);
    }
}
