# Beispiel-Konfigurationen für Uranus Events Extension

## Sprechende Detail-URLs (RouteEnhancer)

Die Detailansicht kann direkt mit Query-Parametern aufgerufen werden:

```
?uranus_event_id=12&uranus_event_date_id=213
```

Für sprechende URLs kann in der Site-Konfiguration folgender RouteEnhancer ergänzt werden:

```yaml
routeEnhancers:
    UranusEventsDetail:
        type: Simple
        limitToPages: [42]
        routePath: '/events/{eventId}/{dateId}'
        defaults:
            eventId: '0'
            dateId: '0'
        requirements:
            eventId: '[0-9]+'
            dateId: '[0-9]+'
        _arguments:
            eventId: 'uranus_event_id'
            dateId: 'uranus_event_date_id'
```

Hinweise:
- `limitToPages` auf die UID der Seite mit dem Uranus-Events-Plugin setzen.
- Nach der Anpassung den TYPO3-Cache leeren.
- Ergebnis-Beispiel: `/events/12/213`.

## 1. Basis-Konfiguration (Alle Events anzeigen)

**Plugin-Einstellungen:**
- **Limit**: 10
- **Template**: Default
- **Cache Lifetime**: 3600

**TypoScript Setup:**
```typoscript
plugin.tx_uranusevents_events {
    view {
        templateRootPaths.10 = EXT:uranus_events/Resources/Private/Templates/
        partialRootPaths.10 = EXT:uranus_events/Resources/Private/Partials/
        layoutRootPaths.10 = EXT:uranus_events/Resources/Private/Layouts/
    }
    
    settings {
        apiBaseUrl = https://api.uranus.example.com
        cacheLifetime = 3600
    }
}
```

## 2. Events für eine bestimmte Stadt (Flensburg)

**Plugin-Einstellungen:**
- **City**: Flensburg
- **Limit**: 20
- **Show Images**: Ja
- **Show Organization**: Ja

**Filter-Parameter:**
```
city=Flensburg&limit=20
```

**Erwartetes Ergebnis:** Zeigt alle Events in Flensburg mit Bildern und Organisationsnamen.

## 3. Events mit Kategorie-Filter

**Plugin-Einstellungen:**
- **Categories**: 1,5,7
- **Start Date**: 2026-04-01
- **End Date**: 2026-04-30
- **Template**: Compact

**Filter-Parameter:**
```
categories=1,5,7&start=2026-04-01&end=2026-04-30
```

**Erwartetes Ergebnis:** Zeigt Events der Kategorien 1, 5 und 7 im April 2026 im kompakten Layout.

## 4. Events mit Sprachfilter und Pagination

**Plugin-Einstellungen:**
- **Language**: de
- **Limit**: 5
- **Offset**: 0
- **Show Categories**: Ja
- **Show Tags**: Ja

**Filter-Parameter:**
```
language=de&limit=5&offset=0
```

**TypoScript für Pagination:**
```typoscript
plugin.tx_uranusevents_events {
    settings {
        pagination {
            itemsPerPage = 5
            insertAbove = 1
            insertBelow = 1
            maximumNumberOfLinks = 10
        }
    }
}
```

## 5. Events mit Radius-Suche (geografisch)

**Plugin-Einstellungen:**
- **Latitude**: 54.7833
- **Longitude**: 9.4333
- **Radius**: 10
- **Limit**: 15
- **Template**: Detailed

**Filter-Parameter:**
```
lat=54.7833&lon=9.4333&radius=10&limit=15
```

**Erwartetes Ergebnis:** Zeigt Events innerhalb von 10km um Flensburg (54.7833, 9.4333) im detaillierten Layout.

## 6. Events mit Altersbeschränkung

**Plugin-Einstellungen:**
- **Min Age**: 18
- **Show Age Restriction**: Ja
- **Limit**: 10
- **Cache Lifetime**: 1800

**Filter-Parameter:**
```
min_age=18
```

**Erwartetes Ergebnis:** Zeigt Events ab 18 Jahren mit Altersangabe.

## 7. Events mit Multiple-Filter (komplex)

**Plugin-Einstellungen:**
- **Start Date**: 2026-03-01
- **End Date**: 2026-03-31
- **City**: Flensburg
- **Countries**: DEU
- **Categories**: 2,3
- **Search**: Konzert
- **Limit**: 25
- **Show All Fields**: Ja
- **Template**: Detailed

**Filter-Parameter:**
```
start=2026-03-01&end=2026-03-31&city=Flensburg&countries=DEU&categories=2,3&search=Konzert&limit=25
```

**Erwartetes Ergebnis:** Zeigt Konzerte in Flensburg, Deutschland im März 2026 mit allen Details.

## 8. Events mit Event-Types Filter

**Plugin-Einstellungen:**
- **Event Types**: [[53,0],[44,0]]
- **Limit**: 10
- **Show Images**: Ja

**Filter-Parameter:**
```
event_types=[[53,0],[44,0]]
```

**JSON-Format für Event Types:**
```json
[
    [53, 0],
    [44, 0]
]
```

## 9. Vergangene Events anzeigen

**Plugin-Einstellungen:**
- **Include Past Events**: Ja
- **Limit**: 20
- **Template**: Compact

**Filter-Parameter:**
```
past=true&limit=20
```

**Erwartetes Ergebnis:** Zeigt vergangene Events im kompakten Layout.

## 10. Events mit Organisations-Filter

**Plugin-Einstellungen:**
- **Organizations**: 9,12,15
- **Show Organization**: Ja
- **Limit**: 10

**Filter-Parameter:**
```
organizations=9,12,15
```

**Erwartetes Ergebnis:** Zeigt Events der Organisationen 9, 12 und 15.

## TypoScript Beispiele

### 1. Custom Template Paths
```typoscript
plugin.tx_uranusevents_events {
    view {
        templateRootPaths {
            0 = EXT:uranus_events/Resources/Private/Templates/
            10 = EXT:my_site/Resources/Private/Templates/UranusEvents/
        }
        partialRootPaths {
            0 = EXT:uranus_events/Resources/Private/Partials/
            10 = EXT:my_site/Resources/Private/Partials/UranusEvents/
        }
    }
}
```

### 2. Custom CSS/JS Einbindung
```typoscript
page.includeCSS {
    uranusEvents = EXT:uranus_events/Resources/Public/CSS/events.css
    uranusEventsCustom = EXT:my_site/Resources/Public/CSS/uranus-events.css
}

page.includeJSFooter {
    uranusEvents = EXT:uranus_events/Resources/Public/JavaScript/events.js
}
```

### 3. Cache-Konfiguration
```typoscript
plugin.tx_uranusevents_events {
    settings {
        cache {
            enabled = 1
            lifetime = 7200
            tags = uranus_events
        }
    }
    
    features {
        requireCHashArgumentForActionArguments = 0
    }
}
```

### 4. AJAX-Pagination Konfiguration
```typoscript
plugin.tx_uranusevents_events {
    settings {
        ajax {
            enabled = 1
            containerSelector = .events-list
            buttonSelector = .load-more-button
            loadingText = Loading...
        }
    }
}
```

## Frontend Integration Beispiele

### 1. Inline CSS Overrides
```html
<style>
    .uranus-events {
        --primary-color: #4A6FA5;
        --secondary-color: #FFD166;
    }
    
    .event-item {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
```

### 2. Custom JavaScript Events
```javascript
document.addEventListener('uranus:events:loaded', function(e) {
    console.log('Events loaded:', e.detail.count);
    
    // Custom animations
    gsap.from('.event-item', {
        duration: 0.6,
        opacity: 0,
        y: 20,
        stagger: 0.1
    });
});

document.addEventListener('uranus:events:loadmore', function(e) {
    console.log('Loading more events...');
});
```

### 3. Filter-Formular Integration
```html
<form class="uranus-events-filter" data-ajax-url="/api/events/filter">
    <input type="date" name="start" placeholder="Start date">
    <input type="date" name="end" placeholder="End date">
    <input type="text" name="search" placeholder="Search...">
    <button type="submit">Filter</button>
</form>

<script>
    document.querySelector('.uranus-events-filter').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // AJAX request to update events
        fetch(this.dataset.ajaxUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              // Update events list
              document.querySelector('.events-list').innerHTML = data.html;
          });
    });
</script>
```

## Troubleshooting Beispiele

### 1. Keine Events werden angezeigt
**Problem:** API gibt leere Antwort zurück
**Lösung:**
- API Base URL prüfen
- Filter-Parameter überprüfen
- Cache deaktivieren zum Testen
- TYPO3 Log auf Fehler prüfen

### 2. Bilder werden nicht geladen
**Problem:** Image-Pfade sind nicht erreichbar
**Lösung:**
- CORS-Einstellungen prüfen
- Proxy-Konfiguration für Bilder
- Placeholder für fehlende Bilder verwenden

### 3. Pagination funktioniert nicht
**Problem:** AJAX-Loading fehlgeschlagen
**Lösung:**
- JavaScript-Fehler in Konsole prüfen
- CORS-Einstellungen für AJAX
- CHash-Argument in TypoScript konfigurieren

## Best Practices

1. **Caching:** Immer Cache für Produktion aktivieren
2. **Limit:** Maximal 50 Events pro Seite für Performance
3. **Error Handling:** Graceful Degradation bei API-Fehlern
4. **Responsive Design:** Mobile-first Ansatz
5. **Accessibility:** Semantisches HTML und ARIA-Labels
6. **Security:** Input-Validierung und Output-Escaping
7. **Performance:** Lazy Loading für Bilder
8. **SEO:** Meta-Tags für Events
9. **Analytics:** Event-Tracking für Klicks
10. **Maintenance:** Regelmäßige Cache-Clearing