# Automatische Spielauswertung

`update_matches.php` ist nur per PHP-CLI aufrufbar. Voraussetzung in
`common/config.local.php`: `'automatic_match_updates' => true`.
Ohne diese Einstellung bleibt der bisherige Login-Ablauf aktiv.

Lokal läuft die Windows-Aufgabe **BL-Wette lokale Spielauswertung** alle fünf
Minuten mit `K:\xampp\php\php.exe`. Rechner und MySQL müssen laufen; der Task
läuft im eingerichteten Benutzerkontext. Kein Browser und kein Login erforderlich.
Protokoll: `bl-wette-match-update.log` im PHP-Verzeichnis `sys_get_temp_dir()`.

- Ab 105 Minuten nach dem gespeicherten Anpfiff: offene Begegnungen des
  betreffenden Spieltags bei OpenLigaDB prüfen (ein Aufruf je Spieltag).
- Nur `matchIsFinished=true` mit gültigem Endergebnis (`resultTypeID=2`) werten.
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

Die Anzeige prüft über `user/live.php` jede Minute auf Änderungen (ohne
OpenLigaDB-Aufruf oder Session-Verlängerung). Unveränderte Antworten enthalten
keine Anzeigeinhalte. Ein neuer Spieltag öffnet automatisch die Tippseite;
gewöhnliche Aktualisierungen lassen Tippfelder und Fokus unverändert.

API-Dokumentation: https://github.com/OpenLigaDB/OpenLigaDB-Samples
