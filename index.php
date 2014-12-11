<?php

namespace Csv2ics;

require 'vendor/autoload.php';

use League\Plates\Engine;
use Michelf\MarkdownExtra;

date_default_timezone_set('Europe/Berlin');

session_start();

$strPath = dirname(__FILE__);
$engine  = new Engine($strPath.'/templates');
$engine->addData(array(
    'title'        => 'csv2ics',
    'copyright'    => 'Copyright '.(new \DateTime())->format('Y').' Martin Kozianka (kozianka.de)',
    'errorMessage' => Manager::getErrorMessage()
));

// Gib die ICS-Datei aus!
if(array_key_exists('ics', $_GET)) {
    $csvData    = null;
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
}
elseif(array_key_exists('result', $_GET)) {
    $strFilename = null;
    $fileKey     = $_GET['result'];

    // Die hochgeladene Datei verarbeiten
    if (array_key_exists($fileKey, $_SESSION)) {
        $strFilename   = $_SESSION[$fileKey];
    }
    $strPath = Manager::getPath().'/'.$strFilename;

    if (!file_exists($strPath) || $strFilename === null) {
        Manager::handleError('File not found.');
    }

    try {
        $objConverter = new Converter($strPath);
    }
    catch(\Exception $e) {
        // Datei löschen
        unlink($strPath);
        Manager::handleError($e->getMessage());
    }

    // Datei löschen und den Wert aus der Session entfernen
    unlink($strPath);
    unset($_SESSION[$fileKey]);

    $icsKey            = 'csv2ics'.uniqid();
    $_SESSION[$icsKey] = $objConverter->csvData;


    $arrConvert   = array(
        'icsKey'  => $icsKey,
        'csvData' => $objConverter->csvData,
    );

    echo $engine->render('result', $arrConvert);
}
else {

    $arrUpload = array();

    // Wurde das Formular abgesendet?
    if(array_key_exists('upload', $_GET)) {
        $arrUpload = Manager::handleUpload();

        // Gab es Fehler im Formular?
        if ($arrUpload['uploadError'] === null) {
            $key            = 'csv2ics'.uniqid();
            $_SESSION[$key] = $arrUpload['name'];
            header("Location: ?result=".$key);
            exit;
        }
        else {
            Manager::handleError($arrUpload['uploadError']);
        }

    }
    $strReadme              = file_get_contents($strPath.'/docs/README.md');
    $strDetails             = file_get_contents($strPath.'/docs/DETAILS.md');
    $arrUpload['mdReadme']  = MarkdownExtra::defaultTransform($strReadme);
    $arrUpload['mdDetails'] = MarkdownExtra::defaultTransform($strDetails);

    echo $engine->render('upload', $arrUpload);
}
