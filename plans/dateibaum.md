# Dateibaum-Struktur für Uranus Events Extension

```
uranus_events/
├── composer.json
├── ext_emconf.php
├── ext_localconf.php
├── ext_tables.php
├── Configuration/
│   ├── Services.yaml
│   ├── TCA/
│   │   └── Overrides/
│   │       └── tt_content.php
│   └── TypoScript/
│       ├── constants.typoscript
│       └── setup.typoscript
├── Documentation/
│   └── Index.rst
├── Resources/
│   ├── Private/
│   │   ├── Language/
│   │   │   ├── locallang.xlf
│   │   │   └── locallang_db.xlf
│   │   ├── Templates/
│   │   │   ├── Event/
│   │   │   │   └── List.html
│   │   │   ├── Partial/
│   │   │   │   ├── Event/
│   │   │   │   │   ├── Item.html
│   │   │   │   │   ├── Pagination.html
│   │   │   │   │   └── FilterInfo.html
│   │   │   │   └── Shared/
│   │   │   │       ├── Header.html
│   │   │   │       └── Footer.html
│   │   │   └── Layouts/
│   │   │       └── Default.html
│   │   └── CSS/
│   │       └── events.css
│   └── Public/
│       ├── Icons/
│       │   └── Extension.svg
│       └── JavaScript/
│           └── events.js
├── Classes/
│   ├── Controller/
│   │   └── EventController.php
│   ├── Domain/
│   │   ├── Model/
│   │   │   ├── Event.php
│   │   │   ├── EventResponse.php
│   │   │   ├── Venue.php
│   │   │   ├── Organization.php
│   │   │   └── EventType.php
│   │   └── Dto/
│   │       └── FilterParameters.php
│   ├── Service/
│   │   ├── ApiClientService.php
│   │   ├── EventService.php
│   │   ├── CacheService.php
│   │   └── LoggingService.php
│   └── Utility/
│       ├── DateUtility.php
│       ├── ArrayUtility.php
│       └── ValidationUtility.php
├── Tests/
│   ├── Unit/
│   │   ├── Service/
│   │   │   ├── ApiClientServiceTest.php
│   │   │   └── EventServiceTest.php
│   │   └── Domain/
│   │       └── Model/
│   │           └── EventTest.php
│   └── Functional/
│       └── Controller/
│           └── EventControllerTest.php
└── ext_conf_template.txt
```

## Dateibeschreibungen

### Root-Level Dateien

1. **`composer.json`** - Composer-Konfiguration mit Abhängigkeiten (Guzzle, TYPO3 Core)
2. **`ext_emconf.php`** - Extension-Metadaten für TYPO3 Extension Manager
3. **`ext_localconf.php`** - Extension-Konfiguration (Dienstregistrierung, Cache-Konfiguration)
4. **`ext_tables.php`** - Tabellen-Definitionen (falls benötigt)
5. **`ext_conf_template.txt`** - Konfigurationsoptionen für Extension Manager

### Configuration-Verzeichnis

6. **`Configuration/Services.yaml`** - Dependency Injection Konfiguration für Symfony DI
7. **`Configuration/TCA/Overrides/tt_content.php`** - TCA-Override für Content Element Plugin
8. **`Configuration/TypoScript/constants.typoscript`** - TypoScript Konstanten
9. **`Configuration/TypoScript/setup.typoscript`** - TypoScript Setup

### Resources-Verzeichnis

10. **`Resources/Private/Language/locallang.xlf`** - Frontend-Sprachdatei
11. **`Resources/Private/Language/locallang_db.xlf`** - Backend-Sprachdatei für TCA
12. **`Resources/Private/Templates/Event/List.html`** - Haupt-Template für Event-Liste
13. **`Resources/Private/Templates/Partial/Event/Item.html`** - Partial für einzelnes Event
14. **`Resources/Private/Templates/Partial/Event/Pagination.html`** - Partial für Pagination
15. **`Resources/Private/Templates/Partial/Event/FilterInfo.html`** - Partial für Filter-Info
16. **`Resources/Private/Templates/Layouts/Default.html`** - Default Layout
17. **`Resources/Private/CSS/events.css`** - Basis-CSS für Event-Darstellung
18. **`Resources/Public/JavaScript/events.js`** - JavaScript für interaktive Features

### Classes-Verzeichnis (PSR-4)

19. **`Classes/Controller/EventController.php`** - Frontend Controller
20. **`Classes/Domain/Model/Event.php`** - Event Domain Model
21. **`Classes/Domain/Model/EventResponse.php`** - API Response Container
22. **`Classes/Domain/Model/Venue.php`** - Veranstaltungsort Model
23. **`Classes/Domain/Model/Organization.php`** - Organisation Model
24. **`Classes/Domain/Model/EventType.php`** - Event-Typ Model
25. **`Classes/Domain/Dto/FilterParameters.php`** - Filter Parameter DTO
26. **`Classes/Service/ApiClientService.php`** - API Client Service
27. **`Classes/Service/EventService.php`** - Event Business Logic Service
28. **`Classes/Service/CacheService.php`** - Cache Service
29. **`Classes/Service/LoggingService.php`** - Logging Service
30. **`Classes/Utility/DateUtility.php`** - Datum-Hilfsfunktionen
31. **`Classes/Utility/ArrayUtility.php`** - Array-Hilfsfunktionen
32. **`Classes/Utility/ValidationUtility.php`** - Validierungs-Hilfsfunktionen

### Tests-Verzeichnis

33. **`Tests/Unit/Service/ApiClientServiceTest.php`** - Unit Tests für API Client
34. **`Tests/Unit/Service/EventServiceTest.php`** - Unit Tests für Event Service
35. **`Tests/Unit/Domain/Model/EventTest.php`** - Unit Tests für Event Model
36. **`Tests/Functional/Controller/EventControllerTest.php`** - Functional Tests für Controller

### Dokumentation

37. **`Documentation/Index.rst`** - Sphinx-Dokumentation (optional)

## Namespace-Struktur

```
OklabFlensburg\UranusEvents\Controller\EventController
OklabFlensburg\UranusEvents\Domain\Model\Event
OklabFlensburg\UranusEvents\Domain\Model\EventResponse
OklabFlensburg\UranusEvents\Domain\Model\Venue
OklabFlensburg\UranusEvents\Domain\Model\Organization
OklabFlensburg\UranusEvents\Domain\Model\EventType
OklabFlensburg\UranusEvents\Domain\Dto\FilterParameters
OklabFlensburg\UranusEvents\Service\ApiClientService
OklabFlensburg\UranusEvents\Service\EventService
OklabFlensburg\UranusEvents\Service\CacheService
OklabFlensburg\UranusEvents\Service\LoggingService
OklabFlensburg\UranusEvents\Utility\DateUtility
OklabFlensburg\UranusEvents\Utility\ArrayUtility
OklabFlensburg\UranusEvents\Utility\ValidationUtility
```

## Datei-Erstellungsreihenfolge

1. Grundlegende Extension-Dateien (`composer.json`, `ext_emconf.php`)
2. Konfigurationsdateien (`Services.yaml`, TypoScript)
3. Domain Models und DTOs
4. Service-Klassen
5. Controller
6. Templates und Partials
7. Sprachdateien
8. Assets (CSS/JS)
9. Tests
10. Dokumentation

## Besondere Dateien

### `Configuration/Services.yaml`
Definiert alle Services mit Dependency Injection. Beispiel:
```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false
    
  OklabFlensburg\UranusEvents\:
    resource: '../Classes/*'
    exclude: '../Classes/Domain/Model/*'
```

### `Configuration/TCA/Overrides/tt_content.php`
Registriert das Frontend-Plugin als Content Element:
```php
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'UranusEvents',
    'Events',
    'LLL:EXT:uranus_events/Resources/Private/Language/locallang_db.xlf:plugin.events.title'
);
```

### `ext_conf_template.txt`
Definiert konfigurierbare Einstellungen:
```plaintext
# cat=basic; type=string; label=API Base URL
apiBaseUrl = https://api.example.com

# cat=basic; type=int+; label=Cache Lifetime (seconds)
cacheLifetime = 3600

# cat=advanced; type=int+; label=HTTP Timeout (seconds)
httpTimeout = 30