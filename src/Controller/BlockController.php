<?php

namespace App\Controller;

use App\Utils;
use App\Entity\Block;
use App\Entity\Content;
use App\Entity\Blocks\ContactInfo;
use App\Entity\Blocks\Experience;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class BlockController extends BaseController
{
    /* #region Create */

    #[Route('/block_create/{aParentId}', name: 'block_create')]
    public function create(Request $aRequest, $aParentId)
    {
        $block = $this->createObject($aRequest, Block::class, $aParentId);
        return $this->doObjectToDataBase($block);
    }

    #[Route('/contactinfo_create/{aParentId}', name: 'contactinfo_create')]
    public function createContactInfo(Request $aRequest, $aParentId)
    {
        $contactInfo = $this->createObject($aRequest, ContactInfo::class, $aParentId, true);
        $subParentId = array_key_exists('parentId', $contactInfo) ? $contactInfo['parentId'] : -1;
        return $this->doObjectToDataBase($contactInfo, ContactInfo::class, $subParentId);
    }

    #[Route('/experience_create/{aParentId}', name: 'experience_create')]
    public function createExperience(Request $aRequest, $aParentId)
    {
        $experience = $this->createObject($aRequest, Experience::class, $aParentId, true);
        $subParentId = array_key_exists('parentId', $experience) ? $experience['parentId'] : -1;
        return $this->doObjectToDataBase($experience, Experience::class, $subParentId);
    }

    /* #endregion */

    /* #region Update */

    #[Route('/block_update/{aId}', name: 'block_update')]
    public function update(Request $aRequest, $aId)
    {
        $entity = $this->getEntity(['id' => $aId]);
        $block = $this->updateObject($aRequest, $aId, $entity::class);
        return $this->doObjectToDataBase($block);
    }

    /* #endregion */

    /* #region Delete */

    #[Route('/block_delete/{aId}', name: 'block_delete')]
    public function delete(Request $aRequest, $aId)
    {
        return $this->deleteObject($aRequest, $aId, Block::class);
    }

    /* #endregion */
}
