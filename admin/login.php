<!-- admin/login.php -->
<?php
require_once '../includes/init.php';

$auth = new Auth();

// Giriş yapmış kullanıcı varsa ana sayfaya yönlendir
if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Giriş işlemi
    if ($auth->login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}

// admin/login.php dosyasındaki giriş işleme bölümüne ekleyin
if (isset($_POST['remember']) && $_POST['remember']) {
    // Daha uzun oturum süresi ayarla
    $lifetime = 30 * 24 * 60 * 60; // 30 gün
    ini_set('session.gc_maxlifetime', $lifetime);
    session_set_cookie_params($lifetime);
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Emlak İlan Sitesi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4338ca;
            --primary-dark: #312e81;
            --primary-light: #c7d2fe;
            --gray-light: #e2e8f0;
            --white: #ffffff;
            --secondary: #0f172a;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-card {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(to right, rgba(67, 56, 202, 0.1), rgba(49, 46, 129, 0.05));
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .login-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .login-logo i {
            font-size: 2rem;
            margin-right: 0.5rem;
        }
        
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .input-group-text {
            background-color: rgba(241, 245, 249, 0.5);
            border: 1px solid var(--gray-light);
            border-radius: 0.5rem 0 0 0.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(49, 46, 129, 0.2);
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem;
            background-color: rgba(241, 245, 249, 0.5);
            border-top: 1px solid var(--gray-light);
        }
        
        .back-link {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
            transform: translateX(-3px);
        }
        
        .back-link i {
            margin-right: 0.5rem;
        }
        
        .alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid transparent;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            border-left-color: #ef4444;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="bi bi-building"></i>
                <span>Emlak Admin</span>
            </div>
            <h1 class="login-title">Yönetim Paneli Girişi</h1>
            <p class="login-subtitle">Devam etmek için giriş yapın</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login.php">
                <div class="mb-4">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Şifre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Beni hatırla
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            <a href="../index.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Ana Sayfaya Dön
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

