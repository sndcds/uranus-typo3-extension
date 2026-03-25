/**
 * Uranus Events JavaScript
 * Handles interactive features like load more, filtering, etc.
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initUranusEvents();
    });
    
    function initUranusEvents() {
        // Initialize load more functionality
        initLoadMore();
        
        // Initialize event item interactions
        initEventInteractions();
        
        // Initialize responsive behavior
        initResponsiveBehavior();
    }
    
    function initLoadMore() {
        const loadMoreButtons = document.querySelectorAll('.load-more-button');
        
        loadMoreButtons.forEach(button => {
            if (button.dataset.ueBound === '1') {
                return;
            }

            button.dataset.ueBound = '1';
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const button = e.currentTarget;
                const lastEventDateId = button.dataset.lastEventDateId;
                const lastEventStartAt = button.dataset.lastEventStartAt;
                const offset = parseInt(button.dataset.offset) || 0;
                
                loadMoreEvents(button, offset, lastEventDateId, lastEventStartAt);
            });
        });
    }
    
    function loadMoreEvents(button, offset, lastEventDateId, lastEventStartAt) {
        // Show loading state
        const originalText = button.textContent;
        button.textContent = 'Lädt...';
        button.disabled = true;
        
        // Create AJAX request
        const xhr = new XMLHttpRequest();
        const url = buildAjaxUrl(offset, lastEventDateId, lastEventStartAt);
        
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                handleLoadMoreResponse(xhr.responseText, button);
            } else {
                handleLoadMoreError(button, originalText);
            }
        };
        
        xhr.onerror = function() {
            handleLoadMoreError(button, originalText);
        };
        
        xhr.send();
    }
    
    function buildAjaxUrl(offset, lastEventDateId, lastEventStartAt) {
        const baseUrl = window.location.pathname;
        const params = new URLSearchParams(window.location.search);
        
        // Update pagination parameters
        params.set('offset', offset);
        
        if (lastEventDateId) {
            params.set('last_event_date_id', lastEventDateId);
        }
        
        if (lastEventStartAt) {
            params.set('last_event_start_at', lastEventStartAt);
        }
        
        // Add AJAX flag
        params.set('ajax', '1');
        params.set('type', '165432');
        
        return baseUrl + '?' + params.toString();
    }
    
    function handleLoadMoreResponse(responseHtml, button) {
        try {
            // Parse the response HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(responseHtml, 'text/html');
            
            // Extract events and pagination
            const newEvents = doc.querySelectorAll('.event-item');
            const newPagination = doc.querySelector('.events-pagination');
            
            if (newEvents.length > 0) {
                // Append new events
                const eventsList = document.querySelector('.events-list');
                newEvents.forEach(event => {
                    eventsList.appendChild(event);
                });
                
                // Update pagination
                const oldPagination = document.querySelector('.events-pagination');
                if (newPagination && oldPagination) {
                    oldPagination.replaceWith(newPagination);
                    initLoadMore();
                } else if (newPagination && !oldPagination) {
                    // Add pagination if it didn't exist before
                    const eventsContainer = document.querySelector('.uranus-events');
                    eventsContainer.appendChild(newPagination);
                    initLoadMore();
                }
                
                // Smooth scroll to first new event
                if (newEvents[0]) {
                    newEvents[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            } else {
                // No more events
                button.textContent = 'Keine weiteren Events';
                button.disabled = true;
            }
            
        } catch (error) {
            console.error('Error handling load more response:', error);
            handleLoadMoreError(button, 'Mehr Events laden');
        }
    }
    
    function handleLoadMoreError(button, originalText) {
        button.textContent = 'Fehler - Erneut versuchen';
        button.disabled = false;
        
        // Restore original text after 3 seconds
        setTimeout(() => {
            button.textContent = originalText;
        }, 3000);
    }
    
    function initEventInteractions() {
        // Add click handlers for event items
        const eventItems = document.querySelectorAll('.event-item');
        
        eventItems.forEach(item => {
            // Add hover effect
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
            
            // Make entire event item clickable (except for links)
            item.addEventListener('click', function(e) {
                // Don't trigger if user clicked on a link or button
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') {
                    return;
                }
                
                const eventLink = this.querySelector('.event-title a');
                if (eventLink) {
                    window.location.href = eventLink.href;
                }
            });
        });
    }
    
    function initResponsiveBehavior() {
        // Handle responsive image loading
        const eventImages = document.querySelectorAll('.event-image img');
        
        eventImages.forEach(img => {
            // Add loading="lazy" if not already present
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
            
            // Handle image load errors
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const placeholder = this.parentElement.querySelector('.event-image-placeholder') || 
                                   createImagePlaceholder(this.parentElement);
                placeholder.style.display = 'flex';
            });
        });
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(handleResize, 250);
        });
    }
    
    function createImagePlaceholder(container) {
        const placeholder = document.createElement('div');
        placeholder.className = 'event-image-placeholder';
        placeholder.innerHTML = '<span>Bild nicht verfügbar</span>';
        container.appendChild(placeholder);
        return placeholder;
    }
    
    function handleResize() {
        // Adjust layout for mobile/desktop
        const eventItems = document.querySelectorAll('.event-item');
        const isMobile = window.innerWidth < 768;
        
        eventItems.forEach(item => {
            if (isMobile) {
                item.style.gridTemplateColumns = '1fr';
            } else {
                item.style.gridTemplateColumns = '300px 1fr';
            }
        });
    }
    
    // Public API (if needed)
    window.UranusEvents = {
        refresh: function() {
            location.reload();
        },
        
        clearCache: function() {
            // This would typically call a backend endpoint
            console.log('Cache clear requested');
        },
        
        testConnection: function() {
            // This would test API connection
            console.log('Connection test requested');
        }
    };
})();
