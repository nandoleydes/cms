<?php

namespace App\Entity\Contents;

use App\Repository\EducationRepository;
use App\Entity\Block;
use App\Entity\Content;
use App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
class Education extends Content
{
    public Content $subTitle;
    public Content $period;
    public Content $location;
    public Content $result;

    public function getSubTitle(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'subTitle');
    }

    public function setSubTitle(Content $aSubTitle): self
    {
        $this->subTitle = $aSubTitle;

        return $this;
    }

    public function getPeriod(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'period');
    }

    public function setPeriod(Content $aPeriod): self
    {
        $this->period = $aPeriod;

        return $this;
    }

    public function getLocation(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'location');
    }

    public function setLocation(Content $aLocation): self
    {
        $this->location = $aLocation;

        return $this;
    }

    public function getResult(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'result');
    }

    public function setResult(Content $aResult): self
    {
        $this->result = $aResult;

        return $this;
    }
}

?>
