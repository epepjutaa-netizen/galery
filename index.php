<?php
declare(strict_types=1);

$privateDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'private';
$accountsFile = $privateDirectory . DIRECTORY_SEPARATOR . 'accounts.json';

if (!is_dir($privateDirectory) && !mkdir($privateDirectory, 0700, true) && !is_dir($privateDirectory)) {
    http_response_code(500);
    exit('Penyimpanan login tidak dapat disiapkan. Periksa izin folder situs.');
}

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalizeAccountName(string $name): string
{
    return strtolower(trim($name));
}

function readAccounts(string $path): ?array
{
    if (!file_exists($path)) {
        return null;
    }

    $contents = file_get_contents($path);
    $accounts = $contents === false ? null : json_decode($contents, true);
    if (
        !is_array($accounts)
        || array_is_list($accounts) === false
        || count($accounts) !== 2
        || count(array_filter($accounts, static fn ($account): bool =>
            is_array($account)
            && isset($account['id'], $account['name'], $account['password_hash'])
            && is_string($account['id'])
            && is_string($account['name'])
            && is_string($account['password_hash'])
        )) !== 2
    ) {
        throw new RuntimeException('File akun tidak dapat dibaca atau isinya tidak valid.');
    }

    return $accounts;
}

function createAccountPair(string $path, array $names, array $passwords): array
{
    $lockFile = dirname($path) . DIRECTORY_SEPARATOR . 'setup.lock';
    $lock = fopen($lockFile, 'c');
    if ($lock === false) {
        throw new RuntimeException('Penyimpanan setup tidak dapat dibuka.');
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('Penyimpanan setup tidak dapat dikunci.');
        }
        if (file_exists($path)) {
            throw new DomainException('Akun berdua sudah dibuat. Silakan masuk.');
        }

        $accounts = [];
        foreach ($names as $index => $name) {
            $accounts[] = [
                'id' => bin2hex(random_bytes(16)),
                'name' => $name,
                'password_hash' => password_hash($passwords[$index], PASSWORD_DEFAULT),
            ];
        }

        $encoded = json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            throw new RuntimeException('Data akun tidak dapat diproses.');
        }

        $temporaryPath = tempnam(dirname($path), 'accounts-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Penyimpanan akun tidak dapat disiapkan.');
        }

        try {
            if (file_put_contents($temporaryPath, $encoded, LOCK_EX) === false) {
                throw new RuntimeException('Akun tidak dapat disimpan.');
            }
            @chmod($temporaryPath, 0600);
            if (!rename($temporaryPath, $path)) {
                throw new RuntimeException('File akun tidak dapat disimpan.');
            }
            @chmod($path, 0600);
        } finally {
            if (file_exists($temporaryPath)) {
                unlink($temporaryPath);
            }
        }

        return $accounts;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function redirectToLogin(): never
{
    header('Location: ./', true, 303);
    exit;
}

function renderAuthPage(string $mode, string $message, string $messageType, string $csrfToken, array $values = []): never
{
    $setup = $mode === 'setup';
    $title = $setup ? 'Siapkan ruang berdua' : 'Ruang kecil untuk kita';
    $subtitle = $setup
        ? 'Buat tepat dua akun pribadi. Halaman galeri hanya terbuka setelah salah satu dari kalian masuk.'
        : 'Masuk memakai akun yang sudah dibuat untuk kalian berdua.';
    $safeMessage = escapeHtml($message);
    $messageClass = $messageType === 'error' ? 'error' : 'notice';
    $nameOne = escapeHtml((string) ($values['name_one'] ?? ''));
    $nameTwo = escapeHtml((string) ($values['name_two'] ?? ''));
    $loginName = escapeHtml((string) ($values['login_name'] ?? ''));
    $csrf = escapeHtml($csrfToken);
    $pageTitle = escapeHtml($title);
    $pageSubtitle = escapeHtml($subtitle);

    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!doctype html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#f6f2eb">
      <title><?= $pageTitle ?> — Ruang Cerita</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
      <style>
        :root{color-scheme:light;--paper:#f6f2eb;--ink:#28221f;--muted:#8e8379;--rose:#a85d55;--line:rgba(52,43,38,.13);font-family:"DM Sans",Arial,sans-serif}
        *{box-sizing:border-box}
        body{display:grid;min-width:320px;min-height:100vh;margin:0;padding:24px;overflow-x:hidden;background:radial-gradient(ellipse at 50% 25%,rgba(232,216,197,.46),transparent 45%),var(--paper);color:var(--ink);place-items:center}
        .romance-canvas{position:fixed;z-index:0;inset:0;width:100%;height:100%;pointer-events:none}
        .card{position:relative;z-index:1;width:min(440px,100%);padding:28px 34px 30px;border:1px solid var(--line);border-radius:12px;background:rgba(255,255,255,.76);box-shadow:0 18px 55px rgba(58,42,32,.1);backdrop-filter:blur(8px)}
        .heart{display:grid;width:43px;height:43px;margin:0 auto 16px;border:1px solid rgba(168,93,85,.3);border-radius:50%;color:var(--rose);font-size:19px;place-items:center}
        .eyebrow{margin:0;color:var(--rose);font-size:9px;font-weight:700;letter-spacing:.19em;text-align:center;text-transform:uppercase}
        h1{margin:10px 0 8px;font-family:"Playfair Display",Georgia,serif;font-size:clamp(29px,7vw,37px);font-weight:500;letter-spacing:-.045em;text-align:center}
        .subtitle{margin:0 auto 23px;color:var(--muted);font-size:11px;line-height:1.7;text-align:center}
        .couple-photo{position:relative;width:min(100%,255px);height:112px;margin:0 auto 18px;overflow:hidden;border:3px solid rgba(255,255,255,.94);border-radius:8px;background:#dccbbf;box-shadow:0 9px 24px rgba(58,42,32,.13);transform:rotate(-1deg)}
        .couple-photo img{display:block;width:100%;height:100%;object-fit:cover;object-position:center 45%}
        .couple-photo::after{position:absolute;inset:45% 0 0;background:linear-gradient(transparent,rgba(39,28,30,.22));content:"";pointer-events:none}
        .couple-photo figcaption{position:absolute;z-index:1;right:10px;bottom:8px;color:#fff;font-family:"Playfair Display",Georgia,serif;font-size:11px;font-style:italic;text-shadow:0 1px 5px rgba(0,0,0,.35)}
        .message{margin:0 0 16px;padding:11px 13px;border-radius:6px;font-size:10px;line-height:1.6}
        .message.notice{background:rgba(168,93,85,.075);color:#88534d}
        .message.error{background:rgba(161,62,55,.08);color:#a13e37}
        .pair{display:grid;grid-template-columns:1fr 1fr;gap:11px}
        label{display:block;margin:0 0 7px;color:#655a53;font-size:10px;font-weight:600}
        .field{margin-bottom:13px}
        input{width:100%;min-height:41px;padding:0 11px;border:1px solid var(--line);border-radius:6px;outline:none;background:rgba(255,255,255,.8);color:var(--ink);font:inherit;font-size:11px}
        input:focus{border-color:rgba(168,93,85,.68);box-shadow:0 0 0 3px rgba(168,93,85,.1)}
        .hint{display:block;margin-top:5px;color:var(--muted);font-size:9px;line-height:1.5}
        button{width:100%;min-height:43px;margin-top:4px;border:0;border-radius:6px;background:var(--ink);color:#fff;cursor:pointer;font:inherit;font-size:11px;font-weight:600;transition:background 180ms ease}
        button:hover{background:var(--rose)}
        .privacy{margin:15px 0 0;color:var(--muted);font-size:9px;line-height:1.6;text-align:center}
        @media(max-width:420px){body{padding:17px}.card{padding:24px 21px 25px}.pair{grid-template-columns:1fr;gap:0}.couple-photo{height:98px;margin-bottom:15px}}
        @media(prefers-reduced-motion:reduce){.romance-canvas{display:none}}
      </style>
    </head>
    <body>
      <canvas class="romance-canvas" id="romance-canvas" aria-hidden="true"></canvas>
      <main class="card">
        <div class="heart" aria-hidden="true">♡</div>
        <p class="eyebrow">album kecil, cerita besar</p>
        <h1><?= $pageTitle ?></h1>
        <p class="subtitle"><?= $pageSubtitle ?></p>
        <figure class="couple-photo">
          <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&amp;fit=crop&amp;w=640&amp;q=85" alt="Dua hati membentuk satu bingkai, mengingatkan tentang kebersamaan">
          <figcaption>tentang kita, selalu ♡</figcaption>
        </figure>
        <?php if ($message !== ''): ?>
          <p class="message <?= $messageClass ?>" role="alert"><?= $safeMessage ?></p>
        <?php endif; ?>

        <?php if ($setup): ?>
          <form method="post" action="./" autocomplete="on">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="setup">
            <div class="pair">
              <div class="field">
                <label for="name-one">Nama kamu</label>
                <input id="name-one" name="name_one" type="text" maxlength="40" autocomplete="name" required value="<?= $nameOne ?>">
              </div>
              <div class="field">
                <label for="name-two">Nama pasangan</label>
                <input id="name-two" name="name_two" type="text" maxlength="40" required value="<?= $nameTwo ?>">
              </div>
            </div>
            <div class="pair">
              <div class="field">
                <label for="password-one">Kata sandi kamu</label>
                <input id="password-one" name="password_one" type="password" minlength="10" maxlength="72" autocomplete="new-password" required>
                <span class="hint">Minimal 10 karakter.</span>
              </div>
              <div class="field">
                <label for="password-two">Kata sandi pasangan</label>
                <input id="password-two" name="password_two" type="password" minlength="10" maxlength="72" autocomplete="new-password" required>
                <span class="hint">Gunakan kata sandi yang berbeda.</span>
              </div>
            </div>
            <button type="submit">Buat dua akun pribadi</button>
          </form>
          <p class="privacy">Setup ini hanya dapat dilakukan sekali. Kata sandi disimpan sebagai hash dan akun tambahan tidak dapat didaftarkan dari halaman ini.</p>
        <?php else: ?>
          <form method="post" action="./" autocomplete="on">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="login">
            <div class="field">
              <label for="login-name">Nama akun</label>
              <input id="login-name" name="login_name" type="text" maxlength="40" autocomplete="username" required autofocus value="<?= $loginName ?>">
            </div>
            <div class="field">
              <label for="login-password">Kata sandi</label>
              <input id="login-password" name="login_password" type="password" maxlength="72" autocomplete="current-password" required>
            </div>
            <button type="submit">Masuk ke galeri kita</button>
          </form>
          <p class="privacy">Hanya dua akun pasangan yang dibuat saat setup dapat masuk.</p>
        <?php endif; ?>
      </main>
      <script>
          (() => {
            const canvas = document.querySelector("#romance-canvas");
            const context = canvas.getContext("2d");
            if (!context) return;

            const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
            const particles = [];
            const colors = ["#dc839e", "#eaa4b6", "#d97191", "#f0b6c4"];
            let animationFrame;
            let lastFrameTime = 0;

            function resizeCanvas() {
              const ratio = Math.min(window.devicePixelRatio || 1, 1.25);
              canvas.width = Math.round(window.innerWidth * ratio);
              canvas.height = Math.round(window.innerHeight * ratio);
              context.setTransform(ratio, 0, 0, ratio, 0, 0);
              particles.length = 0;
              const total = window.innerWidth < 600 ? 20 : 32;

              for (let index = 0; index < total; index += 1) {
                particles.push({
                  x: Math.random() * window.innerWidth,
                  y: Math.random() * window.innerHeight,
                  size: 7 + Math.random() * 8,
                  speed: .25 + Math.random() * .45,
                  sway: .35 + Math.random() * .75,
                  phase: Math.random() * Math.PI * 2,
                  rotation: Math.random() * Math.PI * 2,
                  spin: (Math.random() - .5) * .012,
                  opacity: .48 + Math.random() * .37,
                  isHeart: index % 4 === 0,
                  color: colors[Math.floor(Math.random() * colors.length)]
                });
              }

              drawParticles();
            }

            function drawParticles() {
              const width = window.innerWidth;
              const height = window.innerHeight;
              context.clearRect(0, 0, width, height);

              for (const particle of particles) {
                context.save();
                context.translate(particle.x, particle.y);
                context.rotate(particle.rotation);
                context.globalAlpha = particle.opacity;

                if (particle.isHeart) {
                  context.scale(particle.size / 18, particle.size / 18);
                  context.beginPath();
                  context.moveTo(0, 6);
                  context.bezierCurveTo(-4, 3, -10, -1, -9, -5);
                  context.bezierCurveTo(-8, -11, -1, -11, 0, -6);
                  context.bezierCurveTo(1, -11, 8, -11, 9, -5);
                  context.bezierCurveTo(10, -1, 4, 3, 0, 6);
                  context.fillStyle = particle.color;
                  context.fill();
                } else {
                  for (let petal = 0; petal < 5; petal += 1) {
                    context.save();
                    context.rotate((Math.PI * 2 * petal) / 5);
                    context.beginPath();
                    context.ellipse(0, -particle.size * .45, particle.size * .27, particle.size * .48, 0, 0, Math.PI * 2);
                    context.fillStyle = particle.color;
                    context.fill();
                    context.restore();
                  }

                  context.beginPath();
                  context.arc(0, 0, particle.size * .13, 0, Math.PI * 2);
                  context.fillStyle = "#fff0d9";
                  context.fill();
                }

                context.restore();
              }
            }

            function animate(timestamp) {
              if (document.hidden || reducedMotion.matches) {
                animationFrame = undefined;
                return;
              }

              const delta = lastFrameTime ? Math.min(timestamp - lastFrameTime, 32) : 16;
              lastFrameTime = timestamp;

              for (const particle of particles) {
                particle.y += particle.speed * delta;
                particle.x += Math.sin(timestamp * .00055 + particle.phase) * particle.sway * (delta / 16);
                particle.rotation += particle.spin * (delta / 16);

                if (particle.y - particle.size > window.innerHeight) {
                  particle.y = -particle.size;
                  particle.x = Math.random() * window.innerWidth;
                }
                if (particle.x < -particle.size) particle.x = window.innerWidth + particle.size;
                if (particle.x > window.innerWidth + particle.size) particle.x = -particle.size;
              }

              drawParticles();
              animationFrame = window.requestAnimationFrame(animate);
            }

            function startAnimation() {
              if (reducedMotion.matches || document.hidden || animationFrame !== undefined) return;
              lastFrameTime = 0;
              animationFrame = window.requestAnimationFrame(animate);
            }

            window.addEventListener("resize", resizeCanvas);
            document.addEventListener("visibilitychange", () => {
              if (document.hidden) {
                if (animationFrame !== undefined) window.cancelAnimationFrame(animationFrame);
                animationFrame = undefined;
              } else {
                startAnimation();
              }
            });
            reducedMotion.addEventListener("change", () => {
              if (reducedMotion.matches) {
                if (animationFrame !== undefined) window.cancelAnimationFrame(animationFrame);
                animationFrame = undefined;
                drawParticles();
              } else {
                startAnimation();
              }
            });
            resizeCanvas();
            startAnimation();
          })();
      </script>
    </body>
    </html>
    <?php
    exit;
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = 'notice';
$values = [];

try {
    $accounts = readAccounts($accountsFile);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedToken = $_POST['csrf'] ?? '';
        if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
            http_response_code(403);
            $message = 'Sesi formulir berakhir atau tidak valid. Muat ulang halaman dan coba lagi.';
            $messageType = 'error';
        } else {
            $action = $_POST['action'] ?? '';

            if ($action === 'logout') {
                unset($_SESSION['account_id']);
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                redirectToLogin();
            }

            if ($action === 'setup' && $accounts === null) {
                $nameOne = trim((string) ($_POST['name_one'] ?? ''));
                $nameTwo = trim((string) ($_POST['name_two'] ?? ''));
                $passwordOne = (string) ($_POST['password_one'] ?? '');
                $passwordTwo = (string) ($_POST['password_two'] ?? '');
                $values = ['name_one' => $nameOne, 'name_two' => $nameTwo];

                if (
                    $nameOne === ''
                    || $nameTwo === ''
                    || strlen($nameOne) > 80
                    || strlen($nameTwo) > 80
                    || normalizeAccountName($nameOne) === normalizeAccountName($nameTwo)
                ) {
                    throw new DomainException('Isi dua nama akun yang berbeda. Setiap nama maksimal 40 karakter.');
                }
                if (strlen($passwordOne) < 10 || strlen($passwordTwo) < 10 || strlen($passwordOne) > 72 || strlen($passwordTwo) > 72) {
                    throw new DomainException('Kata sandi masing-masing harus berisi 10–72 karakter.');
                }
                if (hash_equals($passwordOne, $passwordTwo)) {
                    throw new DomainException('Buat kata sandi yang berbeda untuk masing-masing akun.');
                }

                $accounts = createAccountPair($accountsFile, [$nameOne, $nameTwo], [$passwordOne, $passwordTwo]);
                session_regenerate_id(true);
                $_SESSION['account_id'] = $accounts[0]['id'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                redirectToLogin();
            }

            if ($action === 'setup' && $accounts !== null) {
                throw new DomainException('Setup akun sudah selesai. Silakan masuk dengan akun yang telah dibuat.');
            }

            if ($action === 'login' && $accounts !== null) {
                $loginName = trim((string) ($_POST['login_name'] ?? ''));
                $password = (string) ($_POST['login_password'] ?? '');
                $values['login_name'] = $loginName;
                $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'started' => time()];

                if (!is_array($attempts) || !isset($attempts['count'], $attempts['started'])) {
                    $attempts = ['count' => 0, 'started' => time()];
                }
                if (time() - (int) $attempts['started'] >= 900) {
                    $attempts = ['count' => 0, 'started' => time()];
                }
                if ((int) $attempts['count'] >= 5) {
                    $waitMinutes = max(1, (int) ceil((900 - (time() - (int) $attempts['started'])) / 60));
                    $_SESSION['login_attempts'] = $attempts;
                    $message = "Terlalu banyak percobaan. Coba lagi dalam {$waitMinutes} menit.";
                    $messageType = 'error';
                } else {
                    $matchedAccount = null;
                    foreach ($accounts as $account) {
                        if (hash_equals(normalizeAccountName($account['name']), normalizeAccountName($loginName))) {
                            $matchedAccount = $account;
                            break;
                        }
                    }

                    if ($matchedAccount !== null && password_verify($password, $matchedAccount['password_hash'])) {
                        session_regenerate_id(true);
                        $_SESSION['account_id'] = $matchedAccount['id'];
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        unset($_SESSION['login_attempts']);
                        redirectToLogin();
                    }

                    $attempts['count'] = (int) $attempts['count'] + 1;
                    $_SESSION['login_attempts'] = $attempts;
                    $message = 'Nama akun atau kata sandi tidak cocok.';
                    $messageType = 'error';
                }
            } elseif (!in_array($action, ['login', 'setup'], true)) {
                http_response_code(400);
                $message = 'Permintaan tidak dikenali. Muat ulang halaman dan coba lagi.';
                $messageType = 'error';
            }
        }
    }

    if ($accounts === null) {
        renderAuthPage('setup', $message, $messageType, $_SESSION['csrf_token'], $values);
    }

    $currentAccount = null;
    foreach ($accounts as $account) {
        if (isset($_SESSION['account_id']) && hash_equals($account['id'], (string) $_SESSION['account_id'])) {
            $currentAccount = $account;
            break;
        }
    }

    if ($currentAccount === null) {
        unset($_SESSION['account_id']);
        renderAuthPage('login', $message, $messageType, $_SESSION['csrf_token'], $values);
    }

    $galleryPath = __DIR__ . DIRECTORY_SEPARATOR . 'gallery.php';
    if (!is_file($galleryPath) || !is_readable($galleryPath)) {
        throw new RuntimeException('Halaman galeri tidak dapat dibaca.');
    }

    $authenticatedAccountName = escapeHtml($currentAccount['name']);
    $authenticatedCsrfToken = escapeHtml($_SESSION['csrf_token']);
    define('RUANG_CERITA_AUTHENTICATED', true);
    require $galleryPath;
    exit;
} catch (DomainException $error) {
    $currentAction = $_POST['action'] ?? '';
    $mode = $currentAction === 'login' && isset($accounts) ? 'login' : 'setup';
    renderAuthPage($mode, $error->getMessage(), 'error', $_SESSION['csrf_token'], $values);
} catch (Throwable $error) {
    error_log('Ruang Cerita auth error: ' . $error->getMessage());
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Galeri tidak tersedia</title><body><h1>Galeri sementara tidak tersedia</h1><p>Periksa konfigurasi dan izin penyimpanan PHP, lalu coba kembali.</p></body></html>';
}
