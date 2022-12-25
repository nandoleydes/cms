<?php

namespace App\Entity\Contents;

use App\Entity\Content;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image extends Content
{
    public function getImageFile()
    {
        $fullUrl = $this->getContent();
        $arrUrl = explode('/', $fullUrl);

        return $arrUrl[count($arrUrl) - 1];
    }
}
