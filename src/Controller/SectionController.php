<?php

namespace App\Controller;

use App\Entity\Section;
use App\Form\SectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

class SectionController extends BaseController
{
    /* #region Create */

    #[Route('/section_create', name: 'section_create')]
    public function create(Request $aRequest)
    {
        $section = $this->createObject($aRequest, Section::class);
        return $this->doObjectToDataBase($section);
    }

    /* #endregion */

    /* #region Update */

    #[Route('/section_update/{aId}', name: 'section_update')]
    public function update(Request $aRequest, $aId)
    {
        $section = $this->updateObject($aRequest, $aId, Section::class);
        return $this->doObjectToDataBase($section);
    }

    /* #endregion */

    /* #region Delete */

    #[Route('/section_delete/{aId}', name: 'section_delete')]
    public function delete(Request $aRequest, ManagerRegistry $doctrine, $aId)
    {
        return $this->deleteObject($aRequest, $aId, Section::class);
    }

    /* #endregion */
}
