jQuery(document).ready(function ($) {
    function fetchLatestNews() {
        let tickerContainer = $('.news-ticker-container');
        let category = tickerContainer.data('category'); // Holt die Kategorie aus dem HTML-Container

        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'fetch_news',
                category: category // Sende Kategorie-Filter
            },
            success: function (response) {
                tickerContainer.empty(); // Löscht die alten Einträge und lädt neue

                response.forEach(news => {
                    let entry = `
                        <div class="news-ticker-entry">
                            <div class="news-ticker-dot"></div>
                            <div class="news-ticker-content">
                                ${news.image ? `<img src="${news.image}" width="50">` : ''}
                                <h4>${news.title}</h4>
                                <p>${news.content}</p>
                                <span class="news-ticker-time">${news.time}</span>
                            </div>
                        </div>`;
                    tickerContainer.append(entry);
                });
            }
        });
    }

    // Starte AJAX-Update alle 60 Sekunden
    setInterval(fetchLatestNews, 60000);
});
