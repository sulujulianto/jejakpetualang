<div class="container">
    <div class="auth-form-container">
        <h2 class="text-center mb-4">Login</h2>
        
        <?php 
        // Blok PHP untuk menampilkan pesan error.
        // Memeriksa apakah variabel $error sudah diatur dan tidak kosong.
        if (isset($error) && !empty($error)): 
        ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; // Akhir dari blok if ?>

        <form action="/jejakpetualang/auth/login.php" method="POST">
            <?= csrf_field(); ?>
            <div class="mb-3">
                <label for="email" class="form-label text-center d-block">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-center align-items-baseline position-relative w-100">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="/jejakpetualang/auth/reset_password.php" class="forgot-password-link position-absolute end-0">Lupa Password?</a>
                </div>
                <input type="password" class="form-control mt-1" id="password" name="password" required>
            </div>
            
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            <p class="mt-3 text-center">Belum punya akun? <a href="/register.php">Daftar di sini</a></p>
        </form>
    </div>
</div>
