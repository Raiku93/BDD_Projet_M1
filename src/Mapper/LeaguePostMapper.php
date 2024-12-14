<?php

namespace App\Mapper;

use App\DTO\LeaguePostDTO;
use App\Entity\League;

class LeaguePostMapper
{
    public function map(LeaguePostDTO $postDTO): League
    {
        $league = new League();

        $league->setDisplayName($postDTO->nom);
        $league->setCountry($postDTO->pays);
        $league->setDateCreation($postDTO->date);

        return $league;
    }
}