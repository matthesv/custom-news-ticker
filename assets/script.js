jQuery(document).ready(function ($) {
    function fetchLatestNews() {
        var tickerContainer = $('.news-ticker-container');
        var category = tickerContainer.data('category'); // Holt die Kategorie aus dem HTML-Container

        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_news',
                category: category // Sende Kategorie-Filter
            },
            success: function (response) {
                tickerContainer.empty(); // Löscht die alten Einträge und lädt neue

                $.each(response, function(index, news) {
                    var imageHTML = news.image ? '<img src="'+news.image+'" width="50" alt="News Image">' : ''; // Prüft, ob ein Bild existiert
                    var entry = '<div class="news-ticker-entry">'+
                                    '<div class="news-ticker-dot"></div>'+
                                    '<div class="news-ticker-content">'+
                                        imageHTML+
                                        '<h4>'+news.title+'</h4>'+
                                        '<p>'+news.content+'</p>'+
                                        '<span class="news-ticker-time">'+news.time+'</span>'+
                                    '</div>'+
                                '</div>';
                    tickerContainer.append(entry);
                });
            },
            error: function(xhr, status, error) {
                console.error('News ticker AJAX error:', error);
            }
        });
    }

    fetchLatestNews(); // Direkt beim Seitenladen aufrufen
    setInterval(fetchLatestNews, 60000); // Danach alle 60 Sekunden wiederholen
});
