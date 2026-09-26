<?php
$pageTitle = 'Login — HelpDesk Pro';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="auth-box">
    <div class="card card-auth">
        <h2>Sign In to HelpDesk</h2>
        <p class="subtitle">Access your account to submit requests or manage technician queues.</p>

        <form action="/login" method="POST" class="mt-4">
            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="admin@company.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="password123">
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-3">Sign In</button>
        </form>

        <div class="demo-logins mt-4">
            <h4>💡 Quick Demo Accounts (Click to Autofill):</h4>
            <div class="demo-buttons">
                <button type="button" class="btn-demo" onclick="fillCreds('admin@company.com', 'password123')">
                    👑 <strong>Technician / Admin</strong> (admin@company.com)
                </button>
                <button type="button" class="btn-demo" onclick="fillCreds('sarah@client.com', 'password123')">
                    👤 <strong>Client User</strong> (sarah@client.com)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
