<?php

namespace App;

class Constants
{
    /* #region public constants */

    public const TESTING = false;

    public const CREATE = "create";
    public const UPDATE = "update";

    public const UP     = "up";
    public const DOWN   = "down";

    public const INSERT_NEW_ELEMENT = "insertNewElement";
    public const DELETE_ELEMENT     = "deleteElement";
    public const MOVE_ELEMENTS_UP   = "moveElementsUp";
    public const MOVE_ELEMENTS_DOWN = "moveElementsDown";

    public const DEFAULT_CHOICE_LABEL = 'Zonder parent';

    public const PAGE_MAIN = 'Homepage';

    public const SECTION_NO_PARENT = "Sections can't have a parent";

    public const FORM_FEEDBACK_SUCCESS   = "form_success";
    public const FORM_FEEDBACK_FAIL   = "form_fail";

    /* #endregion */

    /* #region GET constants */

    private const CREATE_SECTION_SUCCESS    = "Section has been created";
    private const CREATE_SECTION_FAIL       = "Could not create section";
    private const UPDATE_SECTION_SUCCESS    = "Section has been updated";
    private const UPDATE_SECTION_FAIL       = "Could not update section";
    private const DELETE_SECTION_SUCCESS    = "Section was deleted from the database";
    private const DELETE_SECTION_FAIL       = "Could not delete section from the database";

    private const CREATE_BLOCK_SUCCESS      = "Block has been created";
    private const CREATE_BLOCK_FAIL         = "Could not create block";
    private const UPDATE_BLOCK_SUCCESS      = "Block has been updated";
    private const UPDATE_BLOCK_FAIL         = "Could not update block";
    private const DELETE_BLOCK_SUCCESS      = "Block was deleted from the database";
    private const DELETE_BLOCK_FAIL         = "Could not delete block from the database";

    private const CREATE_CONTENT_SUCCESS    = "Content has been created";
    private const CREATE_CONTENT_FAIL       = "Could not create content";
    private const UPDATE_CONTENT_SUCCESS    = "Content has been updated";
    private const UPDATE_CONTENT_FAIL       = "Could not update content";
    private const DELETE_CONTENT_SUCCESS    = "Content was deleted from the database";
    private const DELETE_CONTENT_FAIL       = "Could not delete content from the database";

    private const CREATE_CONTACTINFO_SUCCESS    = "ContactInfo has been created";
    private const CREATE_CONTACTINFO_FAIL       = "Could not create ContactInfo";
    private const UPDATE_CONTACTINFO_SUCCESS    = "ContactInfo has been updated";
    private const UPDATE_CONTACTINFO_FAIL       = "Could not update ContactInfo";
    private const DELETE_CONTACTINFO_SUCCESS    = "ContactInfo was deleted from the database";
    private const DELETE_CONTACTINFO_FAIL       = "Could not delete ContactInfo from the database";

    private const CREATE_EXPERIENCE_SUCCESS    = "Experience has been created";
    private const CREATE_EXPERIENCE_FAIL       = "Could not create Experience";
    private const UPDATE_EXPERIENCE_SUCCESS    = "Experience has been updated";
    private const UPDATE_EXPERIENCE_FAIL       = "Could not update Experience";
    private const DELETE_EXPERIENCE_SUCCESS    = "Experience was deleted from the database";
    private const DELETE_EXPERIENCE_FAIL       = "Could not delete Experience from the database";

    private const CREATE_EDUCATION_SUCCESS    = "Education has been created";
    private const CREATE_EDUCATION_FAIL       = "Could not create Education";
    private const UPDATE_EDUCATION_SUCCESS    = "Education has been updated";
    private const UPDATE_EDUCATION_FAIL       = "Could not update Education";
    private const DELETE_EDUCATION_SUCCESS    = "Education was deleted from the database";
    private const DELETE_EDUCATION_FAIL       = "Could not delete Education from the database";

    private const CREATE_IMAGE_SUCCESS    = "Image has been created";
    private const CREATE_IMAGE_FAIL       = "Could not create image";
    private const UPDATE_IMAGE_SUCCESS    = "Image has been updated";
    private const UPDATE_IMAGE_FAIL       = "Could not update image";
    private const DELETE_IMAGE_SUCCESS    = "Image was deleted from the database";
    private const DELETE_IMAGE_FAIL       = "Could not delete image from the database";

    private const CREATE_GENERAL_SUCCESS    = "Create success!";
    private const CREATE_GENERAL_FAIL       = "Create fail!";
    private const UPDATE_GENERAL_SUCCESS    = "Update success!";
    private const UPDATE_GENERAL_FAIL       = "Update fail!";
    private const DELETE_GENERAL_SUCCESS    = "Delete success!";
    private const DELETE_GENERAL_FAIL       = "Delete fail!";

    private const DELETE_CHILD_SUCCESS      = "Child was deleted from the database";
    private const DELETE_CHILD_FAIL         = "Could not delete child from the database";

    private const FORM_SECTION_FAIL         = "Section has no formType";
    private const FORM_BLOCK_FAIL           = "Block has no formType";
    private const FORM_CONTENT_FAIL         = "Content has no formType";
    private const FORM_CONTACTINFO_FAIL     = "ContactInfo has no formType";

    private const ENTITY_SECTION_FAIL       = "App\Entity\Section does not exist";
    private const ENTITY_BLOCK_FAIL         = "App\Entity\Block does not exist";
    private const ENTITY_CONTENT_FAIL       = "App\Entity\Content does not exist";
    private const ENTITY_CONTACTINFO_FAIL   = "App\Entity\Blocks\ContactInfo does not exist";

    /* #endregion */

    /* #region GET function */

    public static function get($aAction, $aEntity, $aSuccess = true) : string
    {
        $action = strtoupper($aAction) . '_';
        $entity = self::getEntity($aEntity);
        $success = $aSuccess ? 'SUCCESS' : 'FAIL';

        $constName = $action . $entity . $success;

        return constant("self::$constName");
    }

    private static function getEntity($aEntity) : string
    {
        $entityString = '';

        foreach (explode(' ', $aEntity) as $entity)
        {
            $entityString .= strtoupper($entity) . '_';
        }

        return $entityString;
    }

    /* #endregion */
}

?>