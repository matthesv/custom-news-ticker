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
        container.html('<div class="news-ticker-loading">Lade Nachrichten...</div>');
    }
    
    function fetchLatestNews() {
        var tickerContainer = $('.news-ticker-container');
        var category = tickerContainer.data('category'); // Holt die Kategorie aus dem HTML-Container
        
        // Zeige Loading-Indikator beim ersten Laden
        if (tickerContainer.find('.news-ticker-entry').length === 0) {
            showLoading(tickerContainer);
        }

        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_news',
                category: category, // Sende Kategorie-Filter
                nonce: newsTickerAjax.nonce // Sicherheits-Nonce
            },
            success: function (response) {
                tickerContainer.empty(); // Löscht die alten Einträge und lädt neue

                if(response && response.length) {
                    $.each(response, function(index, news) {
                        var imageHTML = news.image ? '<img src="'+news.image+'" alt="News Image">' : '';
                        var dotColor = news.border_color ? news.border_color : newsTickerAjax.border_color;
                        var rgb = hexToRgb(dotColor);
                        var rgbaPulse = "rgba(" + rgb.r + ", " + rgb.g + ", " + rgb.b + ", 0.4)";
                        var rgbaTransparent = "rgba(" + rgb.r + ", " + rgb.g + ", " + rgb.b + ", 0)";
                        var dotStyle = "background-color:" + dotColor + "; --dot-color:" + dotColor + "; --dot-color-pulse:" + rgbaPulse + "; --dot-color-pulse-transparent:" + rgbaTransparent + ";";
                        
                        var entry = '<div class="news-ticker-entry">'+
                                        '<div class="news-ticker-dot" style="' + dotStyle + '"></div>'+
                                        '<div class="news-ticker-content">'+
                                            imageHTML+
                                            '<h4>'+news.title+'</h4>'+
                                            '<p>'+news.content+'</p>'+
                                            '<span class="news-ticker-time">'+news.time+'</span>'+
                                        '</div>'+
                                    '</div>';
                        tickerContainer.append(entry);
                    });
                    
                    // Animation für neue Einträge
                    setTimeout(function() {
                        $('.news-ticker-entry').addClass('loaded');
                    }, 100);
                    
                } else {
                    tickerContainer.append('<p>Keine News verfügbar.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('News ticker AJAX error:', error);
                tickerContainer.append('<p>Fehler beim Laden der Nachrichten.</p>');
            }
        });
    }

    // Ticker initial laden
    fetchLatestNews();
    
    // Auto-Refresh alle 60 Sekunden
    var refreshInterval = setInterval(fetchLatestNews, 60000);
    
    // Aufräumen bei Seitenverlassen
    $(window).on('beforeunload', function() {
        clearInterval(refreshInterval);
    });
    
    // Manuelles Refresh durch Klicken auf den Ticker (optional)
    $('.news-ticker-container').on('click', '.refresh-ticker', function(e) {
        e.preventDefault();
        fetchLatestNews();
    });
});
