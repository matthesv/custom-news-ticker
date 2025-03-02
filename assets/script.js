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
        
        var html = '<div class="news-ticker-entry" data-news-id="'+news.ID+'">';
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
    
    // Lädt News via AJAX, unterscheidet zwischen 'refresh' und 'load_more'
    function loadNews(mode, offset) {
        var tickerContainer = $('.news-ticker-container');
        var category = tickerContainer.data('category') || '';
        var postsPerPage = tickerContainer.data('posts-per-page') || 5;
        var data = {
            action: 'fetch_news',
            category: category,
            posts_per_page: postsPerPage,
            offset: offset,
            mode: mode,
            nonce: newsTickerAjax.nonce
        };
        
        // Bei "Mehr Laden" IDs der bereits geladenen Beiträge mitgeben
        if (mode === 'load_more') {
            var excludeIDs = [];
            $('.news-ticker-entry').each(function() {
                var id = $(this).data('news-id');
                if (id) {
                    excludeIDs.push(id);
                }
            });
            data.exclude_ids = excludeIDs;
        }
        
        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: data,
            beforeSend: function() {
                showLoading(tickerContainer);
            },
            success: function (response) {
                tickerContainer.find('.news-ticker-loading').remove();
                if(response && response.news_items) {
                    if(mode === 'refresh') {
                        // Bei Auto-Refresh: Neue Einträge nur hinzufügen, wenn sie noch nicht vorhanden sind
                        $.each(response.news_items, function(index, news) {
                            if (tickerContainer.find('.news-ticker-entry[data-news-id="'+news.ID+'"]').length === 0) {
                                tickerContainer.prepend(renderNewsItem(news));
                            }
                        });
                    } else if(mode === 'load_more') {
                        // Beim "Mehr Laden" anhängen
                        tickerContainer.append(renderNewsItems(response.news_items));
                    }
                    // Aktualisiere den Offset sowohl intern als auch im DOM-Attribut
                    tickerContainer.data('offset', response.new_offset);
                    tickerContainer.attr('data-offset', response.new_offset);
                    
                    // Zeige oder verstecke den "Mehr Laden"-Button
                    if(response.has_more) {
                        $('#news-ticker-load-more').show();
                    } else {
                        $('#news-ticker-load-more').hide();
                    }
                } else {
                    if(mode === 'refresh') {
                        // Keine neuen News – nichts tun
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
    
    // Initialer Ladevorgang
    loadNews('refresh', 0);
    
    // Auto-Refresh alle 60 Sekunden
    var refreshInterval = setInterval(function() {
        loadNews('refresh', 0);
    }, 60000);
    
    // "Mehr Laden" Button
    $(document).on('click', '#news-ticker-load-more', function(e) {
        e.preventDefault();
        var currentOffset = $('.news-ticker-container').data('offset') || 0;
        loadNews('load_more', currentOffset);
    });
    
    // Tooltip-Logik: Bei Hover über die Zeitangabe wird das vollständige Datum angezeigt
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
