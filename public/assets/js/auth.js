const passwordInput = document.querySelector('#password');
const passwordToggle = document.querySelector('[data-password-toggle]');

passwordToggle?.addEventListener('click', () => {
    const showPassword = passwordInput?.type === 'password';

    if (passwordInput) {
        passwordInput.type = showPassword ? 'text' : 'password';
    }

    passwordToggle.classList.toggle('is-visible', showPassword);
    passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
    passwordToggle.setAttribute('aria-pressed', String(showPassword));
});

// Play Background: tampilkan video sebagai latar login, kembali ke gambar semula saat selesai
const backgroundVideo = document.querySelector('#loginBackgroundVideo');
const playBackgroundBtn = document.querySelector('#playBackgroundBtn');

function stopBackgroundVideo() {
    if (!backgroundVideo) return;
    backgroundVideo.pause();
    backgroundVideo.currentTime = 0;
    backgroundVideo.hidden = true;
    if (playBackgroundBtn) {
        playBackgroundBtn.disabled = false;
        playBackgroundBtn.querySelector('span')?.replaceChildren(document.createTextNode('Play Background'));
    }
}

playBackgroundBtn?.addEventListener('click', async () => {
    if (!backgroundVideo) return;
    backgroundVideo.currentTime = 0;
    backgroundVideo.hidden = false;
    playBackgroundBtn.disabled = true;
    playBackgroundBtn.querySelector('span')?.replaceChildren(document.createTextNode('Memutar...'));
    try {
        await backgroundVideo.play();
    } catch {
        stopBackgroundVideo();
    }
});

backgroundVideo?.addEventListener('ended', stopBackgroundVideo);
backgroundVideo?.addEventListener('error', stopBackgroundVideo);
backgroundVideo?.addEventListener('click', stopBackgroundVideo);
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && backgroundVideo && !backgroundVideo.hidden) {
        stopBackgroundVideo();
    }
});
