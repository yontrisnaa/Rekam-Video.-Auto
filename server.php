<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['video'])) {
        $file = $_FILES['video'];
        $uploadDir = 'uploads/';
        $filePath = $uploadDir . basename($file['name']);

        // Pastikan folder "uploads" ada
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            echo "Video berhasil disimpan di: $filePath";
        } else {
            echo "Gagal menyimpan video.";
        }
    } else {
        echo "Tidak ada video yang diunggah.";
    }
}
?>
