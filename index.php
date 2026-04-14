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

require 'vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderPartial($templatePath, array $data = [])
{
    extract($data, EXTR_SKIP);

    ob_start();
    require $templatePath;

    return ob_get_clean();
}

function renderTemplate($templatePath, array $data = [], array $layoutData = [])
{
    $content = renderPartial($templatePath, $data);

    return renderPartial(dirname($templatePath) . '/layout.php', array_merge($layoutData, [
        'content' => $content,
    ]));
}

date_default_timezone_set('Europe/Berlin');

session_start();

$strPath = dirname(__FILE__);
$templatePath = $strPath . '/templates';
$markdownConverter = new CommonMarkConverter();
$layoutData = [
    'title' => 'csv2ics',
    'copyright' => 'Copyright 2014-' . (new \DateTime())->format('Y') . ' Martin Kozianka (kozianka.de)',
    'errorMessage' => Manager::getErrorMessage(),
];

// Gib die ICS-Datei aus!
if (array_key_exists('ics', $_GET)) {
    $csvData = null;
    $sessionKey = $_GET['ics'];
    if (array_key_exists($sessionKey, $_SESSION)) {
        $csvData = $_SESSION[$sessionKey];
    }

    if ($csvData === null) {
        Manager::handleError('CSV data not found.');
    }

    $objConverter = new Converter(null, $csvData);

    session_unset();
    session_destroy();

    $objConverter->getIcsFile($csvData);
} elseif (array_key_exists('result', $_GET)) {
    $strFilename = null;
    $fileKey = $_GET['result'];

    // Die hochgeladene Datei verarbeiten
    if (array_key_exists($fileKey, $_SESSION)) {
        $strFilename = $_SESSION[$fileKey];
    }
    $strPath = Manager::getPath() . '/' . $strFilename;

    if (!file_exists($strPath) || $strFilename === null) {
        Manager::handleError('File not found.');
    }

    try
    {
        $objConverter = new Converter($strPath);
    } catch (\Exception $e) {
        // Datei löschen
        unlink($strPath);
        Manager::handleError($e->getMessage());
    }

    // Datei löschen und den Wert aus der Session entfernen
    unlink($strPath);
    unset($_SESSION[$fileKey]);

    $icsKey = 'csv2ics' . uniqid();
    $_SESSION[$icsKey] = $objConverter->csvData;

    $arrConvert = ['icsKey' => $icsKey, 'csvData' => $objConverter->csvData];

    echo renderTemplate($templatePath . '/result.php', $arrConvert, $layoutData);
} else {
    $arrUpload = [];

    // Wurde das Formular abgesendet?
    if (array_key_exists('upload', $_GET)) {
        $arrUpload = Manager::handleUpload();

        // Gab es Fehler im Formular?
        if ($arrUpload['uploadError'] === null) {
            $key = 'csv2ics' . uniqid();
            $_SESSION[$key] = $arrUpload['name'];
            header("Location: ?result=" . $key);
            exit;
        } else {
            Manager::handleError($arrUpload['uploadError']);
        }

    }

    $strReadme = file_get_contents($strPath . '/docs/README.md');
    $strDetails = file_get_contents($strPath . '/docs/DETAILS.md');
    $arrUpload['mdReadme'] = $markdownConverter->convert($strReadme)->getContent();
    $arrUpload['mdDetails'] = $markdownConverter->convert($strDetails)->getContent();

    echo renderTemplate($templatePath . '/upload.php', $arrUpload, $layoutData);
}
