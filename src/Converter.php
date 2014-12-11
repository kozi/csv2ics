<?php

/**
 * csv2ics
 *
 * Copyright (c) 2014 Martin Kozianka <kozianka.de>
 *
 * @package Csv2ics
 * @link    https://csv2ics.kozianka.de
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Csv2ics;

use League\Csv\Reader;
use Sabre\VObject\Component\VCalendar;

class Converter {
    private $calendar   = null;
    public $csvData     = null;

    public function __construct($strPath, $csvData = null) {
        mb_internal_encoding('UTF-8');
        mb_substitute_character('none');
        mb_detect_order(array('ASCII', 'ISO-2022-JP', 'UTF-8', 'EUC-JP', 'ISO-8859-1'));

        if ($strPath === null) {
            $this->csvData = $csvData;
            return;
        }

        $strContent    = file_get_contents($strPath);
        $strContent    = mb_convert_encoding($strContent, 'UTF-8', mb_detect_encoding($strContent));
        $csvReader     = Reader::createFromString($strContent);
        $arrDelimiter  = $csvReader->detectDelimiterList();

        if (count($arrDelimiter) === 0) {
            throw new \Exception('File not compatible.');
        }

        $csvReader->setDelimiter($arrDelimiter[0]);

        try {
            $data = $csvReader->fetchAssoc();
        }
        catch(\Exception $e) {
            throw new \Exception('File not compatible.');
        }

        $i       = 0;
        $csvData = array();
        foreach($data as $row) {

            $row      = array_change_key_case($row, CASE_LOWER);
            $cssClass = ($i++ %2 === 0) ? 'odd' : 'even';
            $objRow   = (object) array(
                'cssClass'    => $cssClass,
                'title'       => array_key_exists('titel', $row) ? trim($row['titel']) : '',
                'date'        => array_key_exists('datum', $row) ? trim($row['datum']) : '',
                'description' => array_key_exists('beschreibung', $row) ? trim($row['beschreibung']) : '',
                'location'    => array_key_exists('ort', $row) ? trim($row['ort']) : ''
            );

            if (strlen($objRow->title) === 0 || strlen($objRow->date) === 0) {
                $objRow->cssClass = $cssClass.'Error';
            }

            $csvData[] = $objRow;
        }

        $this->csvData = $csvData;

    }

    private function addEvent($obj) {
        $event = $this->calendar->createComponent('VEVENT');

        if (strlen($obj->title) > 0) {
            $event->SUMMARY = $obj->title;
        }
        if (strlen($obj->description) > 0) {
            $event->DESCRIPTION = $obj->description;
        }
        if (strlen($obj->location) > 0) {
            $event->LOCATION = $obj->location;
        }

        $event->DTSTAMP->setDateTime(new \DateTime());

        if ($this->setDate($event, $obj->date)) {
            $this->calendar->add($event);
        }
    }

    private function setDate(&$event, $strDatum) {
        $strDate    = trim($strDatum);
        $intLen     = strlen($strDate);

        if ($intLen === 0 ) {
            return false;
        }

        if ($intLen === 10 || $intLen === 8) { // TT.MM.JJ oder TT.MM.JJJJ
            $strIcsDate = $this->getIcsDate($strDate);
            $event->DTSTART          = $strIcsDate;
            $event->DTSTART['VALUE'] = 'DATE';
            $event->DTEND            = $strIcsDate;
            $event->DTEND['VALUE']   = 'DATE';
            return true;
        }
        $arrDate = explode('-', $strDate);
        $arrDate = array_map('trim', $arrDate);

        if (count($arrDate) === 2 && strlen($arrDate[0]) >= 8 && strlen($arrDate[1]) >= 8) {
            // TT.MM.JJ-TT.MM.JJ
            // TT.MM.JJJJ-TT.MM.JJJJ
            $event->DTSTART          = $this->getIcsDate($arrDate[0]);
            $event->DTSTART['VALUE'] = 'DATE';

            // Hier muss 1 Tag hinzugefügt werden sonst wird der letzte
            // Tag nicht mit eingeschlossen
            $event->DTEND            = $this->getIcsDate($arrDate[1], '1 Day');
            $event->DTEND['VALUE']   = 'DATE';
            return true;
        }

        $arrDate = explode(' ', $strDate);

        if (count($arrDate) === 2 && strpos($arrDate[1], ':') !== false) {

            if (strpos($arrDate[1], '-') === false) {
                // TT.MM.JJ SS:MM oder TT.MM.JJJJ SS:MM
                $strDate        = $arrDate[0].' '.$arrDate[1];
                $event->DTSTART = $this->getIcsDateTime($strDate);
                $event->DTEND   = $this->getIcsDateTime($strDate);
                return true;
            }
            else {
                $arrTime = explode('-', $arrDate[1]);
                $arrTime = array_map('trim', $arrTime);
                // TT.MM.JJ SS:MM-SS:MM oder TT.MM.JJJJ SS:MM-SS:MM
                $event->DTSTART = $this->getIcsDateTime($arrDate[0].' '.$arrTime[0]);
                $event->DTEND   = $this->getIcsDateTime($arrDate[0].' '.$arrTime[1]);
                return true;
            }
        }
        return true;
    }

    private function getIcsDate($strDate, $strDateInterval = false) {
        $fmt = (strlen($strDate) === 8) ? 'd.m.y' : 'd.m.Y';
        $dt  = \DateTime::createFromFormat($fmt, $strDate);

        if ($strDateInterval !== false) {
            $dt->add(\DateInterval::createFromDateString($strDateInterval));
        }
        return $dt->format('Ymd');
    }

    private function getIcsDateTime($strDate) {
        $fmt = (strlen($strDate) === 14) ? 'd.m.y H:i' : 'd.m.Y H:i';
        return \DateTime::createFromFormat($fmt, $strDate);
    }


    public function getIcsFile() {
        $this->calendar         = new VCalendar();
        $this->calendar->prodid = 'csv2ics // http://csv2ics.kozianka.de';
        foreach ($this->csvData as $entry) {
            $this->addEvent($entry);
        }
        header('Content-type: text/calendar');
        header('Content-Disposition: attachment; filename="calendar.ics"');
        echo $this->calendar->serialize();
        exit;
    }
}
