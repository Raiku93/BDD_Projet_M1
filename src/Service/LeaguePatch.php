<?php

namespace App\Service;

use App\DTO\LeaguePatchDTO;
use App\Entity\League;

class LeaguePatch
{
    public function patch(League $league, LeaguePatchDTO $DTO): bool
    {
        $updated = false;

        if($DTO->nom && $DTO->nom != $league->getDisplayName()){
            $league->setDisplayName($DTO->nom);
            $updated = true;
        }

        if($DTO->pays && $DTO->pays != $league->getCountry()){
            $league->setCountry($DTO->pays);
            $updated = true;
        }

        if($DTO->date && $DTO->date != $league->getDateCreation())
        {
            $league->setDateCreation($DTO->date);
            $updated = true;
        }

        return $updated;
    }
}