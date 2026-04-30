<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — MediBase University Health</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Inter',system-ui,sans-serif;
  background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0f172a 100%);
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;
}
.auth-page{
  width:100%;max-width:440px;background:#fff;border-radius:20px;
  box-shadow:0 25px 60px rgba(0,0,0,.4),0 0 0 1px rgba(255,255,255,.05);overflow:hidden;
}
.auth-header{
  background:linear-gradient(135deg,#1d4ed8,#0891b2);
  padding:36px 40px 32px;text-align:center;color:#fff;
}
.auth-header .logo-icon{font-size:44px;margin-bottom:10px;filter:drop-shadow(0 4px 8px rgba(0,0,0,.3))}
.auth-header h1{font-size:26px;font-weight:700;letter-spacing:-.5px}
.auth-header p{font-size:14px;opacity:.8;margin-top:4px}
.auth-body{padding:36px 40px}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:7px}
.form-group input{
  width:100%;padding:12px 16px;border:2px solid #e2e8f0;border-radius:10px;
  font-size:14px;font-family:inherit;color:#1e293b;transition:border-color .2s,box-shadow .2s;
  outline:none;background:#f8fafc;
}
.form-group input:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12);background:#fff}
.btn-signin{
  width:100%;padding:14px;background:linear-gradient(135deg,#2563eb,#0891b2);
  color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:600;
  font-family:inherit;cursor:pointer;transition:all .2s;margin-top:4px;
  box-shadow:0 4px 15px rgba(37,99,235,.35);
}
.btn-signin:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(37,99,235,.45)}
.btn-signin:active{transform:translateY(0)}
.alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;border:1px solid}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.alert-success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
.register-link{text-align:center;margin-top:20px;font-size:13px;color:#64748b}
.register-link a{color:#2563eb;font-weight:500;text-decoration:none}
.register-link a:hover{text-decoration:underline}
.creds-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px}
.cred-item{
  background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;
  padding:10px 12px;font-size:12px;cursor:pointer;transition:all .15s;
}
.cred-item:hover{background:#eff6ff;border-color:#93c5fd;transform:translateY(-1px)}
.cred-role{font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.cred-role.admin{color:#dc2626}
.cred-role.doctor{color:#2563eb}
.cred-role.nurse{color:#16a34a}
.cred-role.student{color:#7c3aed}
.cred-email{color:#475569;font-size:11px;margin-bottom:1px}
.cred-pass{color:#94a3b8;font-size:11px;font-family:monospace}
.creds-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;text-align:center;margin-top:20px}
.divider{border:none;border-top:1px solid #f1f5f9;margin:20px 0 0}
</style>
</head>
<body>
<div class="auth-page">
  <div class="auth-header">
    <div class="logo-icon">🏥</div>
    <h1>MediBase</h1>
    <p>University Health Center</p>
  </div>
  <div class="auth-body">
    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success = getFlash('success')): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/login.php" novalidate id="loginForm">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required autocomplete="username"
               placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required
               autocomplete="current-password" placeholder="••••••••">
      </div>
      <button type="submit" class="btn-signin">Sign In →</button>
    </form>

    <p class="register-link">
      New student? <a href="<?= BASE_URL ?>/public/register.php">Create your account</a>
    </p>

    <hr class="divider">
    <p class="creds-label">Quick Login — Click to fill</p>
    <div class="creds-grid">
      <div class="cred-item" onclick="fillLogin('admin@medibase.bd','Admin@1234')">
        <div class="cred-role admin">👨‍💼 Admin</div>
        <div class="cred-email">admin@medibase.bd</div>
        <div class="cred-pass">Admin@1234</div>
      </div>
      <div class="cred-item" onclick="fillLogin('dr.ayesha@medibase.bd','Admin@1234')">
        <div class="cred-role doctor">👨‍⚕️ Doctor</div>
        <div class="cred-email">dr.ayesha@medibase.bd</div>
        <div class="cred-pass">Admin@1234</div>
      </div>
      <div class="cred-item" onclick="fillLogin('nurse.salma@medibase.bd','Admin@1234')">
        <div class="cred-role nurse">👩‍⚕️ Nurse</div>
        <div class="cred-email">nurse.salma@medibase.bd</div>
        <div class="cred-pass">Admin@1234</div>
      </div>
      <div class="cred-item" onclick="fillLogin('farida@student.uni','Admin@1234')">
        <div class="cred-role student">🎓 Student</div>
        <div class="cred-email">farida@student.uni</div>
        <div class="cred-pass">Admin@1234</div>
      </div>
    </div>
  </div>
</div>
<script>
function fillLogin(email, pass) {
  document.getElementById('email').value    = email;
  document.getElementById('password').value = pass;
}
</script>
</body>
</html>
