# Fluid-Templates und Frontend-Design

## Template-Struktur

### 1. Layout: `Resources/Private/Templates/Layouts/Default.html`
```html
<!DOCTYPE html>
<html lang="de" xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uranus Events</title>
    <link rel="stylesheet" href="{f:uri.resource(path: 'CSS/events.css')}">
</head>
<body>
    <div class="uranus-events-container">
        <f:render section="MainContent" />
    </div>
    
    <script src="{f:uri.resource(path: 'JavaScript/events.js')}" defer></script>
</body>
</html>
```

### 2. Haupt-Template: `Resources/Private/Templates/Event/List.html`
```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      data-namespace-typo3-fluid="true"
      lang="de">

<f:layout name="Default" />

<f:section name="MainContent">
    <div class="uranus-events">
        <header class="events-header">
            <h1>Events</h1>
            
            <f:if condition="{filter}">
                <f:render partial="Event/FilterInfo" arguments="{filter: filter}" />
            </f:if>
        </header>
        
        <f:if condition="{error}">
            <f:then>
                <div class="alert alert-error">
                    <p>{errorMessage}</p>
                    <p>Bitte versuchen Sie es später erneut.</p>
                </div>
            </f:then>
            <f:else>
                <f:if condition="{events}">
                    <f:then>
                        <div class="events-list">
                            <f:for each="{events}" as="event">
                                <f:render partial="Event/Item" arguments="{event: event}" />
                            </f:for>
                        </div>
                        
                        <f:if condition="{pagination.total} > {pagination.limit}">
                            <f:render partial="Event/Pagination" arguments="{pagination: pagination}" />
                        </f:if>
                    </f:then>
                    <f:else>
                        <div class="no-events">
                            <p>Keine Events gefunden.</p>
                        </div>
                    </f:else>
                </f:if>
            </f:else>
        </f:if>
    </div>
</f:section>

</html>
```

### 3. Partial: `Resources/Private/Templates/Partial/Event/Item.html`
```html
<article class="event-item" data-event-id="{event.id}">
    <div class="event-image">
        <f:if condition="{event.imagePath}">
            <f:then>
                <img src="{event.imagePath}" 
                     alt="{event.title}" 
                     loading="lazy"
                     width="300"
                     height="200">
            </f:then>
            <f:else>
                <div class="event-image-placeholder">
                    <span>Kein Bild</span>
                </div>
            </f:else>
        </f:if>
    </div>
    
    <div class="event-content">
        <header class="event-header">
            <h2 class="event-title">
                <f:link.external uri="https://example.com/event/{event.id}" target="_blank">
                    {event.title}
                </f:link.external>
            </h2>
            
            <f:if condition="{event.subtitle}">
                <p class="event-subtitle">{event.subtitle}</p>
            </f:if>
        </header>
        
        <div class="event-meta">
            <div class="event-date-time">
                <span class="event-date">
                    <f:format.date format="d.m.Y">{event.startDate}</f:format.date>
                </span>
                
                <f:if condition="{event.startTime}">
                    <span class="event-time">
                        <f:format.date format="H:i">{event.startTime}</f:format.date> Uhr
                    </span>
                </f:if>
                
                <f:if condition="{event.entryTime}">
                    <span class="event-entry-time">
                        (Einlass: <f:format.date format="H:i">{event.entryTime}</f:format.date>)
                    </span>
                </f:if>
            </div>
            
            <div class="event-venue">
                <h3 class="venue-name">{event.venueName}</h3>
                <address class="venue-address">
                    <f:if condition="{event.venueStreet}">
                        {event.venueStreet} {event.venueHouseNumber}<br>
                    </f:if>
                    {event.venuePostalCode} {event.venueCity}
                    <f:if condition="{event.venueState}">
                        , {event.venueState}
                    </f:if>
                    <br>
                    <f:if condition="{event.venueCountry} == 'DEU'">
                        Deutschland
                    </f:if>
                    <f:if condition="{event.venueCountry} == 'DNK'">
                        Dänemark
                    </f:if>
                    <f:if condition="{event.venueCountry} == 'AUT'">
                        Österreich
                    </f:if>
                </address>
                
                <f:if condition="{event.venueLat} && {event.venueLon}">
                    <a href="https://maps.google.com/?q={event.venueLat},{event.venueLon}" 
                       target="_blank" 
                       class="venue-map-link">
                        Auf Karte anzeigen
                    </a>
                </f:if>
            </div>
            
            <div class="event-organization">
                <span class="organization-name">{event.organizationName}</span>
            </div>
        </div>
        
        <div class="event-details">
            <f:if condition="{event.eventTypes}">
                <div class="event-types">
                    <strong>Kategorien:</strong>
                    <ul class="event-types-list">
                        <f:for each="{event.eventTypes}" as="eventType">
                            <li>{eventType.typeId} - {eventType.genreId}</li>
                        </f:for>
                    </ul>
                </div>
            </f:if>
            
            <f:if condition="{event.languages}">
                <div class="event-languages">
                    <strong>Sprachen:</strong>
                    <span>{event.languages -> f:count()} Sprachen</span>
                </div>
            </f:if>
            
            <f:if condition="{event.tags}">
                <div class="event-tags">
                    <strong>Tags:</strong>
                    <f:for each="{event.tags}" as="tag" iteration="tagIteration">
                        <span class="tag">{tag}</span>
                        <f:if condition="{tagIteration.isLast} == 0">, </f:if>
                    </f:for>
                </div>
            </f:if>
            
            <f:if condition="{event.minAge} || {event.maxAge}">
                <div class="event-age">
                    <strong>Altersbeschränkung:</strong>
                    <f:if condition="{event.minAge} && {event.maxAge}">
                        <span>{event.minAge} - {event.maxAge} Jahre</span>
                    </f:if>
                    <f:if condition="{event.minAge} && !{event.maxAge}">
                        <span>Ab {event.minAge} Jahren</span>
                    </f:if>
                    <f:if condition="{!event.minAge} && {event.maxAge}">
                        <span>Bis {event.maxAge} Jahre</span>
                    </f:if>
                </div>
            </f:if>
            
            <div class="event-status">
                <span class="status-badge status-{event.releaseStatus}">
                    {event.releaseStatus}
                </span>
            </div>
        </div>
    </div>
</article>
```

### 4. Partial: `Resources/Private/Templates/Partial/Event/Pagination.html`
```html
<nav class="events-pagination" aria-label="Event-Navigation">
    <ul class="pagination-list">
        <f:if condition="{pagination.offset} > 0">
            <li class="pagination-item pagination-prev">
                <f:link.action action="list" 
                              arguments="{offset: pagination.offset - pagination.limit}"
                              class="pagination-link">
                    ← Vorherige Seite
                </f:link.action>
            </li>
        </f:if>
        
        <li class="pagination-item pagination-info">
            <span>
                Seite {f:math(equation: '(offset / limit) + 1', 
                              a: pagination.offset, 
                              b: pagination.limit) -> f:format.number(decimals: 0)}
                von {f:math(equation: 'ceil(total / limit)', 
                           a: pagination.total, 
                           b: pagination.limit) -> f:format.number(decimals: 0)}
            </span>
        </li>
        
        <f:if condition="{pagination.offset + pagination.limit} < {pagination.total}">
            <li class="pagination-item pagination-next">
                <f:link.action action="list" 
                              arguments="{offset: pagination.offset + pagination.limit}"
                              class="pagination-link">
                    Nächste Seite →
                </f:link.action>
            </li>
        </f:if>
        
        <f:if condition="{pagination.lastEventDateId}">
            <li class="pagination-item pagination-load-more">
                <button class="load-more-button" 
                        data-last-event-date-id="{pagination.lastEventDateId}"
                        data-last-event-start-at="{pagination.lastEventStartAt -> f:format.date(format: 'c')}">
                    Mehr Events laden
                </button>
            </li>
        </f:if>
    </ul>
</nav>
```

### 5. Partial: `Resources/Private/Templates/Partial/Event/FilterInfo.html`
```html
<div class="filter-info">
    <h3>Aktive Filter</h3>
    <dl class="filter-list">
        <f:if condition="{filter.start}">
            <dt>Startdatum:</dt>
            <dd>{filter.start -> f:format.date(format: 'd.m.Y')}</dd>
        </f:if>
        
        <f:if condition="{filter.end}">
            <dt>Enddatum:</dt>
            <dd>{filter.end -> f:format.date(format: 'd.m.Y')}</dd>
        </f:if>
        
        <f:if condition="{filter.search}">
            <dt>Suchbegriff:</dt>
            <dd>{filter.search}</dd>
        </f:if>
        
        <f:if condition="{filter.city}">
            <dt>Stadt:</dt>
            <dd>{filter.city}</dd>
        </f:if>
        
        <f:if condition="{filter.countries}">
            <dt>Länder:</dt>
            <dd>
                <f:for each="{filter.countries}" as="country" iteration="countryIteration">
                    <f:if condition="{country} == 'DEU'">Deutschland</f:if>
                    <f:if condition="{country} == 'DNK'">Dänemark</f:if>
                    <f:if condition="{country} == 'AUT'">Österreich</f:if>
                    <f:if condition="{countryIteration.isLast} == 0">, </f:if>
                </f:for>
            </dd>
        </f:if>
        
        <f:if condition="{filter.language}">
            <dt>Sprache:</dt>
            <dd>{filter.language}</dd>
        </f:if>
    </dl>
</div>
```

## CSS-Stil: `Resources/Private/CSS/events.css`

```css
/* Uranus Events - Basis-Styling */
.uranus-events-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header */
.events-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.events-header h1 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 2rem;
}

/* Filter Info */
.filter-info {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.filter-info h3 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.1rem;
    color: #495057;
}

.filter-list {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 8px 15px;
    margin: 0;
}

.filter-list dt {
    font-weight: 600;
    color: #6c757d;
}

.filter-list dd {
    margin: 0;
    color: #212529;
}

/* Event List */
.events-list {
    display: grid;
    gap: 25px;
    margin-bottom: 30px;
}

/* Event Item */
.event-item {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 25px;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #fff;
    transition: box-shadow 0.2s ease;
}

.event-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.event-image {
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

.event-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.event-image-placeholder {
    width: 100%;
    height: 200px;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-style: italic;
}

/* Event Content */
.event-content {
    display: flex;
    flex-direction: column;
}

.event-header {
    margin-bottom: 15px;
}

.event-title {
    margin: 0 0 5px 0;
    font-size: 1.5rem;
    color: #007bff;
}

.event-title a {
    color: inherit;
    text-decoration: none;
}

.event-title a:hover {
    text-decoration: underline;
}

.event-subtitle {
    margin: 0;
    color: #6c757d;
    font-style: italic;
}

/* Event Meta */
.event-meta {
    margin-bottom: 15px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 4px;
}

.event-date-time {
    margin-bottom: 10px;
    font-weight: 600;
    color: #495057;
}

.event-date,
.event-time,
.event-entry-time {
    margin-right: 10px;
}

.event-venue {
    margin-bottom: 10px;
}

.venue-name {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: #333;
}

.venue-address {
    margin: 0 0 8px 0;
    font-style: normal;
    color: #6c757d;
    line-height: 1.4;
}

.venue-map-link {
    display: inline-block;
    color: #007bff;
    text-decoration: none;
    font-size: 0.9rem;
}

.venue-map-link:hover {
    text-decoration: underline;
}

.event-organization {
    font-size: 0.9rem;
    color: #6c757d;
}

.organization-name {
    font-weight: 600;
}

/* Event Details */
.event-details {
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.event-details > div {
    margin-bottom: 8px;
}

.event-types-list {
    display: inline;
    margin: 0;
    padding: 0;
    list-style: none;
}

.event-types-list li {
    display: inline;
    margin-right: 8px;
    padding: 2px 8px;
    background-color: #e9ecef;
    border-radius: 12px;
    font-size: 0.8rem;
}

.event-tags .tag {
    display: inline-block;
    margin-right: 5px;
    padding: 2px 8px;
    background-color: #e7f1ff;
    color: #0056b3;
    border-radius: 12px;
    font-size: 0.8rem;
}

.event-age {
    color: #dc3545;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-released {
    background-color: #d4edda;
    color: #155724;
}

.status-draft {
    background-color: #fff3cd;
    color: #856404;
}

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

/* No Events */
.no-events {
    text