// Jam real-time (WIB) — memperbarui semua elemen [data-live-clock] dan [data-live-date] setiap detik.
(function () {
    const clockEls = [...document.querySelectorAll('[data-live-clock]')];
    const dateEls = [...document.querySelectorAll('[data-live-date]')];
    if (clockEls.length === 0 && dateEls.length === 0) return;

    const timeFormat = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
    const dateFormat = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    function update() {
        const now = new Date();
        clockEls.forEach((el) => {
            el.textContent = timeFormat.format(now) + ' WIB';
        });
        dateEls.forEach((el) => {
            el.textContent = dateFormat.format(now);
        });
    }

    update();
    setInterval(update, 1000);
})();
