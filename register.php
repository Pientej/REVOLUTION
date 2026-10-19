<?php
require __DIR__ . '/config.php';

// Zaten girişliyse panele gönder
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Hata / başarı / eski değerleri al
$error   = $_SESSION['register_error']   ?? null;
$success = $_SESSION['register_success'] ?? null;
$old     = $_SESSION['register_old']     ?? ['username' => '', 'email' => ''];
unset($_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['register_old']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révolution | Kayıt Ol</title>

    <link rel="stylesheet" href="style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<main class="page-container">

    <!-- SOL: MARKA -->
    <section class="brand-section">
        <div class="brand-logo">
            <i class="fa-solid fa-crown crown"></i>
            <div class="logo-letter">R</div>
        </div>
        <h1 class="brand-title">RÉVO<span>LUTION</span></h1>
        <p class="brand-subtitle">DAHA FAZLASI SENİNLE</p>
        <div class="brand-line"></div>
        <div class="brand-watermark">R</div>
    </section>

    <!-- ORTA: KAYIT FORMU -->
    <section class="login-section">
        <div class="login-card">

            <div class="mobile-logo">
                <i class="fa-solid fa-crown"></i>
                <h2>RÉVOLUTION</h2>
            </div>

            <div class="card-heading">
                <h2>Aramıza Katıl</h2>
                <p>Hesabını oluştur ve yolculuğa başla.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="register_process.php" method="post" id="registerForm">

                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <div class="input-group">
                    <label for="username">Takma ad</label>
                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Takma adını gir"
                            autocomplete="username"
                            minlength="3"
                            maxlength="30"
                            value="<?= htmlspecialchars($old['username'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="input-group">
                    <label for="email">E-posta adresi</label>
                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="ornek@mail.com"
                            autocomplete="email"
                            value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Şifre</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="En az 8 karakter"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Şifreyi göster">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password_confirm">Şifre (tekrar)</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            placeholder="Şifreni tekrar gir"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="terms" value="1" required>
                        <span>
                            <a href="#" class="forgot-password" style="margin:0;">Kullanım şartlarını</a>
                            kabul ediyorum
                        </span>
                    </label>
                </div>

                <button type="submit" class="login-button">
                    <span>Kayıt Ol</span>
                    <i class="fa-solid fa-user-plus"></i>
                </button>

                <div class="separator">
                    <span></span>
                    <p>VEYA</p>
                    <span></span>
                </div>

                <p class="register-text">
                    Zaten hesabın var mı?
                    <a href="login.php">Giriş Yap</a>
                </p>
            </form>
        </div>
    </section>

    <!-- SAĞ: SLOGAN -->
    <section class="slogan-section">
        <div class="slogan">
            <p>Yolculuğun</p>
            <span>burada başlıyor...</span>
            <div class="slogan-underline"></div>
        </div>
        <div class="sun-glow"></div>
        <div class="red-light"></div>
    </section>

</main>

<footer class="footer">
    RÉVOLUTION © <?= date('Y') ?>
    <span>|</span>
    Tüm hakları saklıdır.
</footer>

<script>
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput  = document.getElementById("password");

    togglePassword.addEventListener("click", function () {
        const isPassword = passwordInput.type === "password";
        passwordInput.type = isPassword ? "text" : "password";

        this.innerHTML = isPassword
            ? '<i class="fa-regular fa-eye-slash"></i>'
            : '<i class="fa-regular fa-eye"></i>';

        this.setAttribute("aria-label", isPassword ? "Şifreyi gizle" : "Şifreyi göster");
    });

    // Canlı şifre eşleşme kontrolü
    const confirmInput = document.getElementById("password_confirm");
    confirmInput.addEventListener("input", function () {
        this.setCustomValidity(
            this.value !== passwordInput.value ? "Şifreler eşleşmiyor." : ""
        );
    });
</script>

</body>
</html>