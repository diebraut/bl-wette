-- Einmalig für bestehende Datenbanken ausführen.
-- Die Spalte bleibt für alte Begegnungen leer, bis der OpenLigaDB-Abgleich
-- den jeweiligen Anstoßzeitpunkt geliefert hat.
ALTER TABLE tblspieltag
    ADD COLUMN dtmStart DATETIME NULL DEFAULT NULL
    AFTER intMatchIdFromOpenLigaDB;
