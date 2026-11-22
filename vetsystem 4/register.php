<?php
require_once 'config/db.php'; require_once 'config/auth.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error=''; $success=''; $count=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name=trim($_POST['full_name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Please enter a valid name and email.';
    elseif (strlen($pass)<8) $error='Password must be at least 8 characters.';
    elseif ($pass!==$confirm) $error='Passwords do not match.';
    else { try { $role=$count===0?'admin':'staff'; $st=$pdo->prepare('INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,?)'); $st->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT),$role]); $success='Account created. You can now log in.'; } catch(PDOException $e) { $error='That email is already registered.'; } }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Register | VetSystem</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-bg"><main class="auth-card"><div class="auth-brand"><span class="brand-mark">VS</span><span><b>VetSystem</b><small class="muted">Naic Municipal Veterinary Office</small></span></div><h1>Create account</h1><p class="muted">Register to access VetSystem.</p><?php if($error):?><div class="alert error"><?=h($error)?></div><?php endif;?><?php if($success):?><div class="alert success"><?=h($success)?></div><?php endif;?><form method="post"><label>Full name</label><input name="full_name" required><label>Email address</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" minlength="8" required><label>Confirm password</label><input type="password" name="confirm_password" minlength="8" required><button class="btn primary" type="submit">Register</button></form><p class="auth-foot">Already registered? <a href="index.php">Login</a></p></main></body></html>
