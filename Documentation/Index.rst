Uranus Events Extension
==========================

.. contents:: Table of Contents
   :depth: 3
   :local:

Introduction
------------

The Uranus Events extension for TYPO3 14.1 displays events from the Uranus Public API in the frontend.
It provides a flexible plugin with configurable filters, caching, and modern TYPO3 standards.

Features
--------

* **API Integration**: Fetches events from Uranus API with configurable base URL
* **Flexible Filtering**: Supports all API filter parameters (date ranges, categories, venues, etc.)
* **Pagination**: Supports both standard offset/limit and Uranus-specific pagination
* **Caching**: Tag-based caching to reduce API calls
* **Error Handling**: Graceful error handling with logging
* **Responsive Design**: Modern CSS with responsive layout
* **AJAX Loading**: Load more events without page reload
* **Multiple Templates**: Default, compact, and detailed templates
* **Extensible**: Clean architecture with dependency injection

Requirements
------------

* TYPO3 14.1 LTS
* PHP 8.1 or higher
* Guzzle HTTP Client (installed via composer)
* TYPO3 Extension Manager

Installation
------------

1. **Install via Composer**::

    composer require oklabflensburg/uranus-events

2. **Activate the extension** in the TYPO3 Extension Manager

3. **Clear all caches** in the TYPO3 Install Tool

Configuration
-------------

Extension Configuration
~~~~~~~~~~~~~~~~~~~~~~~

Configure the extension in **Extension Manager** → **Uranus Events**:

* **API Base URL**: Base URL of the Uranus API (e.g., ``https://api.example.com``)
* **API Endpoint**: Endpoint for events (default: ``/api/events``)
* **API Timeout**: Request timeout in seconds (default: 10)
* **Max Retries**: Number of retry attempts for failed requests (default: 3)
* **Default Cache Lifetime**: Cache lifetime in seconds (default: 3600)
* **Enable Logging**: Enable/disable logging of API calls
* **Log Level**: Log level for API logging

TypoScript Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

The extension provides TypoScript constants and setup. Include them in your template::

    @import 'EXT:uranus_events/Configuration/TypoScript/constants.typoscript'
    @import 'EXT:uranus_events/Configuration/TypoScript/setup.typoscript'

Plugin Configuration
~~~~~~~~~~~~~~~~~~~~

Add the **"Uranus Events"** content element to a page. Configure filters in the plugin settings:

**Filter Settings**:

* **Start date**: Filter events starting from this date (YYYY-MM-DD)
* **End date**: Filter events until this date (YYYY-MM-DD)
* **Search term**: Search in event titles and subtitles
* **Categories**: Comma-separated category IDs
* **Organizations**: Comma-separated organization IDs
* **Venues**: Comma-separated venue IDs
* **City**: Filter by city name
* **Countries**: Comma-separated ISO country codes (DEU, DNK, etc.)
* **Language**: Filter by language code
* **Limit**: Number of events per page
* **Offset**: Offset for pagination
* **Event types**: JSON array of event types
* **Include past events**: Include events that have already passed

**Display Settings**:

* **Show images**: Display event images
* **Show organization**: Display organization name
* **Show categories**: Display event categories
* **Show tags**: Display event tags
* **Show age restriction**: Display age restriction
* **Template**: Choose template (Default, Compact, Detailed)

**Cache Settings**:

* **Cache lifetime**: Override default cache lifetime
* **Disable cache**: Disable caching for this plugin

Usage
-----

Frontend Output
~~~~~~~~~~~~~~~

The plugin displays events in a responsive grid layout with:

* Event title and subtitle
* Date and time
* Venue information (name, address, city)
* Organization name
* Event image (if available)
* Categories and tags
* Age restriction
* Status

Pagination
~~~~~~~~~~

The plugin supports two pagination methods:

1. **Standard pagination**: Using ``limit`` and ``offset`` parameters
2. **Uranus pagination**: Using ``last_event_date_id`` and ``last_event_start_at``

The "Load more" button uses AJAX to load additional events without page reload.

Caching
~~~~~~~

Events are cached based on filter parameters. Cache tags are used for invalidation.
The cache lifetime can be configured per plugin or globally.

To clear the cache manually::

    # Clear all Uranus events cache
    ./typo3/sysext/core/bin/typo3 cache:flush --tags uranus_events

Error Handling
~~~~~~~~~~~~~~

If the API is unreachable or returns an error:

* Frontend shows a user-friendly message
* Empty event list is displayed
* Errors are logged to the TYPO3 log
* No PHP fatal errors occur

Development
-----------

Architecture
~~~~~~~~~~~~

The extension follows a clean architecture:

* **Controller**: ``EventController`` handles frontend requests
* **Services**: ``ApiClientService``, ``EventService`` handle business logic
* **Domain Models**: ``Event``, ``EventType``, ``EventResponse`` represent data
* **DTOs**: ``FilterParameters`` for filter configuration
* **Templates**: Fluid templates with partials and layouts

Adding Custom Templates
~~~~~~~~~~~~~~~~~~~~~~~

1. Create a new template in ``Resources/Private/Templates/Event/``
2. Add template option to FlexForm in ``Configuration/FlexForms/Events.xml``
3. Update TypoScript to include the new template

Extending Filter Parameters
~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Add field to ``FilterParameters`` DTO
2. Update ``EventService::applyFilters()``
3. Add FlexForm field in ``Configuration/FlexForms/Events.xml``
4. Update TypoScript configuration

API Reference
-------------

The extension uses the Uranus Public API endpoint::

    GET /api/events

Supported query parameters:

* ``start``: Start date (YYYY-MM-DD)
* ``end``: End date (YYYY-MM-DD)
* ``search``: Search term
* ``categories``: Comma-separated category IDs
* ``organizations``: Comma-separated organization IDs
* ``venues``: Comma-separated venue IDs
* ``city``: City name
* ``countries``: Comma-separated ISO country codes
* ``language``: Language code
* ``limit``: Number of events (default: 10)
* ``offset``: Offset for pagination
* ``last_event_date_id``: For pagination
* ``last_event_start_at``: For pagination
* ``event_types``: JSON array of [type_id, genre_id]
* ``past``: Include past events (true/false)

Example API Response
~~~~~~~~~~~~~~~~~~~~

.. code-block:: json

    {
      "events": [
        {
          "event_date_id": 550,
          "id": 470,
          "title": "Event Title",
          "subtitle": null,
          "start_date": "2026-03-28",
          "start_time": "21:00",
          "entry_time": "20:00",
          "venue_id": 52,
          "venue_name": "Venue Name",
          "venue_city": "City",
          "venue_street": "Street",
          "venue_house_number": "30",
          "venue_postal_code": "24937",
          "venue_country": "DEU",
          "venue_lat": 54.805848,
          "venue_lon": 9.453062,
          "image_id": 386,
          "image_path": "http://localhost:9090/api/image/386",
          "organization_id": 9,
          "organization_name": "Organization Name",
          "event_types": [
            { "type_id": 53, "genre_id": 0 },
            { "type_id": 44, "genre_id": 0 }
          ],
          "languages": null,
          "tags": null,
          "min_age": null,
          "max_age": null,
          "release_status": "released"
        }
      ],
      "last_event_date_id": 3119,
      "last_event_start_at": "2026-05-04T15:00"
    }

Troubleshooting
---------------

API Not Reachable
~~~~~~~~~~~~~~~~~

1. Check API Base URL in extension configuration
2. Verify network connectivity
3. Check API timeout settings
4. Review TYPO3 log for errors

No Events Displayed
~~~~~~~~~~~~~~~~~~~

1. Check filter settings in plugin
2. Verify API response contains events
3. Check cache settings (try disabling cache)
4. Review browser console for JavaScript errors

Cache Not Working
~~~~~~~~~~~~~~~~~

1. Check cache lifetime settings
2. Verify cache tags are being set
3. Check TYPO3 cache configuration
4. Try clearing all caches

Performance Issues
~~~~~~~~~~~~~~~~~~

1. Reduce cache lifetime
2. Increase API timeout
3. Limit number of events per page
4. Enable compression in TypoScript

Changelog
---------

Version 1.0.0 (2026-03-19)
~~~~~~~~~~~~~~~~~~~~~~~~~~

* Initial release for TYPO3 14.1
* Complete API integration with Uranus Public API
* Flexible filter configuration
* Tag-based caching
* Responsive frontend with AJAX loading
* Multiple template options
* Comprehensive error handling and logging

License
-------

This extension is licensed under GNU General Public License v2 or later.

See `LICENSE.txt` for details.

Support
-------

* Documentation: This file
* Issue tracker: GitHub repository
* Email: support@oklab-flensburg.de

Credits
-------

* Developed by Oklab Flensburg
* Uranus API by Uranus Project
* TYPO3 CMS by the TYPO3 Community