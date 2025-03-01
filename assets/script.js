jQuery(document).ready(function ($) {
    function fetchLatestNews() {
        $.ajax({
            url: newsTickerAjax.ajax_url,
            type: 'POST',
            data: { action: 'fetch_news' },
            success: function (response) {
                let tickerContainer = $('.news-ticker-container');
                tickerContainer.empty();
                response.forEach(news => {
                    let entry = `<div class="news-ticker-entry">
                        <div class="news-ticker-dot"></div>
                        <div class="news-ticker-content">
                            <img src="${news.image}" width="50">
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

    setInterval(fetchLatestNews, 60000); // Alle 60 Sekunden abrufen
});
