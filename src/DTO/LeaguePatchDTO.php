<?php

namespace App\DTO;



class LeaguePatchDTO
{
    public ?string $nom;
    public ?string $pays = null;

    public ?\DateTime $date;
}