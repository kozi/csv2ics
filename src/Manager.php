<?php

/**
 * csv2ics
 *
 * Copyright (c) 2014-2015 Martin Kozianka <kozianka.de>
 *
 * @package Csv2ics
 * @link    https://csv2ics.kozianka.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Csv2ics;

use Upload\Storage\FileSystem;
use Upload\File;

class Manager
{
    public static function getPath()
    {
        $dirname = dirname(__FILE__);
        $dirname = substr($dirname, 0, strlen($dirname) - strlen('/src'));
        return $dirname.'/temp';
    }

    public static function handleUpload()
    {
        $path     = static::getPath();
        $storage  = new FileSystem($path);
        $filename = uniqid('fn', true);

        try {
            $file = new File('file', $storage);
        }
        catch (\Exception $e) {
            static::handleError($e->getMessage());
        }

        $file->setName($filename);
        $file->addValidations([
            new \Upload\Validation\Mimetype('text/plain'),
            new \Upload\Validation\Size('2M')
        ]);

        $data = [
            'name'        => $file->getNameWithExtension(),
            'extension'   => $file->getExtension(),
            'mime'        => $file->getMimetype(),
            'size'        => $file->getSize(),
            'md5'         => $file->getMd5(),
            'uploadError' => null
        ];

        try
        {
            // Success!
            $file->upload();
        }
        catch (\Exception $e)
        {
            // Fail!
            $data['uploadError'] = implode(', ', $file->getErrors());
        }
        return $data;
    }

    public static function getErrorMessage()
    {
        $message = null;
        if (array_key_exists('errorMessage', $_SESSION))
        {
            $message = $_SESSION['errorMessage'];
            session_unset();
            session_destroy();
        }
        return $message;
    }

    public static function handleError($strMessage)
    {
        $_SESSION['errorMessage'] = $strMessage;
        header('Location: '.str_replace('index.php', '', $_SERVER['PHP_SELF']));
        exit;
    }

}
