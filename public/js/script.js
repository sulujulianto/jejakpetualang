// File: public/js/script.js

// Menambahkan 'event listener' yang akan menjalankan semua kode di dalamnya HANYA SETELAH
// seluruh halaman HTML selesai dimuat (DOM-nya sudah 'ready').
document.addEventListener("DOMContentLoaded", function() {
    // Mencari semua elemen di halaman yang memiliki kelas CSS 'fade-in'.
    const fadeInElements = document.querySelectorAll('.fade-in');

    // Cek apakah ada elemen dengan kelas 'fade-in' di halaman ini.
    // Ini untuk mencegah error jika skrip ini dimuat di halaman yang tidak memiliki elemen tersebut.
    if (fadeInElements.length > 0) {
        // Membuat sebuah 'IntersectionObserver' baru. Observer ini bertugas untuk "mengamati" elemen
        // dan akan memicu fungsi callback ketika elemen tersebut masuk atau keluar dari viewport (layar).
        const observer = new IntersectionObserver((entries) => {
            // Looping untuk setiap elemen ('entry') yang statusnya berubah (masuk/keluar layar).
            entries.forEach(entry => {
                // `entry.isIntersecting` akan bernilai `true` jika elemen mulai terlihat di layar.
                if (entry.isIntersecting) {
                    // Jika elemen terlihat, tambahkan kelas 'visible' ke elemen tersebut.
                    // (Gaya untuk kelas 'visible', seperti mengubah opacity, diatur di file CSS).
                    entry.target.classList.add('visible');
                    
                    // Setelah animasi selesai, hentikan pengamatan pada elemen ini untuk menghemat resource.
                    // Animasi fade-in hanya perlu terjadi sekali.
                    observer.unobserve(entry.target);
                }
            });
        }, {
            // Opsi untuk observer:
            // `threshold: 0.1` berarti callback akan dipicu ketika 10% dari elemen sudah terlihat di layar.
            threshold: 0.1
        });

        // Looping untuk setiap elemen 'fade-in' yang ditemukan.
        fadeInElements.forEach(element => {
            // Mulai amati setiap elemen.
            observer.observe(element);
        });
    }
});