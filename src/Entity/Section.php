<?php

namespace App\Entity;

use App\Entity\Base;
use App\Repository\SectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section extends Base
{

}
