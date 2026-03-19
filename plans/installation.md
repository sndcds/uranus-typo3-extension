# Installations- und Konfigurationsanleitung

## 1. Systemvoraussetzungen

### TYPO3-Version
- TYPO3 CMS 14.1 LTS
- Kompatibel mit PHP 8.1 - 8.3

### PHP-Erweiterungen
- cURL (für HTTP-Requests)
- JSON (für API-Response-Parsing)
- mbstring (für String-Operationen)

### Server-Anforderungen
- Mindestens 128 MB PHP Memory Limit
- HTTPS-Unterstützung für sichere API-Kommunikation
- Outbound HTTP/HTTPS-Zugriff auf Uranus-API

## 2. Installation

### Methode A: Über Composer (empfohlen)
```bash
composer require oklabflensburg/uranus-events
```

### Methode B: Manuelle Installation
1. Extension herunterladen oder aus Repository klonen
2. In `typo3conf/ext/` kopieren
3. Im TYPO3-Backend unter "Admin Tools" → "Extension Manager" aktivieren

### Methode C: Git Submodule
```bash
cd /pfad/zum/typo3-projekt
git submodule add https://github.com/oklabflensburg/uranus-events typo3conf/ext/uranus_events
```

## 3. Extension aktivieren

1. Im TYPO3-Backend einloggen
2. Zu "Admin Tools" → "Extension Manager" navigieren
3. Nach "uranus_events" suchen
4. Extension aktivieren

## 4. Konfiguration

### 4.1 Extension-Konfiguration (ext_conf_template.txt)

Im Extension Manager unter "Configure" folgende Werte setzen:

```plaintext
# Basis-URL der Uranus-API
apiBaseUrl = https://api.uranus.example.com

# API-Endpoint für Events
apiEndpoint = /api/events

# Cache-Lebensdauer in Sekunden (Standard: 1 Stunde)
cacheLifetime = 3600

# HTTP-Timeout in Sekunden
httpTimeout = 30

# Maximale Wiederholungsversuche bei API-Fehlern
maxRetries = 3

# Debug-Modus aktivieren (nur für Entwicklung)
debugMode = 0
```

### 4.2 TypoScript-Konfiguration

#### constants.typoscript
```typoscript
plugin.tx_uranusevents {
    view {
        templateRootPath = EXT:uranus_events/Resources/Private/Templates/
        partialRootPath = EXT:uranus_events/Resources/Private/Templates/Partial/
        layoutRootPath = EXT:uranus_events/Resources/Private/Templates/Layouts/
    }
    
    persistence {
        storagePid = 0
    }
    
    settings {
        # Default-Werte für Filter
        limit = 20
        showImages = 1
        showVenueMap = 1
        dateFormat = d.m.Y
        timeFormat = H:i
    }
}
```

#### setup.typoscript
```typoscript
plugin.tx_uranusevents {
    view {
        templateRootPaths {
            0 = {$plugin.tx_uranusevents.view.templateRootPath}
            10 = EXT:uranus_events/Resources/Private/Templates/
        }
        partialRootPaths {
            0 = {$plugin.tx_uranusevents.view.partialRootPath}
            10 = EXT:uranus_events/Resources/Private/Templates/Partial/
        }
        layoutRootPaths {
            0 = {$plugin.tx_uranusevents.view.layoutRootPath}
            10 = EXT:uranus_events/Resources/Private/Templates/Layouts/
        }
    }
    
    persistence {
        storagePid = {$plugin.tx_uranusevents.persistence.storagePid}
    }
    
    settings =< plugin.tx_uranusevents.settings
    
    features {
        # skipDefaultArguments = 1
        requireCHashArgumentForActionArguments = 0
    }
    
    mvc {
        callDefaultActionIfActionCantBeResolved = 1
    }
}

# CSS und JS einbinden
page.includeCSS.uranusEvents = EXT:uranus_events/Resources/Private/CSS/events.css
page.includeJSFooter.uranusEvents = EXT:uranus_events/Resources/Public/JavaScript/events.js
```

## 5. Plugin einrichten

### 5.1 Content Element hinzufügen
1. Neue Seite oder bestehende Seite öffnen
2. Content Element hinzufügen
3. Plugin "Uranus Events" auswählen
4. Plugin konfigurieren

### 5.2 Plugin-Konfiguration (FlexForm)

Im Plugin stehen folgende Filter-Optionen zur Verfügung:

#### Datumsfilter
- **Startdatum**: Events ab diesem Datum (Format: YYYY-MM-DD)
- **Enddatum**: Events bis zu diesem Datum (Format: YYYY-MM-DD)

#### Suchfilter
- **Suchbegriff**: Volltextsuche in Event-Titeln und Beschreibungen

#### Kategorienfilter
- **Kategorien**: Kommaseparierte Kategorie-IDs (z.B. "1,5,12")
- **Organisationen**: Kommaseparierte Organisations-IDs
- **Veranstaltungsorte**: Kommaseparierte Venue-IDs

#### Geografische Filter
- **Stadt**: Events in bestimmter Stadt
- **Länder**: ISO-3166-1-Alpha-3-Codes (z.B. "DEU,DNK,AUT")

#### Pagination
- **Limit**: Anzahl Events pro Seite (Standard: 20)
- **Offset**: Startposition (für manuelle Pagination)

### 5.3 Beispiel-Konfigurationen

#### Alle kommenden Events in Flensburg
```yaml
start: "" # leer = ab heute
end: "" # leer = kein Enddatum
city: "Flensburg"
limit: 50
```

#### Events einer bestimmten Organisation
```yaml
organizations: "9" # Aktivitetshuset
limit: 20
```

#### Events in einem Zeitraum
```yaml
start: "2026-03-01"
end: "2026-03-31"
limit: 100
```

## 6. Caching konfigurieren

### 6.1 Cache-Backend
Die Extension verwendet standardmäßig das TYPO3-Datenbank-Cache-Backend. Für bessere Performance kann Redis oder Memcached konfiguriert werden:

```php
// In AdditionalConfiguration.php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['uranus_events'] = [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\RedisBackend::class,
    'options' => [
        'database' => 1,
        'hostname' => '127.0.0.1',
        'port' => 6379,
        'defaultLifetime' => 3600,
    ],
    'groups' => ['pages', 'all'],
];
```

### 6.2 Cache leeren
1. **Manuell**: Im TYPO3-Backend unter "Admin Tools" → "Maintenance" → "Clear all caches"
2. **CLI**: `./vendor/bin/typo3 cache:flush`
3. **Automatisch**: Bei Änderungen der Extension-Konfiguration

## 7. Fehlerbehandlung und Logging

### 7.1 Log-Level konfigurieren
```php
// In AdditionalConfiguration.php
$GLOBALS['TYPO3_CONF_VARS']['LOG']['OklabFlensburg']['UranusEvents']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            'logFile' => 'typo3temp/var/log/uranus_events.log'
        ]
    ],
    \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            'logFile' => 'typo3temp/var/log/uranus_errors.log'
        ]
    ]
];
```

### 7.2 Debug-Modus
Für Entwicklung kann der Debug-Modus aktiviert werden:
1. In Extension-Konfiguration `debugMode = 1` setzen
2. Detaillierte Logs werden geschrieben
3. API-Responses werden im Frontend angezeigt (nur für Entwickler)

## 8. Performance-Optimierung

### 8.1 Cache-Optimierung
- **Cache-Lebensdauer** an Nutzungsmuster anpassen
- **Cache-Warmup** über Scheduler-Task einrichten
- **OPcache** für PHP aktivieren

### 8.2 API-Optimierung
- **HTTP/2** für API-Aufrufe nutzen
- **Keep-Alive** Connections aktivieren
- **GZIP-Kompression** für API-Responses

### 8.3 Frontend-Optimierung
- **Lazy Loading** für Bilder aktivieren
- **CSS/JS minifizieren**
- **CDN** für statische Assets nutzen

## 9. Sicherheitskonfiguration

### 9.1 API-Zugriff
- **HTTPS** für alle API-Aufrufe erzwingen
- **API-Key** (falls erforderlich) sicher speichern
- **Rate Limiting** für API-Aufrufe implementieren

### 9.2 Frontend-Sicherheit
- **XSS-Schutz**: Alle Ausgaben werden automatisch escaped
- **CSRF-Schutz**: Für Formulare aktivieren
- **Content Security Policy**: Konfigurieren

### 9.3 Datenvalidierung
- **Input-Validierung**: Alle Filter-Parameter werden validiert
- **Output-Escaping**: Automatisch in Fluid-Templates
- **SQL-Injection**: Nicht relevant, da keine Datenbank-Persistenz

## 10. Troubleshooting

### 10.1 Häufige Probleme und Lösungen

#### Problem: "Events konnten nicht geladen werden"
- **Ursache**: API nicht erreichbar
- **Lösung**: 
  1. API-URL in Extension-Konfiguration prüfen
  2. Netzwerkverbindung testen
  3. Firewall-Einstellungen prüfen

#### Problem: "Keine Events gefunden"
- **Ursache**: Filter zu restriktiv
- **Lösung**:
  1. Filter-Einstellungen überprüfen
  2. Datumsbereich erweitern
  3. Suchbegriff anpassen

#### Problem: "Langsame Ladezeiten"
- **Ursache**: API-Antwortzeiten oder Cache-Probleme
- **Lösung**:
  1. Cache-Lebensdauer erhöhen
  2. Limit reduzieren
  3. API-Performance prüfen

#### Problem: "Bilder werden nicht angezeigt"
- **Ursache**: Image-Pfade nicht erreichbar
- **Lösung**:
  1. CORS-Einstellungen prüfen
  2. Proxy-Konfiguration für Bilder
  3. Platzhalter-Bilder verwenden

### 10.2 Debugging

#### Logs prüfen
```bash
# TYPO3-Logs
tail -f typo3temp/var/log/uranus_events.log
tail -f typo3temp/var/log/uranus_errors.log

# PHP-Error-Log
tail -f /var/log/php/error.log
```

#### API-Aufrufe testen
```bash
# API-Endpoint manuell testen
curl "https://api.uranus.example.com/api/events?limit=1"

# Mit Filter-Parametern
curl "https://api.uranus.example.com/api/events?city=Flensburg&limit=5"
```

#### Cache-Status prüfen
```php
// In einem TYPO3-Modul oder via CLI
$cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('uranus_events');
$info = $cache->getBackend()->getCacheInfo();
print_r($info);
```

## 11. Update der Extension

### 11.1 Composer Update
```bash
composer update oklabflensburg/uranus-events
```

### 11.2 Manuelles Update
1. Alte Extension deaktivieren
2. Neue Version herunterladen
3. Alte Dateien ersetzen
4. Extension aktivieren
5. Datenbank-Updates durchführen (falls nötig)

### 11.3 Breaking Changes
- Bei Major-Version-Updates: Changelog prüfen
- Konfiguration anpassen
- Templates aktualisieren

## 12. Deinstallation

### 12.1 Composer Deinstallation
```bash
composer remove oklabflensburg/uranus-events
```

### 12.2 Manuelle Deinstallation
1. Extension im Extension Manager deaktivieren
2. Extension löschen
3. TypoScript-Templates entfernen
4. Cache leeren

## 13. Support und Dokumentation

### 13.1 Ressourcen
- **GitHub Repository**: https://github.com/oklabflensburg/uranus-events
- **Dokumentation**: `/Documentation/` im Extension-Verzeichnis
- **API-Dokumentation**: https://sndcds.github.io/uranus-docs/api/public/get/events/

### 13.2 Kontakt
- **Issues**: GitHub Issue Tracker
- **E-Mail**: support@oklab-flensburg.de
- **Community**: TYPO3 Slack Channel #ext-uranus-events

### 13.3 Beitragen
1. Fork Repository
2. Feature-Branch erstellen
3. Änderungen implementieren
4. Tests schreiben
5. Pull Request erstellen

---

## Zusammenfassung

Die Extension `uranus_events` ist nach dieser Anleitung vollständig installiert und konfiguriert. Die wichtigsten Schritte:

1. **Installation** über Composer oder manuell
2. **Konfiguration** der API-URL und Cache-Einstellungen
3. **TypoScript** einbinden
4. **Plugin** als Content Element hinzufügen
5. **Filter** nach Bedarf konfigurieren

Die Extension ist nun produktionsbereit und zeigt Events aus der Uranus-API im TYPO3-Frontend an.