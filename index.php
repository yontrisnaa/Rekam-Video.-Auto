<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Video Otomatis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
        }
        video {
            width: 100%;
            max-width: 600px;
            border: 2px solid #333;
            margin-top: 20px;
        }
        #status {
            margin: 15px;
            font-size: 1.2em;
            color: green;
        }
    </style>
</head>
<body>
    <h1>Rekam Video Otomatis</h1>
    <video id="preview" autoplay playsinline></video>
    <p id="status">Rekaman sedang berlangsung...</p>

    <script>
        const preview = document.getElementById('preview');
        const statusText = document.getElementById('status');
        let mediaRecorder;
        let recordedChunks = [];

        // Fungsi untuk memulai kamera dan rekaman
        async function startCameraAndRecord() {
            try {
                // Akses kamera
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                preview.srcObject = stream;

                // Siapkan MediaRecorder
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                // Aksi ketika rekaman dihentikan
                mediaRecorder.onstop = async () => {
                    const blob = new Blob(recordedChunks, { type: 'video/webm' });
                    recordedChunks = []; // Kosongkan buffer
                    await uploadToServer(blob); // Unggah ke server
                };

                // Mulai merekam
                mediaRecorder.start();
                console.log('Rekaman dimulai...');
            } catch (error) {
                console.error("Gagal mengakses kamera:", error);
                statusText.textContent = "Gagal mengakses kamera.";
            }
        }

        // Fungsi untuk mengunggah video ke server
        async function uploadToServer(blob) {
            const formData = new FormData();
            formData.append('video', blob, `rekaman-${Date.now()}.webm`);

            try {
                const response = await fetch('server.php', { // Ganti 'server.php' sesuai endpoint server Anda
                    method: 'POST',
                    body: formData,
                });
                if (response.ok) {
                    console.log('Video berhasil diunggah ke server.');
                    statusText.textContent = "Video berhasil diunggah ke server.";
                } else {
                    console.error('Gagal mengunggah video ke server.');
                    statusText.textContent = "Gagal mengunggah video.";
                }
            } catch (error) {
                console.error('Kesalahan saat mengunggah video:', error);
                statusText.textContent = "Kesalahan saat mengunggah video.";
            }
        }

        // Deteksi perubahan visibilitas tab
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                statusText.textContent = "Perhatian: Tab tidak aktif, rekaman mungkin berhenti.";
                console.warn("Browser sedang tidak aktif.");
            } else {
                statusText.textContent = "Rekaman sedang berlangsung...";
                console.log("Browser kembali aktif.");
            }
        });

        // Deteksi jika browser diminimalkan atau kehilangan fokus
        window.addEventListener('blur', () => {
            statusText.textContent = "Perhatian: Browser tidak aktif, rekaman mungkin terganggu.";
            console.warn("Browser kehilangan fokus.");
        });

        // Deteksi jika browser kembali aktif
        window.addEventListener('focus', () => {
            statusText.textContent = "Rekaman sedang berlangsung...";
            console.log("Browser kembali aktif.");
        });

        // Hentikan rekaman ketika halaman ditutup
        window.addEventListener('beforeunload', () => {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            }
        });

        // Mulai kamera dan rekaman saat halaman dimuat
        startCameraAndRecord();
    </script>
</body>
</html>
