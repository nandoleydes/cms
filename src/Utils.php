<?php

namespace App;

use Symfony\Component\HttpFoundation\Response;

class Utils
{
    public static function getJsonResponse($aToJson): Response
    {
        return new Response(
            json_encode ($aToJson),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     *
     * Get content from textarea and create json string
     *
     * @param   string  $aSubject   The main key
     * @param   string  $aContent   The content from the textarea
     * @param   ?array  $aTitles    The keys for sub elements, optional
     *
     * @return  string  The json string
     *
     */
    public static function getJsonFromContentField(string $aSubject, string $aContent, ?array $aTitles = null) : string
    {
        $jsonFromContent = array($aSubject => []);

        $elements = explode(PHP_EOL.PHP_EOL, $aContent);

        foreach ($elements as $element)
        {
            $elementArray = explode(PHP_EOL, $element);

            if (!empty($aTitles))
            {
                $elementArrayWithTitles = array();

                for ($i = 0; $i < count($aTitles); $i++)
                {
                    $value = array_key_exists($i, $elementArray) ? $elementArray[$i] : null;

                    $elementArrayWithTitles[$aTitles[$i]] = $value;
                }

                $elementArray = $elementArrayWithTitles;
            }

            if (count($elements) > 1)
            {
                array_push($jsonFromContent[$aSubject], $elementArray);
            }
            else
            {
                $jsonFromContent[$aSubject] = $elementArray;
            }
        }

        return json_encode($jsonFromContent);
    }
}
