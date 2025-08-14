// CATATAN: Ini adalah versi final dari file JavaScript Anda yang sudah diperbaiki secara lengkap.

document.addEventListener('DOMContentLoaded', function () {
    
    // --- Fungsi untuk membuat navbar menjadi solid saat di-scroll (Kode Asli Anda) ---
    const navbar = document.querySelector('.navbar-custom');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // --- Logika untuk tombol "Baca Selengkapnya" (Kode Asli Anda) ---
    const readMoreBtn = document.getElementById('read-more-trigger');
    const descriptionBox = document.getElementById('description-box');
    if (readMoreBtn && descriptionBox) {
        setTimeout(() => {
            if (descriptionBox.scrollHeight <= descriptionBox.clientHeight) {
                readMoreBtn.style.display = 'none';
            }
        }, 0);

        readMoreBtn.addEventListener('click', function (event) {
            event.preventDefault(); 
            descriptionBox.classList.toggle('expanded'); 
            
            if (descriptionBox.classList.contains('expanded')) {
                this.textContent = 'Tampilkan Lebih Sedikit';
            } else {
                this.textContent = 'Baca Selengkapnya';
            }
        });
    }

    // --- Logika untuk semua tombol kuantitas (+/-) (Kode Asli Anda) ---
    const quantityWrappers = document.querySelectorAll('.quantity-wrapper');
    if (quantityWrappers.length > 0) {
        quantityWrappers.forEach(wrapper => {
            const minusBtn = wrapper.querySelector('button:first-of-type');
            const plusBtn = wrapper.querySelector('button:last-of-type');
            const input = wrapper.querySelector('.quantity-input');

            if (minusBtn) {
                minusBtn.addEventListener('click', function() {
                    input.stepDown();
                });
            }
            if (plusBtn) {
                plusBtn.addEventListener('click', function() {
                    input.stepUp();
                });
            }
        });
    }

    // --- [PERBAIKAN UTAMA] Logika AJAX untuk Form "Tambah ke Keranjang" ---
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(event) {
            // 1. Mencegah form berjalan secara normal (mencegah pindah halaman).
            event.preventDefault(); 

            // Tampilkan notifikasi sederhana (Anda bisa menggantinya dengan notifikasi yang lebih baik nanti).
            alert('Menambahkan produk ke keranjang...');

            // 2. Mengambil semua data dari form.
            const formData = new FormData(addToCartForm);
            
            // 3. Mengirim data ke server menggunakan Fetch API.
            fetch('/jejak-petualang/pages/tambah_keranjang.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Mengubah respons dari server menjadi format JSON.
            .then(data => {
                // 4. Memproses respons JSON.
                if (data.success) {
                    alert(data.message); // Tampilkan pesan sukses: "Produk berhasil ditambahkan!"

                    // Perbarui angka di ikon keranjang.
                    let cartCountElement = document.getElementById('cart-item-count');
                    if (!cartCountElement) { // Jika badge belum ada, buat baru.
                        const cartLink = document.querySelector('a[href="/jejak-petualang/pages/keranjang.php"]');
                        if(cartLink) {
                            cartLink.insertAdjacentHTML('beforeend', `
                                <span id="cart-item-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    ${data.cart_count}
                                    <span class="visually-hidden">item di keranjang</span>
                                </span>`);
                            cartCountElement = document.getElementById('cart-item-count');
                        }
                    } else {
                         cartCountElement.innerText = data.cart_count;
                    }
                   
                    if (cartCountElement) {
                        cartCountElement.style.display = 'block';
                    }

                } else {
                    alert(data.message); // Tampilkan pesan error.
                    if (data.redirect) { // Jika server meminta redirect (karena sesi habis).
                        window.location.href = data.redirect;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });
    }

    // --- Logika untuk Rating Bintang Interaktif (Kode Asli Anda) ---
    const starRatingWrapper = document.querySelector('.star-rating-wrapper');
    if (starRatingWrapper) {
        const starLabels = starRatingWrapper.querySelectorAll('.star-rating label');
        const ratingFeedback = starRatingWrapper.querySelector('.rating-text-feedback');

        starLabels.forEach(label => {
            label.addEventListener('mouseenter', function() {
                ratingFeedback.textContent = this.getAttribute('data-text');
            });

            starRatingWrapper.addEventListener('mouseleave', function() {
                const checkedInput = starRatingWrapper.querySelector('input:checked');
                if (checkedInput) {
                    const checkedLabel = starRatingWrapper.querySelector('label[for="' + checkedInput.id + '"]');
                    ratingFeedback.textContent = checkedLabel.getAttribute('data-text');
                } else {
                    ratingFeedback.textContent = '';
                }
            });
        });
    }
});