<?php

namespace App\Entity\Blocks;

use App\Repository\ContactInfoRepository;
use App\Entity\Block;
use App\Entity\Content;
use App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

#[ORM\Entity(repositoryClass: ContactInfoRepository::class)]
class ContactInfo extends Block
{
    public Content $name;
    public Content $address;
    public Content $postalCity;
    public Content $birthDate;
    public Content $email;
    public Content $phone;

    public function getName(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'name');
    }

    public function setName(Content $aName): self
    {
        $this->name = $aName;

        return $this;
    }

    public function getAddress(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'address');
    }

    public function setAddress(Content $aAddress): self
    {
        $this->address = $aAddress;

        return $this;
    }

    public function getPostalCity(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'postalCity');
    }

    public function setPostalCity(Content $aPostalCity): self
    {
        $this->postalCity = $aPostalCity;

        return $this;
    }

    public function getBirthDate(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'birthDate');
    }

    public function setBirthDate(Content $aBirthDate): self
    {
        $this->birthDate = $aBirthDate;

        return $this;
    }

    public function getEmail(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'email');
    }

    public function setEmail(Content $aEmail): self
    {
        $this->email = $aEmail;

        return $this;
    }

    public function getPhone(ManagerRegistry $aDoctrine, bool $aGetContent = false) : string|Content
    {
        return $this->get($aDoctrine, $aGetContent, 'phone');
    }

    public function setPhone(Content $aPhone): self
    {
        $this->phone = $aPhone;

        return $this;
    }
}

?>
