<?php

namespace App\Controller;

use App\Constants;
use App\Utils;
use App\EntityTree;
use App\Entity\Base;
use App\Entity\Content;
use App\Entity\Block;
use App\Entity\Section;
use App\Entity\Blocks\ContactInfo;
use App\Entity\Blocks\Experience;
use App\Entity\Contents\Education;
use App\Entity\Contents\Image;
use App\Form\ContentType;
use App\Form\BlockType;
use App\Form\SectionType;
use App\Form\Blocks\ContactInfoType;
use App\Form\Blocks\ExperienceType;
use App\Form\Contents\EducationType;
use App\Form\Contents\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Form;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Criteria;

class BaseController extends AbstractController
{
    /* #region - Constructor */

    private $baseSession;
    private $entityManager;
    private $clsEntityTree;

    public function __construct(private ManagerRegistry $doctrine, private SluggerInterface $slugger)
    {
        $this->entityManager = $this->doctrine->getManager();
        $this->clsEntityTree = new EntityTree($this->doctrine);

        $this->initSession();
    }

    public function initSession()
    {
        $this->baseSession = new Session();
        $this->baseSession->start();

        if (Constants::TESTING)
        {
            $this->baseSession->set('entityTree', $this->clsEntityTree->get());
        }

        if ($this->baseSession->get('entityTree') == null || $this->baseSession->get('resetEntityTree') == true)
        {
            $this->baseSession->set('entityTree', $this->clsEntityTree->get());
            $this->baseSession->set('resetEntityTree', false);
        }
    }

    /* #endregion */

    /* #region - Todo */

    #[Route('/todo', name: 'todo')]
    public function todo(): Response
    {
        return $this->render('base/todo.html.twig', [
            'todos' => [
                'Descriptions: add them to all functions',
                'Error handling',
                'redirectTo -> main object pages are obsolete, go to homepage or form',
                '(front end) - When changing parents -> update position',
                '(front end) - Add child -> choose type of block/content'
            ]
        ]);
    }

    /* #endregion */


    /* #region - Create */

    public function createObject(Request $aRequest, string $aEntityType, int $parentId = -1, bool $hasSub = false) : array
    {
        return $this->objectToDataBase($aRequest, $aEntityType, -1, Constants::CREATE, $parentId, $hasSub);
    }

    /* #endregion */

    /* #region - Read */

    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
            'sections' => $this->baseSession->get('entityTree'),
            'clsContent' => Content::class,
            'clsImage' => Image::class,
            'clsBlock' => Block::class,
            'clsContactInfo' => ContactInfo::class,
            'clsExperience' => Experience::class,
            'clsEducation' => Education::class,
            'clsSection' => Section::class,
            'childColors' => ['blue', 'orange', 'cyan', 'green', 'red', 'dimgrey'],
            'moveButtonColors' => ['primary', 'warning', 'info', 'success', 'danger', 'secondary']
        ]);
    }

    /* #endregion */

    /* #region - Update */

    public function updateObject(Request $aRequest, int $aEntityId, string $aEntityType) : array
    {
        return $this->objectToDataBase($aRequest, $aEntityType, $aEntityId, Constants::UPDATE);
    }

    /* #endregion */

    /* #region - Delete */

    public function deleteObject(Request $aRequest, $aEntityId, $aEntityType)
    {
        $entityArray = $this->findEntity(['id' => $aEntityId]);

        if ($entityArray != null)
        {
            $this->doDelete($entityArray);
        }

        return $this->redirectTo('homepage');
    }

    private function doDelete($aEntityArray)
    {
        $childFail = false;

        $entity = $this->getEntity(['id' => $aEntityArray['id']]);
        $typeName = $this->getTypeName($aEntityArray['type']);

        if ($entity != null)
        {
            $children = $aEntityArray['children'];

            $this->setFlashMessage('delete', $typeName, $entity->getTitle());

            $this->resetPositions($entity, $entity->getPosition(), true);

            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            if (count($children) > 0)
            {
                foreach ($children as $child)
                {
                    if (!$this->doDelete($child) && $childFail == false)
                    {
                        $childFail = true;
                        $this->setFlashMessage('delete', 'child', $child['title'], false);
                    }
                }
            }

            $this->baseSession->set('resetEntityTree', true);

            return true;
        }

        $this->setFlashMessage('delete', $typeName, $entityArray['title'], false);

        return false;
    }

    /* #endregion */


    /* #region - objectToDataBase */

    /**
     *
     * Generic function for creating/updating entities in the database
     *
     * @param   Request     $aRequest       The http request
     * @param   string      $aEntityType    The entity class name
     * @param   int         $aEntityId      The entity id
     * @param   string      $aAction        The type of action: create/update
     * @param   array       $aParentId      The parent id (when creating a new entity)
     *
     * @return  Response    A http response
     *
     */
    private function objectToDataBase(Request $aRequest, string $aEntityType, int $aEntityId, string $aAction, int $aParentId = -1, bool $hasSub = false) : array
    {
        $imageName = null;

        $doUpdate = $aAction == Constants::UPDATE;
        $doCreate = !$doUpdate;

        $entity = $doCreate ? $this->entityFactory($aEntityType) :
                              $this->getEntity(['id' => $aEntityId]);

        $isSection = $entity::class == Section::class;

        $formType = $this->getFormType($aEntityType);
        $typeName = $this->getTypeName($aEntityType);

        if (!$this->isValidParameters($entity, $formType, $typeName))
        {
            // TODO -> cannot redirect to main (for example 'contents') page -> goto form
            return [
                'redirect' => true,
                'target' => $aEntityType
            ];
        }

        if ($aEntityType == Image::class)
        {
            if ($doUpdate)
            {
                $imageName = $entity->getContent();
                $file = new File($this->getParameter('image_directory').'/'.$imageName);
                $entity->setContent($file);
            }
        }

        $oldPosition = $doUpdate ? $entity->getPosition() : -1;

        $form = $isSection ? $this->createForm($formType, $entity) :
                             $this->createForm($formType, $entity, array('choices' => $this->clsEntityTree->getChoices($aEntityId, $aParentId)));

        $form->handleRequest($aRequest);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->resetPositions($entity, $oldPosition);

            if ($aEntityType == Image::class)
            {
                $entity->setContent( $this->setImageContent($form, $doCreate, $imageName) );
            }

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->baseSession->set('resetEntityTree', true);

            $this->setFlashMessage($doCreate ? 'create' : 'update', $typeName, $entity->getTitle());

            $returnArray = [
                'redirect' => true,
                'target' => 'homepage'
            ];

            if ($hasSub)
            {
                $returnArray['parentId'] = $entity->getId();
            }

            return $returnArray;
        }

        $renderUrl = $this->getRenderUrl($typeName, $entity::class, $aAction);
        $renderFormType = $doUpdate ? 'form_update' : 'form_create';

        $max = $this->getMax($entity, $aParentId, $doCreate);

        $entityFromTree = $this->findEntity(['id' => $entity->getId()]);

        $renderArray = array(
            $renderFormType => $form->createView(),
            'max' => $doCreate ? ++$max : $max,
            'isSub' => $entityFromTree != null ? $entityFromTree['isSub'] : false
        );

        return [
            'render' => true,
            'url' => $renderUrl,
            'array' => $renderArray
        ];
    }

    /**
     *
     * Get the max position of an entity within a parents scope
     *
     * @param   Base    $aEntity        The entity
     * @param   array   $aParentId      The parent id (when creating a new entity)
     * @param   bool    $aDoCreate      Creating new entity?
     *
     * @return  int     The max position
     *
     */
    private function getMax(Base $aEntity, int $aParentId, bool $aDoCreate) : int
    {
        $max = 0;

        if ($aEntity::class == Section::class)
        {
            $max = count($this->findEntities(['type' => Section::class]));
        }
        else
        {
            $parentId = $aDoCreate ? $aParentId : $aEntity->getParentId();
            $max = count($this->findEntities(['parentId' => $parentId]));
        }

        return $max;
    }

    /**
     *
     * Check if Entity and FormType are valid
     *
     * @param   Base    $aEntity    The entity
     * @param   string  $aFormType  The form type
     * @param   string  $aTypeName  The type name, for feedback message
     *
     * @return  bool    Parameters are valid?
     *
     */
    private function isValidParameters(Base $aEntity, string $aFormType, string $aTypeName) : bool
    {
        $isValid = true;

        if ($aEntity == null)
        {
            $this->setFlashMessage('entity', $aTypeName, aSuccess:false);
            $isValid = false;
        }

        if ($aFormType == null)
        {
            $this->setFlashMessage('form', $aTypeName, aSuccess:false);
            $isValid = false;
        }

        return $isValid;
    }

    /**
     *
     * Set content field for ImageType
     *
     * @param   Form    $aForm          The form
     * @param   bool    $aDoCreate      Creating new Image?
     * @param   string  $aImageName     The name of the previously persisted image
     *
     * @return  string  The image file name
     *
     */
    private function setImageContent(Form $aForm, bool $aDoCreate, string $aImageName = null) : string
    {
        $newFilename = null;
        $imageFile = $aForm->get('content')->getData();

        if ($imageFile)
        {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('image_directory'),
                    $newFilename
                );
            }
            catch (FileException $e) {
                $this->setFlashMessage($aDoCreate ? 'create' : 'update', 'image', '');
            }
        }

        return $newFilename ?? $aImageName;
    }

    /**
     *
     * Returns the result of the main objectToDataBase function
     *
     * @param   array       $aEntity        The entity array
     * @param   string      $aParentType    The parent type, for creating subs
     * @param   int         $aParentId      The parent id, for creating subs
     *
     * @return  Response    The result of the main objectToDataBase function
     *
     */
    protected function doObjectToDataBase(array $aEntity, string $aParentType = null, int $aParentId = -1) : Response
    {
        $doRender = array_key_exists('render', $aEntity);
        if ($doRender)
        {
            return $this->render($aEntity['url'], $aEntity['array']);
        }

        if ($aParentId > -1 && $aParentType != null)
        {
            $this->createSubs($aParentId, $this->getSubProperties($aParentType));
        }

        $doRedirect = array_key_exists('redirect', $aEntity);
        if ($doRedirect)
        {
            return $this->redirectTo($aEntity['target']);
        }

        return $this->redirectTo('homepage');
    }

    /* #endregion */

    /* #region - createSubs */

    protected function createSubs(int $aParentId = -1, array $aSubs = array()) : void
    {
        if ($aParentId > -1 && count($aSubs))
        {
            $arrAddSubs = array(
                'hasSubs' => true,
                'subs' => []
            );

            for ($i = 0; $i < count($aSubs); $i++)
            {
                $entity = $this->entityFactory($aSubs[$i]['type']);

                $entity->setPosition($i + 1);
                $entity->setLabel($aSubs[$i]['label']);
                $entity->setTitle('');
                $entity->setParentId($aParentId);

                $this->entityManager->persist($entity);
                $this->entityManager->flush();

                array_push($arrAddSubs['subs'], [
                    'id' => $entity->getId(),
                    'label' => $aSubs[$i]['label']
                ]);
            }

            $this->addSubsToParent($aParentId, $arrAddSubs);
        }
    }

    private function addSubsToParent($aParentId, $aArrAddSubs)
    {
        $parent = $this->doctrine->getRepository(Base::class)->find($aParentId);

        if ($parent != null && count($aArrAddSubs['subs']) > 0)
        {
            $parent->setContent(json_encode($aArrAddSubs));
        }

        $this->entityManager->persist($parent);
        $this->entityManager->flush();
    }

    protected function getSubProperties(string $aEntityType) : array
    {
        $properties = array();
        $labels = array_keys(get_class_vars($aEntityType));

        foreach($labels as $label)
        {
            $rp = new \ReflectionProperty($aEntityType, $label);

            array_push($properties, [
                'type' => $rp->getType()->getName(),
                'label' => $label
            ]);
        }

        return $properties;
    }

    /* #endregion */

    /* #region - Find */

    /**
     *
     * Get single Entity from Base repository
     *
     * @param   array   $aSearchParams  The array of search parameters
     * @param   int     $aIndex         The index in the result array, for returning only one value
     *
     * @return  Base    An Entity or null
     *
     */
    protected function getEntity(array $aSearchParams, int $aIndex = 0)
    {
        return $this->clsEntityTree->getEntity($aSearchParams, $aIndex);
    }

    /**
     *
     * Get array of Entities from Base repository
     *
     * @param   array   $aSearchParams  The array of search parameters
     *
     * @return  array   An array of Entities
     *
     */
    protected function getEntities(array $aSearchParams)
    {
        return $this->clsEntityTree->getEntities($aSearchParams);
    }

    /**
     *
     * Find a single Entity array in the EntityTree
     *
     * @param   array   $aSearchParams  The array of search parameters
     * @param   int     $aIndex         The index in the result array, for returning only one value
     *
     * @return  array   An Entity array or null
     *
     */
    protected function findEntity(array $aSearchParams, int $aIndex = 0)
    {
        return EntityTree::findEntity($this->baseSession->get('entityTree'), $aSearchParams, $aIndex);
    }

    /**
     *
     * Get array of Entities arrays in the EntityTree
     *
     * @param   array   $aSearchParams  The array of search parameters
     *
     * @return  array   An array of Entity arrays
     *
     */
    protected function findEntities(array $aSearchParams) : array
    {
        return EntityTree::findEntities($this->baseSession->get('entityTree'), $aSearchParams);
    }

    /* #endregion */

    /* #region - Move */

    #[Route('/move/{aId}/{aDirection}', name: 'move')]
    public function move($aId, $aDirection) : Response
    {
        $entity = $this->getEntity(['id' => $aId]);
        $oldPosition = $entity->getPosition();
        $newPosition = $aDirection == Constants::UP ? $oldPosition - 1 : $oldPosition + 1;

        $entity->setPosition($newPosition);

        $this->resetPositions($entity, $oldPosition);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->baseSession->set('resetEntityTree', true);

        return $this->redirectToRoute('app_main');
    }

    /* #endregion */

    /* #region - resetPositions */

    /**
     *
     * Resets all sections position, when:
     * - a new section was inserted into the list
     * - an existing section was moved to another position in the list
     * - an existing section was removed from the list
     *
     * @param   int $aEntity        The entity that moved to a new position
     * @param   int $aOldPosition   The old position of the entity
     * @param   int $aDoDelete      The entity has been removed
     *
     */
    private function resetPositions(Base $aEntity, int $aOldPosition, bool $aDoDelete = false) : void
    {
        $newPosition = $aEntity->getPosition();

        $action = $aOldPosition == -1           ? Constants::INSERT_NEW_ELEMENT :
                ( $newPosition > $aOldPosition  ? Constants::MOVE_ELEMENTS_UP :
                ( $newPosition < $aOldPosition  ? Constants::MOVE_ELEMENTS_DOWN :
                ( $aDoDelete                    ? Constants::DELETE_ELEMENT : null ) ) );

        if ($action != null)
        {
            $isSection = $aEntity::class == Section::class;

            $sibling = null;
            $siblingsArray = $isSection ? $this->getEntities(['type' => Section::class]) :
                                          $this->getEntities(['parentId' => $aEntity->getParentId()]);

            foreach ($siblingsArray as $siblingEntity)
            {
                $siblingPosition = $siblingEntity->getPosition();

                switch ($action)
                {
                    case Constants::INSERT_NEW_ELEMENT:
                    {
                        if ($siblingPosition >= $newPosition)
                        {
                            $siblingEntity->setPosition($siblingPosition + 1);
                        }
                        break;
                    }
                    case Constants::DELETE_ELEMENT:
                    {
                        if ($siblingPosition >= $newPosition)
                        {
                            $siblingEntity->setPosition($siblingPosition - 1);
                        }
                        break;
                    }
                    case Constants::MOVE_ELEMENTS_UP:
                    {
                        if ($siblingPosition > $aOldPosition && $siblingPosition <= $newPosition && $siblingEntity !== $aEntity)
                        {
                            $siblingEntity->setPosition($siblingPosition - 1);
                        }
                        break;
                    }
                    case Constants::MOVE_ELEMENTS_DOWN:
                    {
                        if ($siblingPosition < $aOldPosition && $siblingPosition >= $newPosition && $siblingEntity !== $aEntity)
                        {
                            $siblingEntity->setPosition($siblingPosition + 1);
                        }
                        break;
                    }
                }
            }

            if ($sibling != null)
            {
                $this->entityManager->persist($sibling);
                $this->entityManager->flush();
            }
        }
    }

    /* #endregion */

    /* #region - aEntityType */

    protected function redirectTo($aEntityType = null)
    {
        switch ($aEntityType)
        {
            case Section::class:
            {
                return $this->redirectToRoute('sections');
            }
            case Block::class:
            {
                return $this->redirectToRoute('blocks');
            }
            case Content::class:
            {
                return $this->redirectToRoute('contents');
            }
            default:
            {
                return $this->redirectToRoute('app_main');
            }
        }
    }

    private function getRenderUrl($aTypeName, $aEntityClass, $aAction): string
    {
        $folder = is_subclass_of($aEntityClass, Block::class) ? "/blocks/$aTypeName" :
                ( is_subclass_of($aEntityClass, Content::class) ? "/contents/$aTypeName" : $aTypeName );

        return $aTypeName != null ? $folder . "/$aAction.html.twig" : 'main/index.html.twig';
    }

    private function getFormType($aEntityType)
    {
        switch ($aEntityType)
        {
            case Section::class:
            {
                return SectionType::class;
            }
            case Block::class:
            {
                return BlockType::class;
            }
            case Content::class:
            {
                return ContentType::class;
            }
            case ContactInfo::class:
            {
                return ContactInfoType::class;
            }
            case Experience::class:
            {
                return ExperienceType::class;
            }
            case Education::class:
            {
                return EducationType::class;
            }
            case Image::class:
            {
                return ImageType::class;
            }
            default:
            {
                return null;
            }
        }
    }

    private function getTypeName($aEntityType)
    {
        $entityNameArray = explode('\\', $aEntityType);
        return strtolower($entityNameArray[count($entityNameArray) - 1]);
    }

    private function entityFactory($aEntityType)
    {
        switch ($aEntityType)
        {
            case Section::class:
            {
                return new Section();
            }
            case Block::class:
            {
                return new Block();
            }
            case Content::class:
            {
                return new Content();
            }
            case ContactInfo::class:
            {
                return new ContactInfo();
            }
            case Experience::class:
            {
                return new Experience();
            }
            case Education::class:
            {
                return new Education();
            }
            case Image::class:
            {
                return new Image();
            }
            default:
            {
                return null;
            }
        }
    }

    /* #endregion */

    /* #region - setFlashMessage */

    private function setFlashMessage(string $aAction, string $aSubject, ?string $aTitle , bool $aSuccess = true)
    {
        $form = $aSuccess ? Constants::FORM_FEEDBACK_SUCCESS : Constants::FORM_FEEDBACK_FAIL;
        $message = Constants::get($aAction, $aSubject, $aSuccess) . (!empty($aTitle) ? ' - ' . $aTitle : '');

        $this->addFlash($form, $message);
    }

    /* #endregion */
}
