<?php

namespace app;

class Context
{
    const HAS_CHAPTERS =
        [
        "DBG",
        "AUC",
        "InCatilinam",
        "PlinyEpistulae",
    ];

    const IS_POETRY =
        [
        "Aeneid",
        "Catullus",
    ];

    const HAS_IDENTIFIED_SPEAKERS =
        [
        "Aeneid",
        "DBG",
    ];

    const LEVEL_DICT_DB =
        [
        "3" => "~Latin3Dictionary",
        "4" => "^Latin4Dictionary",
        "AP" => "#APDictionary",
    ];

    const LEVEL_NOTES_DB =
        [
        "3" => "~Latin3Notes",
        "4" => "^Latin4Notes",
        "AP" => "#APNotes",
    ];

    const LEVEL_DB = [
        "AP" => "#APHW",
        "4" => "^Latin4HW",
        "3" => "~Latin3HW",
    ];

    const BOOK_DB =
        [
        "Aeneid" => "#APAeneidText",
        "DBG" => "#APDBGText",
        "AUC" => "^Latin4AUCText",
        "InCatilinam" => "^Latin4InCatilinamText",
        "PlinyEpistulae" => "^Latin4PlinyEpistulaeText",
        "Catullus" => "~Latin3CatullusText",

    ];

    const DICT_DB =
        [
        "Aeneid" => "#APDictionary",
        "DBG" => "#APDictionary",
        "AUC" => "^Latin4Dictionary",
        "InCatilinam" => "^Latin4Dictionary",
        "PlinyEpistulae" => "^Latin4Dictionary",
        "Catullus" => "~Latin3Dictionary",
    ];

    const LATIN_BOOK_TITLE =
        [
        "Aeneid" => "Aenēis",
        "DBG" => "Commentāriī Dē Bellō Gallicō",
        "AUC" => "Ab Urbe Condītā Librī",
        "InCatilinam" => "Ōrātiō in Catilinam Prīma in Senātū Habita",
        "PlinyEpistulae" => "Epistulae",
        "Catullus" => "Carmina Catullī",
    ];

    const BOOK_AUTHOR =
        [
        "Aeneid" => "Pvblivs Vergilivs Maro",
        "DBG" => "Gaivs Ivlivs Caesar",
        "InCatilinam" => "Marcvs Tvllivs Cicero",
        "AUC" => "Titvs Livivs",
        "PlinyEpistulae" => "Gaivs Plinivs Caecilivs Secvndvs",
        "Catullus" => "Gaivs Valerivs Catvllus",
    ];

    const ENGLISH_BOOK_TITLE =
        [
        "Aeneid" => "Aeneid",
        "DBG" => "De Bello Gallico",
        "AUC" => "Ab Urbe Condita",
        "InCatilinam" => "In Catilinam",
        "PlinyEpistulae" => "Epistulae Plīniī Secundī",
        "Catullus" => "Catullus",
    ];

    public static function getHWDB()
    {
        return self::LEVEL_DB[self::getLevel()];
    }

    public static function getNotesDB()
    {
        return self::LEVEL_NOTES_DB[self::getLevel()];
    }

    public static function getDict()
    {
        return self::LEVEL_DICT_DB[self::getLevel()];
    }

    public static function getTextDB()
    {
        return self::BOOK_DB[self::getBookTitle()];
    }

    public static function getLatinTitle()
    {
        return self::LATIN_BOOK_TITLE[self::getBookTitle()];
    }

    public static function getAuthor()
    {
        return self::BOOK_AUTHOR[self::getBookTitle()];
    }

    public static function getEnglishTitle()
    {
        return self::ENGLISH_BOOK_TITLE[self::getBookTitle()];
    }

    public static function getTestStatus()
    {
        $tl = self::getLevel();
        return (SQLQ('SELECT `TestMode' . $tl . '`  FROM `Control Panel`') == "1");
    }

    public static function getLevel()
    {
        if (!isset($_GET['level']))
        {
            if (isset($_GET['title']))
            {
                $d = self::DICT_DB[$_GET['title']];
                $l = array_flip(self::LEVEL_DICT_DB)[$d];
            }
            else
            {
                $l = 'AP';
            }
        }
        else
        {
            $l = $_GET['level'];
        }
        return $l;
    }

    public static function getBookTitle()
    {

        if (isset($_GET['title']))
        {
            $bt = $_GET['title'];
        }
        else if (isset($_GET['hw']))
        {
            $bt = SQLQ('SELECT `BookTitle` FROM `' . self::LEVEL_DB[self::getLevel()] . '` WHERE `HW` = ' . $_GET['hw']);
        }

        return $bt;
    }

}
