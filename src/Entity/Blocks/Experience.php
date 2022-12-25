<?php

namespace App\Entity\Blocks;

use App\Repository\ExperienceRepository;
use App\Entity\Block;
use App\Entity\Content;
use App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

#[ORM\Entity(repositoryClass: ExperienceRepository::class)]
class Experience extends Block
{
    public Content $info;
    public Content $tasks;

    public function getInfo(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'info');
    }

    public function setInfo(Content $aInfo): self
    {
        $this->info = $aInfo;

        return $this;
    }

    public function getTasks(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'tasks');
    }

    public function setTasks(Content $aTasks): self
    {
        $this->tasks = $aTasks;

        return $this;
    }
}

?>
