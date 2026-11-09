// Shell modul ORGMATE / E-PROGRESS / SENTRA — diekstrak dari inline <script>
// di layout modul (sidebar toggle, profil, notifikasi, tema).
// Kunci collapse organisasional dibaca dari data-storage-key pada #orgmateShell.
//
// Lucide dimuat lokal via npm/Vite (tidak lagi dari CDN unpkg) agar tetap
// berfungsi offline/ngrok. Inisialisasi ikon dilakukan di sini secara terpusat
// setelah DOM siap; `window.simoraIcons.refresh()` dapat dipanggil ulang
// setelah konten dinamis (chart/dialog/toast) dirender. Hanya ikon yang
// dipakai Blade modul yang dibundel; nama memakai kebab-case di Blade.
import { createIcons } from 'lucide';
import {
    ArrowDown, // arrow-down (ternary jenis transaksi)
    ArrowUp, // arrow-up (ternary jenis transaksi)
    ArrowLeftRight,
    ArrowRight,
    ArrowUpRight,
    Bell,
    BookOpen,
    Building2,
    Calculator,
    CalendarCheck,
    CalendarDays,
    CalendarPlus,
    ChartColumn,
    ChartPie,
    Check,
    ChevronDown,
    CircleAlert,
    CircleArrowDown,
    CircleArrowUp,
    CirclePlus,
    ClipboardList,
    Clock,
    Download,
    ExternalLink,
    FileChartColumn,
    FileCheck,
    FileText,
    Flag,
    Folder,
    FolderOpen,
    Gauge,
    GraduationCap,
    House,
    Image,
    Images,
    Info,
    Layers,
    LayoutDashboard,
    LayoutGrid,
    Lightbulb,
    List,
    ListChecks,
    LogOut,
    Megaphone,
    Menu,
    Moon,
    Network,
    PanelLeft,
    PenLine,
    Plus,
    Printer,
    QrCode,
    ReceiptText,
    Save,
    ScrollText,
    Search,
    SearchCheck,
    Sheet,
    SlidersHorizontal,
    Sun,
    Target,
    TrendingDown,
    TrendingUp,
    Upload,
    UserPlus,
    Users,
    Video,
    Wallet,
    X,
    Zap,
} from 'lucide';

const icons = {
    ArrowDown,
    ArrowUp,
    ArrowLeftRight,
    ArrowRight,
    ArrowUpRight,
    Bell,
    BookOpen,
    Building2,
    Calculator,
    CalendarCheck,
    CalendarDays,
    CalendarPlus,
    ChartColumn,
    ChartPie,
    Check,
    ChevronDown,
    CircleAlert,
    CircleArrowDown,
    CircleArrowUp,
    CirclePlus,
    ClipboardList,
    Clock,
    Download,
    ExternalLink,
    FileChartColumn,
    FileCheck,
    FileText,
    Flag,
    Folder,
    FolderOpen,
    Gauge,
    GraduationCap,
    House,
    Image,
    Images,
    Info,
    Layers,
    LayoutDashboard,
    LayoutGrid,
    Lightbulb,
    List,
    ListChecks,
    LogOut,
    Megaphone,
    Menu,
    Moon,
    Network,
    PanelLeft,
    PenLine,
    Plus,
    Printer,
    QrCode,
    ReceiptText,
    Save,
    ScrollText,
    Search,
    SearchCheck,
    Sheet,
    SlidersHorizontal,
    Sun,
    Target,
    TrendingDown,
    TrendingUp,
    Upload,
    UserPlus,
    Users,
    Video,
    Wallet,
    X,
    Zap,
};

function refreshIcons() {
    try {
        createIcons({ icons: icons, attrs: { 'aria-hidden': 'true' } });
    } catch (e) {
        // Ikon tidak boleh menggagalkan JS halaman. Menelan error di sini
        // supaya sidebar/toast/tema tetap berjalan walau satu ikon rusak.
    }
}

window.simoraIcons = { refresh: refreshIcons };

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', refreshIcons);
} else {
    refreshIcons();
}

// Tangani ikon yang muncul belakangan (mis. chart/dialog/toast dirender dinamis).
// PENTING: createIcons() mengganti <i data-lucide> menjadi <svg class="lucide">
// data-lucide>, yang memicu childList mutation. Observer lama memanggil
// refreshIcons() pada SETIAP mutasi sehingga berpotensi loop/lag.
// Perbaikan: (1) hanya refresh bila ada ikon BELUM dikonversi
// ([data-lucide]:not(svg)); (2) debounce 150ms agar batch mutasi tebal
// (mis. chart) hanya memicu satu pemanggilan createIcons.
function hasUnconvertedIcons() {
    return !!document.querySelector('[data-lucide]:not(svg)');
}

var iconRefreshTimer = null;
function scheduleIconRefresh() {
    if (iconRefreshTimer) return;
    iconRefreshTimer = setTimeout(function () {
        iconRefreshTimer = null;
        if (hasUnconvertedIcons()) refreshIcons();
    }, 150);
}

var iconObserver = new MutationObserver(function () {
    if (hasUnconvertedIcons()) scheduleIconRefresh();
});
if (document.body) {
    iconObserver.observe(document.body, { childList: true, subtree: true });
} else {
    document.addEventListener('DOMContentLoaded', function () {
        if (document.body) iconObserver.observe(document.body, { childList: true, subtree: true });
    });
}

(function () {
    var shell = document.getElementById('orgmateShell');
    if (!shell) return;
    var overlay = document.getElementById('orgmateOverlay');
    var toggle = document.getElementById('orgmateToggle');
    var closeBtn = document.getElementById('orgmateSidebarClose');
    var storageKey = shell.dataset.storageKey || 'module-collapsed';

    function isMobile() { return window.innerWidth <= 900; }
    function openMobile() { shell.classList.add('is-open'); if (overlay) overlay.hidden = false; }
    function closeMobile() { shell.classList.remove('is-open'); if (overlay) overlay.hidden = true; }

    if (toggle) toggle.addEventListener('click', function () {
        if (isMobile()) {
            shell.classList.contains('is-open') ? closeMobile() : openMobile();
        } else {
            shell.classList.toggle('is-collapsed');
            try { localStorage.setItem(storageKey, shell.classList.contains('is-collapsed') ? '1' : '0'); } catch (e) {}
        }
        refreshIcons();
    });
    if (closeBtn) closeBtn.addEventListener('click', closeMobile);
    if (overlay) overlay.addEventListener('click', closeMobile);

    try { if (!isMobile() && localStorage.getItem(storageKey) === '1') shell.classList.add('is-collapsed'); } catch (e) {}
    if (isMobile()) shell.classList.remove('is-collapsed');

    var profileBtn = document.getElementById('orgmateProfileBtn');
    var profileMenu = document.getElementById('orgmateProfileMenu');
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = profileMenu.hidden;
            profileMenu.hidden = !open;
            profileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function () {
            profileMenu.hidden = true;
            profileBtn.setAttribute('aria-expanded', 'false');
        });
    }

    var bell = document.getElementById('orgmateBell');
    var toast = document.getElementById('orgmateToast');
    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        toast.classList.add('is-show');
        setTimeout(function () { toast.classList.remove('is-show'); toast.hidden = true; }, 3200);
    }
    if (bell) bell.addEventListener('click', function () {
        showToast('Tidak ada notifikasi baru. Data ditampilkan dari database saat ini.');
    });
    window.orgmateToast = showToast;

    window.addEventListener('resize', function () { if (!isMobile()) closeMobile(); });
})();

// Toggle tema modul: pakai ikon Lucide moon/sun, bukan karakter emoji.
(function () {
    document.querySelectorAll('[data-theme-toggle]').forEach(function (toggle) {
        function render() {
            var isDark = document.body.classList.contains('dark-mode');
            toggle.innerHTML = isDark
                ? '<i data-lucide="sun" aria-hidden="true"></i>'
                : '<i data-lucide="moon" aria-hidden="true"></i>';
            toggle.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
            refreshIcons();
        }
        render();
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            try { localStorage.setItem('ft-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light'); } catch (e) {}
            render();
        });
    });
})();
