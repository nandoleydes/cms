<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\Contents\Image;
use App\Entity\Contents\Education;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class ContentController extends BaseController
{
    /* #region Create */

    #[Route('/content_create/{aParentId}', name: 'content_create')]
    public function create(Request $aRequest, $aParentId)
    {
        $content = $this->createObject($aRequest, Content::class, $aParentId);
        return $this->doObjectToDataBase($content);
    }

    #[Route('/image_create/{aParentId}', name: 'image_create')]
    public function createImage(Request $aRequest, $aParentId)
    {
        $content = $this->createObject($aRequest, Image::class, $aParentId);
        return $this->doObjectToDataBase($content);
    }

    #[Route('/education_create/{aParentId}', name: 'education_create')]
    public function createEducation(Request $aRequest, $aParentId)
    {
        $education = $this->createObject($aRequest, Education::class, $aParentId, true);
        $subParentId = array_key_exists('parentId', $education) ? $education['parentId'] : -1;
        return $this->doObjectToDataBase($education, Education::class, $subParentId);
    }

    /* #endregion */

    /* #region Update */

    #[Route('/content_update/{aId}', name: 'content_update')]
    public function update(Request $aRequest, $aId)
    {
        $entity = $this->getEntity(['id' => $aId]);
        $content = $this->updateObject($aRequest, $aId, $entity::class);
        return $this->doObjectToDataBase($content);
    }

    /* #endregion */

    /* #region Delete */

    #[Route('/content_delete/{aId}', name: 'content_delete')]
    public function delete(Request $aRequest, $aId)
    {
        return $this->deleteObject($aRequest, $aId, Content::class);
    }

    /* #endregion */

}
