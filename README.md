# Uranus Events TYPO3 Extension

Eine produktionsreife TYPO3 14.1 Extension zur Anzeige von Events aus der Uranus Public API.

## Features

- **Vollständige API-Integration** mit Uranus Public API
- **Flexible Filterung** nach Datum, Kategorien, Orten, Städten, Ländern, etc.
- **Pagination** mit Standard-Offset/Limit und Uranus-spezifischer Pagination
- **Tag-basiertes Caching** für optimale Performance
- **Responsive Frontend** mit modernem CSS und AJAX-Loading
- **Mehrere Templates** (Default, Compact, Detailed)
- **Umfangreiche Fehlerbehandlung** mit Logging
- **TYPO3 14.1 kompatibel** mit Dependency Injection
- **Strikte Typisierung** (PHP 8.1+)

## Installation

1. **Via Composer:**
   ```bash
   composer require oklabflensburg/uranus-events
   ```

2. **Extension aktivieren** im TYPO3 Extension Manager

3. **Caches leeren** im TYPO3 Install Tool

## Konfiguration

### Extension-Konfiguration (Extension Manager)

- **API Base URL**: Basis-URL der Uranus API (z.B. `https://api.example.com`)
- **API Endpoint**: Endpoint für Events (Standard: `/api/events`)
- **API Timeout**: Timeout in Sekunden (Standard: 10)
- **Max Retries**: Anzahl Wiederholungsversuche (Standard: 3)
- **Default Cache Lifetime**: Cache-Lebensdauer in Sekunden (Standard: 3600)

### Plugin-Konfiguration

Füge das **"Uranus Events"** Content-Element zu einer Seite hinzu und konfiguriere:

- **Filter-Einstellungen**: Datumsbereich, Suchbegriff, Kategorien, Orte, etc.
- **Anzeige-Einstellungen**: Bilder, Organisationen, Kategorien, Tags, etc.
- **Cache-Einstellungen**: Cache-Lebensdauer, Cache deaktivieren

## Verwendung

### TypoScript einbinden

```typoscript
@import 'EXT:uranus_events/Configuration/TypoScript/constants.typoscript'
@import 'EXT:uranus_events/Configuration/TypoScript/setup.typoscript'
```

### Beispiel-Konfigurationen

1. **Alle Events anzeigen:**
   - Limit: 10
   - Template: Default

2. **Events für eine bestimmte Stadt:**
   - City: Flensburg
   - Show Images: Ja
   - Limit: 20

3. **Events mit Kategorie-Filter:**
   - Categories: 1,5,7
   - Start Date: 2026-04-01
   - End Date: 2026-04-30
   - Template: Compact

## API-Endpunkt

Die Extension verwendet den Uranus Public API Endpoint:

```
GET /api/events
```

Unterstützte Query-Parameter:
- `start`, `end`: Datumsfilter (YYYY-MM-DD)
- `search`: Suchbegriff
- `categories`, `organizations`, `venues`: Komma-getrennte IDs
- `city`, `countries`, `language`: Ortsfilter
- `limit`, `offset`: Pagination
- `last_event_date_id`, `last_event_start_at`: Uranus-spezifische Pagination
- `event_types`: JSON-Array von [type_id, genre_id]

## Architektur

```
Classes/
├── Controller/           # Frontend-Controller
├── Domain/              # Domain Models und DTOs
│   ├── Model/          # Event, EventType
│   └── Dto/            # FilterParameters, EventResponse
├── Service/            # Business Logic
│   ├── ApiClientService.php
│   ├── EventService.php
│   └── LoggingService.php
└── Utility/            # Hilfsklassen

Configuration/
├── FlexForms/          # Plugin-Konfiguration
├── TCA/               # TCA-Overrides
├── TypoScript/        # TypoScript-Konfiguration
└── Services.yaml      # Dependency Injection

Resources/
├── Private/           # Templates, Sprachdateien, CSS
└── Public/           # JavaScript, Icons
```

## Caching

Events werden basierend auf Filter-Parametern gecached. Cache-Tags ermöglichen gezielte Invalidierung.

Cache manuell leeren:
```bash
./typo3/sysext/core/bin/typo3 cache:flush --tags uranus_events
```

## Fehlerbehandlung

Bei API-Fehlern:
- Benutzerfreundliche Fehlermeldung im Frontend
- Leere Event-Liste wird angezeigt
- Fehler werden im TYPO3 Log protokolliert
- Keine PHP-Fatal-Errors

## Entwicklung

### Tests ausführen

```bash
# Unit-Tests
./vendor/bin/phpunit Tests/Unit/

# Functional-Tests (wenn vorhanden)
./vendor/bin/phpunit Tests/Functional/
```

### Eigene Templates hinzufügen

1. Template in `Resources/Private/Templates/Event/` erstellen
2. Template-Option in `Configuration/FlexForms/Events.xml` hinzufügen
3. TypoScript anpassen

### Filter-Parameter erweitern

1. Feld in `FilterParameters` DTO hinzufügen
2. `EventService::applyFilters()` aktualisieren
3. FlexForm-Feld in `Configuration/FlexForms/Events.xml` hinzufügen
4. TypoScript-Konfiguration aktualisieren

## Dokumentation

Ausführliche Dokumentation: `Documentation/Index.rst`

Beispiel-Konfigurationen: `Documentation/Examples.md`

## Lizenz

MIT

## Support

- Dokumentation: Siehe `Documentation/` Ordner
- Issue Tracker: GitHub Repository
- Email: info@oklabflensburg.de

## Credits

- Entwickelt von Oklab Flensburg
- Uranus API vom Uranus Project
- TYPO3 CMS von der TYPO3 Community