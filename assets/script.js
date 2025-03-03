jQuery(document).ready(function ($) {
    // Hilfsfunktion: Konvertiert Hex-Farbe in RGB
    function hexToRgb(hex) {
        hex = hex.replace(/^#/, '');
        if (hex.length === 3) {
            hex = hex.split('').map(function(hexChar) {
                return hexChar + hexChar;
            }).join('');
        }
        var bigint = parseInt(hex, 16);
        var r = (bigint >> 16) & 255;
        var g = (bigint >> 8) & 255;
        var b = bigint & 255;
        return {r: r, g: g, b: b};
    }
    
    // Loading-Indikator hinzufügen
    function showLoading(container) {
        container.find('.news-ticker-loading').remove();
        container.append('<div class="news-ticker-loading">Lade Nachrichten...</div>');
    }
    
    // Rendert einen einzelnen News-Eintrag
    function renderNewsItem(news) {
        var imageHTML = news.image ? '<img src="'+news.image+'" alt="News Image">' : '';
        var dotColor = news.border_color ? news.border_color : newsTickerAjax.border_color;
        var rgb = hexToRgb(dotColor);
        var rgbaPulse = "rgba(" + rgb.r + ", " + rgb.g + ", " + rgb.b + ", 0.4)";
        var rgbaTransparent = "rgba(" + rgb.r + ", " + rgb.g + ", " + rgb.b + ", 0)";
        var dotStyle = "background-color:" + dotColor + "; --dot-color:" + dotColor + "; --dot-color-pulse:" + rgbaPulse + "; --dot-color-pulse-transparent:" + rgbaTransparent + ";";
        
        var html = '<div class="news-ticker-entry" data-news-id="'+news.ID+'" data-timestamp="'+news.timestamp+'">';
        html += '<div class="news-ticker-dot" style="' + dotStyle + '"></div>';
        html += '<div class="news-ticker-content">';
        html += imageHTML;
        html += '<h4>'+news.title+'</h4>';
        html += '<p>'+news.content+'</p>';
        html += '<span class="news-ticker-time" data-full-date="'+news.full_date+'">'+news.time+'</span>';
        html += '</div></div>';
        return html;
    }
    
    // Rendert mehrere News-Einträge
    function renderNewsItems(newsItems) {
        var html = '';
        $.each(newsItems, function(index, news) {
            html += renderNewsItem(news);
        });
        return html;
    }
    
    // Lädt News via AJAX; unterscheidet zwischen 'refresh' und 'load_more'
    function loadNews(mode, lastTimestamp) {
        var tickerContainer = $('.news-ticker-container');
        var category = tickerContainer.data('category') || '';
        var postsPerPage = tickerContainer.data('posts-per-page') || 5;
        var ajaxData = {
            action: 'fetch_news',
            category: category,
            posts_per_page: postsPerPage,
            mode: mode,
            nonce: newsTickerAjax.nonce
        };

        if(mode === 'load_more') {
            ajaxData.last_timestamp = lastTimestamp;
        } else {
            ajaxData.offset = 0; // für den Refresh-Modus
        }

        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: ajaxData,
            beforeSend: function() {
                showLoading(tickerContainer);
            },
            success: function (response) {
                tickerContainer.find('.news-ticker-loading').remove();
                if(response && response.news_items) {
                    if(mode === 'refresh') {
                        // Beim Auto-Refresh: Neue Beiträge vorne einfügen
                        $.each(response.news_items, function(index, news) {
                            if (tickerContainer.find('.news-ticker-entry[data-news-id="'+news.ID+'"]').length === 0) {
                                tickerContainer.prepend(renderNewsItem(news));
                            }
                        });
                    } else if(mode === 'load_more') {
                        // Beim "Mehr Laden" anhängen und letzten Zeitstempel aktualisieren
                        tickerContainer.append(renderNewsItems(response.news_items));
                        tickerContainer.data('last-timestamp', response.new_last_timestamp);
                    }
                    // "Mehr Laden"-Button anzeigen oder verbergen
                    if(response.has_more) {
                        $('#news-ticker-load-more').show();
                    } else {
                        $('#news-ticker-load-more').hide();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('News ticker AJAX error:', error);
                tickerContainer.find('.news-ticker-loading').remove();
                tickerContainer.append('<p>Fehler beim Laden der Nachrichten.</p>');
            }
        });
    }
    
    // Initialer Ladevorgang (Refresh)
    loadNews('refresh', 0);
    
    // Auto-Refresh alle 60 Sekunden
    var refreshInterval = setInterval(function() {
        loadNews('refresh', 0);
    }, 60000);
    
    // "Mehr Laden"-Button: Nutzt den letzten Zeitstempel aus dem Container
    $(document).on('click', '#news-ticker-load-more', function(e) {
        e.preventDefault();
        var tickerContainer = $('.news-ticker-container');
        var lastTimestamp = tickerContainer.data('last-timestamp') || 0;
        loadNews('load_more', lastTimestamp);
    });
    
    // Tooltip-Logik
    $(document).on('mouseenter', '.news-ticker-time', function() {
        var fullDate = $(this).data('full-date');
        if (fullDate) {
            var tooltip = $('<div class="news-ticker-tooltip"></div>').text(fullDate);
            $('body').append(tooltip);
            tooltip.css({
                top: $(this).offset().top - tooltip.outerHeight() - 10,
                left: $(this).offset().left,
                display: 'none'
            }).fadeIn(200);
            $(this).data('tooltip', tooltip);
        }
    }).on('mouseleave', '.news-ticker-time', function() {
        var tooltip = $(this).data('tooltip');
        if (tooltip) {
            tooltip.fadeOut(200, function() {
                $(this).remove();
            });
            $(this).removeData('tooltip');
        }
    });
    
    // Aufräumen bei Seitenverlassen
    $(window).on('beforeunload', function() {
        clearInterval(refreshInterval);
    });
});
