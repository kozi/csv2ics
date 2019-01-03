
### Wie muss die Excel- bzw. CSV-Datei aussehen?

* 1 Zeile pro Termin
* Export aus Excel am besten als *Tabstoppgetrennter Text (*.txt)*
* **Spalten**: Datum, Titel, Beschreibung, Ort
  * **Datum**: Datum des Termins (verschiedene Formate möglich)
  * **Titel**: Titel des Termins
  * **Beschreibung**: Details zu dem Termin
  * **Ort**: Wo findet der Termin statt?
* **Datum**
  * Termin an einem bestimmten Tag: **TT.MM.JJJJ**
  * Termin mit Uhrzeit: **TT.MM.JJJJ SS:MM** 
  * Termin mit festgelegter Dauer: **TT.MM.JJJJ SS:MM-SS:MM** 
  * Termin über mehrere Tage: **TT.MM.JJJJ-TT.MM.JJJJ**
  * Die Jahresangabe kann auch 2-stellig erfolgen **JJ** statt **JJJJ**
* **Info**: Die hochgeladenen Dateien werden sofort nach der Konvertierung gelöscht.
      Die generierten ICS-Dateien werden nur an den Browser geschickt und nicht auf
      dem Server gespeichert. Es verbleiben also keinerlei persönliche Daten auf dem
      Server.
* **Beispieldateien**
  * [termine.xls](docs/examples/termine.xls) Excel-Datei mit Beispielterminen
  * [termine.csv](docs/examples/termine.csv) Beispieltermine in einer CSV-Datei
  * [termine.txt](docs/examples/termine.txt) Tabstoppgetrennter Text mit Beispielterminen

### Development

* Den Quellcode findet ihr hier: <https://github.com/kozi/csv2ics>
* Für die Realisierung habe ich folgende Pakete verwendet:
  * sabre/vobject <http://sabre.io/vobject/>
  * league/csv <http://csv.thephpleague.com/>
  * league/plates <http://platesphp.com/>
  * michelf/php-markdown <https://michelf.ca/projects/php-markdown/>
  * codeguy/upload <https://github.com/codeguy/Upload>
