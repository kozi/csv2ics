<?php $this->layout('layout'); ?>
<h3>Ergebnis</h3>

<p>
Über den folgenden Link kann einmalig eine iCal-Datei mit den
angezeigten Ereignissen abgerufen werden.
</p>

<p><a href="?ics=<?=$icsKey?>">iCal-Datei herunterladen (calendar.ics)</a></p>

<p>Folgende Ereignisse wurden gefunden:</p>

<table>
    <thead>
        <tr>
            <th>Datum</th>
            <th>Titel</th>
            <th>Ort</th>
        </tr>
        <tr>
            <th colspan="3">Beschreibung</th>
        </tr>
    </thead>
    <tbody>
<?php foreach($csvData as $row): ?>
    <tr class="row1 <?=$row->cssClass?>">
        <td class="date"><?=$row->date?></td>
        <td class="title"><?=$row->title?></td>
        <td class="location"><?=$row->location?></td>
    </tr>
    <tr class="row2 <?=$row->cssClass?>">
        <td colspan="3" class="description"><?=$row->description?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>