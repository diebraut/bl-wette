# Automatische Spielauswertung

`update_matches.php` ist nur per PHP-CLI aufrufbar. Voraussetzung in
`common/config.local.php`: `'automatic_match_updates' => true`.
Ohne diese Einstellung bleibt der bisherige Login-Ablauf aktiv.

Lokal läuft die Windows-Aufgabe **BL-Wette lokale Spielauswertung** alle fünf
Minuten über `wscript.exe` und `bin/update_matches_local.vbs` ohne Konsolenfenster.
Der lokale Wrapper startet `K:\xampp\php\php.exe` nur, wenn Apache und MySQL
aus `K:\xampp` laufen. Sonst endet die Prüfung ohne Auswertung erfolgreich.
Der Task läuft im eingerichteten Benutzerkontext. Kein Browser und kein Login erforderlich.
Protokoll: `bl-wette-match-update.log` im PHP-Verzeichnis `sys_get_temp_dir()`.

- Den aktuellen und den nächsten Spieltag bei jedem Lauf prüfen; dadurch werden
  auch nachträglich geänderte Anstoßtermine von OpenLigaDB übernommen.
- Ab dem aktuell gemeldeten Anstoß eine Begegnung als laufend markieren und den
  Zwischenstand aus der OpenLigaDB-Torfolge bzw. dem aktuellen Ergebnis speichern.
- Solange mindestens eine Begegnung läuft, OpenLigaDB innerhalb desselben
  Hintergrundlaufs alle zehn Sekunden erneut abfragen. Ohne laufendes Spiel
  bleibt es beim regulären Fünf-Minuten-Aufruf. Datenbank und Anzeige werden
  nur bei einem geänderten Spielstand oder Spielstatus aktualisiert.
- Nur `matchIsFinished=true` mit gültigem Endergebnis (`resultTypeID=2`) beenden
  und für die dauerhaften Vereins- und Tipperpunkte werten.
- Vereins- und Tipperpunkte neu summieren; Tipps und Geldbeträge nicht ändern.
- Nach allen neun ausgewerteten Begegnungen auf den nächsten Spieltag wechseln.
  Verschobene/abgebrochene oder unvollständig gemeldete Spiele verhindern dies.
- Anstoßzeiten des geprüften und des folgenden Spieltags aktualisieren.
- API-Fehler protokollieren, beim nächsten Lauf erneut versuchen.
- Parallele Läufe über eine Datenbanksperre verhindern. Keine Schemaänderung.

Tests: `K:\xampp\php\php.exe tests\match_update_test.php`. Ausschließlich
verbindungslokale temporäre Tabellen, keine vorhandenen Daten verändern.

Für den Server ist eine separate Einrichtung eines Fünf-Minuten-Cronjobs mit
passendem PHP-Binary und Dateirechten erforderlich. Vorher Code sichern, Tests
ausführen und den Konfigurationsschalter erst zusammen mit dem Job aktivieren.
Auf dem Server wurde der Job am 12.09.2026 unter
`/etc/cron.d/bl-wette-match-update` aktiviert. Er läuft als Website-Benutzer
mit `/usr/bin/php`; Protokoll: `/var/log/bl-wette-match-update.log`.
Die Serverkonfiguration bleibt separat und wird nicht ins Repository aufgenommen.

Die Anzeige prüft über `user/live.php` während laufender Spiele alle zehn
Sekunden, sonst jede Minute auf Änderungen (ohne OpenLigaDB-Aufruf oder
Session-Verlängerung). Die Seite `alltips.php` prüft während laufender Spiele
ebenfalls alle zehn Sekunden, sonst alle 30 Sekunden. Unveränderte Antworten enthalten
keine Anzeigeinhalte. Ein neuer Spieltag öffnet automatisch die Tippseite;
gewöhnliche Aktualisierungen lassen Tippfelder und Fokus unverändert.

API-Dokumentation: https://github.com/OpenLigaDB/OpenLigaDB-Samples
