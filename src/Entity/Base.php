<?php

namespace App\Entity;

use App\Entity\Blocks\ContactInfo;
use App\Entity\Blocks\Experience;
use App\Entity\Contents\Education;
use App\Entity\Contents\Image;
use App\Repository\BaseRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

#[ORM\Entity(repositoryClass: BaseRepository::class)]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn("type", "string")]
#[ORM\DiscriminatorMap([
    'section' => Section::class,
    'block' => Block::class,
    'content' => Content::class,
    'contactinfo' => ContactInfo::class,
    'experience' => Experience::class,
    'education' => Education::class,
    'image' => Image::class
])]
class Base
{
    protected $subArray;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column]
    protected ?int $position = null;

    #[ORM\Column]
    protected bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    protected function get(ManagerRegistry $aDoctrine, bool $aGetContent, string $aLabel)
    {
        if (!isset($this->subArray))
        {
            $this->subArray = json_decode($this->getContent());
        }

        $sub = array_values( array_filter($this->subArray->subs, function($e) use($aLabel)
        {
            return $e->label == $aLabel;
        }));

        $setFunction = 'set' . ucfirst($aLabel);

        $this->$setFunction($aDoctrine->getRepository(Base::class)->find($sub[0]->id));

        return $aGetContent ? $this->$aLabel->getContent() : $this->$aLabel;
    }
}
