<?php
require_once 'config/db.php'; require_once 'config/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['name']=$user['full_name']; $_SESSION['role']=$user['role'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Incorrect email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VetSystem | Login</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-bg"><main class="auth-card"><div class="auth-brand"><span class="brand-mark">VS</span><span><b>VetSystem</b><small class="muted">Naic Municipal Veterinary Office</small></span></div><h1>Welcome back</h1><p class="muted">Livestock and Poultry Monitoring System</p><?php if($error):?><div class="alert error"><?=h($error)?></div><?php endif;?><form method="post"><label>Email address</label><input type="email" name="email" required placeholder="you@example.com"><label>Password</label><input type="password" name="password" required placeholder="Enter your password"><button class="btn primary" type="submit">Login</button></form><p class="auth-foot">No account yet? <a href="register.php">Create an account</a></p></main></body></html>
