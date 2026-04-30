<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — MediBase University Health</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0f172a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.auth-page{width:100%;max-width:500px;background:#fff;border-radius:20px;box-shadow:0 25px 60px rgba(0,0,0,.4);overflow:hidden}
.auth-header{background:linear-gradient(135deg,#7c3aed,#2563eb);padding:28px 40px 24px;text-align:center;color:#fff}
.auth-header h1{font-size:22px;font-weight:700}.auth-header p{font-size:13px;opacity:.8;margin-top:4px}
.auth-body{padding:32px 40px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:6px}
.form-group input,.form-group select{width:100%;padding:11px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;font-family:inherit;color:#1e293b;outline:none;background:#f8fafc;transition:border-color .2s}
.form-group input:focus,.form-group select:focus{border-color:#7c3aed;box-shadow:0 0 0 4px rgba(124,58,237,.1);background:#fff}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.btn-register{width:100%;padding:13px;background:linear-gradient(135deg,#7c3aed,#2563eb);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:600;font-family:inherit;cursor:pointer;transition:all .2s;margin-top:4px;box-shadow:0 4px 15px rgba(124,58,237,.35)}
.btn-register:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(124,58,237,.45)}
.alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;border:1px solid}
.alert-error{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.login-link{text-align:center;margin-top:18px;font-size:13px;color:#64748b}
.login-link a{color:#7c3aed;font-weight:500;text-decoration:none}
.pwd-hint{font-size:11px;color:#94a3b8;margin-top:5px}
</style>
</head>
<body>
<div class="auth-page">
  <div class="auth-header">
    <div style="font-size:32px;margin-bottom:8px">🎓</div>
    <h1>Student Registration</h1>
    <p>Create your MediBase health account</p>
  </div>
  <div class="auth-body">
    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/register.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="full_name" required placeholder="Your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email Address *</label>
        <input type="email" name="email" required placeholder="you@university.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Course</label>
          <input type="text" name="course" placeholder="e.g. BSc CSE" value="<?= htmlspecialchars($_POST['course'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Year Level</label>
          <select name="year_level">
            <option value="">— Select —</option>
            <?php foreach(['Year 1','Year 2','Year 3','Year 4','Year 5','Graduate'] as $y): ?>
              <option value="<?= $y ?>" <?= ($_POST['year_level']??'')===$y?'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Contact Number</label>
        <input type="text" name="contact_number" placeholder="+880..." value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Password *</label>
          <input type="password" name="password" required placeholder="Min 8 chars">
          <div class="pwd-hint">Uppercase + lowercase + number</div>
        </div>
        <div class="form-group">
          <label>Confirm Password *</label>
          <input type="password" name="confirm_password" required placeholder="Repeat password">
        </div>
      </div>
      <button type="submit" class="btn-register">Create Account →</button>
    </form>
    <p class="login-link">Already have an account? <a href="<?= BASE_URL ?>/public/login.php">Sign in</a></p>
  </div>
</div>
</body>
</html>
