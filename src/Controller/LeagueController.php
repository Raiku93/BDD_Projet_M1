<?php

namespace App\Controller;

use App\DTO\LeaguePatchDTO;
use App\DTO\LeaguePostDTO;
use App\Entity\League;
use App\Mapper\LeaguePostMapper;
use App\Service\LeaguePatch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

class LeagueController extends AbstractController
{


    public function __construct(
        private LeaguePostMapper $mapper,
        private EntityManagerInterface $entityManager,
        private LeaguePatch $patcher,
    )
    {}
    public function getLeague(
        League $league,
    ): JsonResponse
    {
        return $this->json($league,200,[],['groups' => ['league:read']]);
    }

    public function postLeague(
        #[MapRequestPayload]
        LeaguePostDTO $leaguePostDTO
    ): JsonResponse
    {
        $league = $this->mapper->map($leaguePostDTO);

        $this->entityManager->persist($league);
        $this->entityManager->flush();

        return $this->json($league,201,[],['groups' => ['league:read']]);

    }

    public function deleteLeague(League $league): JsonResponse
    {
        $this->entityManager->remove($league);
        $this->entityManager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    public function patchLeague(
        League $league,
        #[MapRequestPayload]
        LeaguePatchDTO $leaguePatchDTO): JsonResponse
    {
        if($this->patcher->patch($league, $leaguePatchDTO)){
            $this->entityManager->persist($league);
            $this->entityManager->flush();
            return $this->json($league,201,[],['groups' => ['league:read']]);
        }else{
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }
    }
}
