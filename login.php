<?php
require __DIR__ . '/config.php';

// DÜZELTİLDİ: Zaten girişliyse dashboard'a değil, home.php'ye gönder
if (!empty($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

$error   = $_SESSION['login_error']   ?? null;
$success = $_SESSION['login_success'] ?? null;
$old     = $_SESSION['old_email']     ?? '';
unset($_SESSION['login_error'], $_SESSION['login_success'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révolution | Giriş Yap</title>

    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<main class="page-container">

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

    <section class="login-section">
        <div class="login-card">

            <div class="mobile-logo">
                <i class="fa-solid fa-crown"></i>
                <h2>RÉVOLUTION</h2>
            </div>

            <div class="card-heading">
                <h2>Tekrar Hoş Geldin</h2>
                <p>Hesabına giriş yap ve yolculuğuna devam et.</p>
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

            <form action="login_process.php" method="post" id="loginForm">

                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <div class="input-group">
                    <label for="email">E-posta adresi</label>
                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="ornek@mail.com" autocomplete="email" value="<?= htmlspecialchars($old, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">Şifre</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Şifreni gir" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Şifreyi göster">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Beni hatırla</span>
                    </label>
                    <a href="#" class="forgot-password">Şifremi unuttum</a>
                </div>

                <button type="submit" class="login-button">
                    <span>Giriş Yap</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>

                <div class="separator">
                    <span></span>
                    <p>VEYA</p>
                    <span></span>
                </div>

                <p class="register-text">
                    Henüz hesabın yok mu?
                    <a href="register.php">Kayıt Ol</a>
                </p>
            </form>
        </div>
    </section>

    <section class="slogan-section">
        <div class="slogan">
            <p>Hayallerine</p>
            <span>bir adım daha yakınsın...</span>
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
</script>

</body>
</html>