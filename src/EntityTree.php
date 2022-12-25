<?php

namespace App;

use App\Entity\Base;
use App\Entity\Content;
use App\Entity\Block;
use App\Entity\Section;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

class EntityTree {

    /* #region constructor */

    private $treeSession;
    private $entityTree;

    private $allEntities;
    private $allChildren;

    private $allSections;
    private $allBlocks;
    private $allContents;

    private $excludeIds;

    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->initEntities();
        $this->set($this->createEntityTree());
        $this->excludeIds = array();
    }

    /* #endregion */

    /* #region - init Entities */

    private function initEntities() : void
    {
        $this->allEntities = $this->doctrine->getRepository(Base::class)->findAll();

        $this->allSections = array_filter($this->allEntities, function($e) {
            return $e::class == Section::class;
        });

        usort($this->allSections, fn($a, $b) => $a->getPosition() <=> $b->getPosition());

        $this->allBlocks = array_filter($this->allEntities, function($e) {
            return $e::class == Block::class || is_subclass_of($e::class, Block::class);
        });

        $this->allContents = array_filter($this->allEntities, function($e) {
            return $e::class == Content::class || is_subclass_of($e::class, Content::class);
        });

        $this->allChildren = array_merge($this->allBlocks, $this->allContents);
    }

    /* #endregion */

    /* #region - get/set */

    public function get()
    {
        return $this->entityTree;
    }

    private function set($aEntityTree)
    {
        $this->entityTree = $aEntityTree;
    }

    public function getEntity(array $aSearchParams, $aIndex = 0)
    {
        $entities = $this->getEntities($aSearchParams);
        return count($entities) > 0 ? array_values($entities)[$aIndex] : null;
    }

    public function getEntities(array $aSearchParams) : array
    {
        $result = array();

        if (count($aSearchParams) > 0)
        {
            $result = array_filter($this->allEntities, function($entity) use($aSearchParams) {

                foreach ($aSearchParams as $paramKey => $paramValue)
                {
                    if ($paramKey == 'id' && $entity->getId() != $paramValue)
                    {
                        return false;
                    }
                    if ($paramKey == 'title' && $entity->getTitle() != $paramValue)
                    {
                        return false;
                    }
                    if ($paramKey == 'parentId' && $entity::class == Section::class)
                    {
                        return false;
                    }
                    if ($paramKey == 'parentId' && $entity->getParentId() != $paramValue)
                    {
                        return false;
                    }
                    if ($paramKey == 'type' && $entity::class != $paramValue)
                    {
                        return false;
                    }
                }

                return true;
            });
        }

        return array_values($result);
    }

    /* #endregion */

    /* #region - create EntityTree */

    private function createEntityTree(): array
    {
        $entityTree = array();

        foreach ($this->allSections as $section)
        {
            $entityArray = $this->getEntityArray($section, Section::class);
            $entityArray['children'] = $this->getChildren($section, $entityArray['hasSubs']);
            $entityArray['type'] = $section::class;

            array_push($entityTree, $entityArray);
        }

        return $entityTree;
    }

    private function getEntityArray($aEntity, $aType) : array
    {
        $hasSubs = false;
        $isSub = false;

        $entityArray = [
            'active' => $aEntity->isActive(),
            'position' => $aEntity->getPosition(),
            'id' => $aEntity->getId(),
            'title' => $aEntity->getTitle(),
            'type' => $aType
        ];

        if ($aType == Content::class)
        {
            $entityArray['label'] = $aEntity->getLabel();
        }

        if ($aType != Section::class)
        {
            $content = $aEntity->getContent();
            $entityArray['content'] = $content;
            $entityArray['parentId'] = $aEntity->getParentId();

            $hasSubs = $this->hasSubs($content);
        }
        else
        {
            $entityArray['parentId'] = Constants::SECTION_NO_PARENT;
        }

        $entityArray['hasSubs'] = $hasSubs;
        $entityArray['isSub'] = $isSub;

        $entityArray['children'] = [];

        return $entityArray;
    }

    private function hasSubs($aStrContent) : bool
    {
        $objContent = json_decode($aStrContent);

        if ($objContent != null)
        {
            if (isset($objContent->hasSubs) && $objContent->hasSubs)
            {
                return true;
            }
        }

        return false;
    }

    private function getChildren($aRoot, $aIsSub) : array
    {
        $children = array();
        $rootId = $aRoot->getId();

        $this->addChildrenToArray($children, Block::class, $rootId, $aIsSub);
        $this->addChildrenToArray($children, Content::class, $rootId, $aIsSub);

        usort($children, fn($a, $b) => $a['position'] <=> $b['position']);

        return $children;
    }

    private function addChildrenToArray(&$aChildren, $aTypeOfChild, $aRootId, $aIsSub)
    {
        $entities = $aTypeOfChild == Block::class ? $this->allBlocks : $this->allContents;

        foreach ($entities as $entity)
        {
            if ($entity->getParentId() == $aRootId)
            {
                $childArray = $this->getEntityArray($entity, $entity::class);
                $childArray['isSub'] = $aIsSub;

                $hasSubs = $this->hasSubs($entity->getContent());

                if ($this->hasChildren($entity->getId()))
                {
                    $childArray['children'] = $this->getChildren($entity, $hasSubs);
                }

                array_push($aChildren, $childArray);
            }
        }
    }

    private function hasChildren($aRootId) : bool
    {
        $children = array_filter($this->allChildren, function($entity) use($aRootId) {
            return $entity->getParentId() == $aRootId;
        });

        return count($children) > 0;
    }

    /* #endregion */

    /* #region - find entities in entityTree */

    public static function findEntity($aEntityTree, array $aSearchParams, int $index = 0)
    {
        $children = self::findEntities($aEntityTree, $aSearchParams);

        if (count($children) > 0)
        {
            return array_values($children)[$index];
        }

        return null;
    }

    public static function findEntities($aEntityTree, array $aSearchParams)
    {
        $resultArray = array_filter($aEntityTree, function($val) use ($aSearchParams) {
            foreach ($aSearchParams as $paramKey => $paramValue)
            {
                if ($val[$paramKey] != $paramValue)
                {
                    return false;
                }
            }

            return true;
        });

        foreach ($aEntityTree as $child)
        {
            if (!empty($child['children']))
            {
                $grandChildArray = self::findEntities($child['children'], $aSearchParams);
                foreach ($grandChildArray as $grandChild)
                {
                    array_push($resultArray, $grandChild);
                }
            }
        }

        return $resultArray;
    }

    /* #endregion */

    /* #region - getChoices */

    public function getChoices(int $aEntityId, int $aParentId) : array
    {
        $parentId = $aEntityId > -1 ? $this->getEntity(['id' => $aEntityId])->getParentId() : $aParentId;
        $parent = $this->getEntity(['id' => $parentId]);
        $defaultLabel = $parent->getTitle();
        $defaultValue = $parent->getId();

        $this->setExcludeIds($aEntityId);

        $sections = $this->getFilteredChoices($this->allSections);
        $blocks = $this->getFilteredChoices($this->allBlocks);
        $contents = $this->getFilteredChoices($this->allContents);

        $choices = array(
            $defaultLabel => $defaultValue,
            'sections' => $sections,
            'blocks' => $blocks,
            'contents' => $contents
        );

        return $choices;
    }

    private function setExcludeIds(int $aEntityId)
    {
        if ($aEntityId > -1)
        {
            array_push($this->excludeIds, $aEntityId);

            $children = $this->findEntity($this->entityTree, ['id' => $aEntityId])['children'];

            foreach ($children as $child)
            {
                $this->setExcludeIds($child['id']);
            }
        }
    }

    private function getFilteredChoices(array $aEntities) : array
    {
        $filteredChoices = array();

        $filteredEntities = array_filter($aEntities, function($e) {
            return !in_array($e->getId(), $this->excludeIds);
        });

        foreach ($filteredEntities as $entity)
        {
            $filteredChoices[$entity->getTitle()] = $entity->getId();
        }

        return $filteredChoices;
    }

    /* #endregion */

}

?>