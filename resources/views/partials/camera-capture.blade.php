<dialog class="camera-dialog" id="cameraDialog" aria-labelledby="cameraTitle">
    <form method="dialog" class="orgmate-dialog-close-form" value="close">
        <button type="submit" class="orgmate-icon-btn orgmate-dialog-close" aria-label="Tutup">✕</button>
    </form>
    <h2 class="orgmate-dialog-title" id="cameraTitle">Ambil Foto Bukti</h2>
    <video class="camera-video" autoplay playsinline></video>
    <div class="camera-actions">
        <button type="button" class="orgmate-btn ghost" id="cameraCancelBtn">Batal</button>
        <button type="button" class="orgmate-btn" id="cameraCaptureBtn">📷 Ambil Foto</button>
    </div>
</dialog>
<script>
    (function () {
        if (!window.CameraCapture) {
            window.CameraCapture = (function () {
                const dialog = document.getElementById('cameraDialog');
                const video = dialog ? dialog.querySelector('.camera-video') : null;
                const captureBtn = document.getElementById('cameraCaptureBtn');
                const cancelBtn = document.getElementById('cameraCancelBtn');
                let stream = null;
                let targetInput = null;

                function stop() {
                    if (stream) {
                        stream.getTracks().forEach((track) => track.stop());
                        stream = null;
                    }
                    if (dialog && dialog.open) dialog.close();
                }

                async function open(input) {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        alert('Kamera tidak didukung di browser ini. Silakan upload file bukti secara manual.');
                        return;
                    }
                    targetInput = input;
                    if (typeof dialog.showModal === 'function') dialog.showModal();
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: { ideal: 'environment' } },
                            audio: false,
                        });
                        video.srcObject = stream;
                    } catch (error) {
                        alert('Tidak dapat mengakses kamera. Periksa izin kamera browser Anda.');
                        stop();
                    }
                }

                function capture() {
                    if (!targetInput || !stream) return;
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    canvas.toBlob((blob) => {
                        if (!blob) return;
                        const file = new File([blob], 'bukti-kamera.jpg', { type: 'image/jpeg' });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        targetInput.files = dataTransfer.files;
                        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                        stop();
                    }, 'image/jpeg', 0.85);
                }

                if (dialog) {
                    captureBtn.addEventListener('click', capture);
                    cancelBtn.addEventListener('click', stop);
                    dialog.addEventListener('close', () => {
                        if (stream) {
                            stream.getTracks().forEach((track) => track.stop());
                            stream = null;
                        }
                    });
                }

                document.querySelectorAll('[data-camera-target]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const input = document.getElementById(button.dataset.cameraTarget);
                        if (input) open(input);
                    });
                });
            })();
        }
    })();
</script>
