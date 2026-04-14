<?php

/**
 * csv2ics
 *
 * Copyright (c) 2014-2019 Martin Kozianka <kozianka.de>
 *
 * @package Csv2ics
 * @link    https://csv2ics.kozianka.de
 * @license MIT License
 */

namespace Csv2ics;

class Manager
{
    private const MAX_UPLOAD_SIZE = 2097152;

    private const ALLOWED_MIME_TYPES = [
        'text/plain',
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
    ];

    public static function getPath()
    {
        $dirname = dirname(__FILE__);
        $dirname = substr($dirname, 0, strlen($dirname) - strlen('/src'));
        return $dirname . '/temp';
    }

    public static function handleUpload()
    {
        $data = [
            'name' => null,
            'extension' => null,
            'mime' => null,
            'size' => null,
            'md5' => null,
            'uploadError' => null,
        ];

        if (!array_key_exists('file', $_FILES) || !is_array($_FILES['file'])) {
            $data['uploadError'] = 'No file uploaded.';
            return $data;
        }

        $uploadedFile = $_FILES['file'];
        $uploadError = (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError !== UPLOAD_ERR_OK) {
            $data['uploadError'] = static::getUploadErrorMessage($uploadError);
            return $data;
        }

        $tmpName = (string) ($uploadedFile['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $data['uploadError'] = 'Upload validation failed.';
            return $data;
        }

        $size = (int) ($uploadedFile['size'] ?? 0);
        if ($size > static::MAX_UPLOAD_SIZE) {
            $data['uploadError'] = 'File is too large.';
            return $data;
        }

        $mime = static::detectMimeType($tmpName);
        if ($mime === null || !in_array($mime, static::ALLOWED_MIME_TYPES, true)) {
            $data['uploadError'] = 'Unsupported file type.';
            return $data;
        }

        $path = static::getPath();
        if (!is_dir($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            $data['uploadError'] = 'Upload directory could not be created.';
            return $data;
        }

        $extension = static::resolveExtension((string) ($uploadedFile['name'] ?? ''), $mime);
        $filename = uniqid('fn', true);
        $targetName = $filename . ($extension !== '' ? '.' . $extension : '');
        $targetPath = $path . '/' . $targetName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            $data['uploadError'] = 'Uploaded file could not be saved.';
            return $data;
        }

        $data['name'] = $targetName;
        $data['extension'] = $extension;
        $data['mime'] = $mime;
        $data['size'] = filesize($targetPath) ?: $size;
        $data['md5'] = md5_file($targetPath) ?: null;

        return $data;
    }

    private static function getUploadErrorMessage($uploadError)
    {
        switch ($uploadError) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large.';
            case UPLOAD_ERR_PARTIAL:
                return 'Upload was interrupted.';
            case UPLOAD_ERR_NO_FILE:
                return 'No file uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Temporary upload directory is missing.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Uploaded file could not be written.';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload was blocked by a PHP extension.';
            default:
                return 'Unknown upload error.';
        }
    }

    private static function detectMimeType($path)
    {
        if (!function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return is_string($mime) ? $mime : null;
    }

    private static function resolveExtension($originalName, $mime)
    {
        $extension = strtolower(trim(pathinfo($originalName, PATHINFO_EXTENSION)));

        if ($extension !== '') {
            return preg_replace('/[^a-z0-9]+/', '', $extension);
        }

        if ($mime === 'text/csv' || $mime === 'application/csv') {
            return 'csv';
        }

        return 'txt';
    }

    public static function getErrorMessage()
    {
        $message = null;
        if (array_key_exists('errorMessage', $_SESSION)) {
            $message = $_SESSION['errorMessage'];
            session_unset();
            session_destroy();
        }
        return $message;
    }

    public static function handleError($strMessage)
    {
        $_SESSION['errorMessage'] = $strMessage;
        header('Location: ' . str_replace('index.php', '', $_SERVER['PHP_SELF']));
        exit;
    }

}
