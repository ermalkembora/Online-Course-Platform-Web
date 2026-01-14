<?php
/**
 * Forgot Password View
 */
?>

<style>
    body {
        background: url('/e-learning-platform/assets/sources/hero-bg.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa;
    }

    .transparent-card {
        background-color: rgba(0, 0, 0, 0.65);
        backdrop-filter: blur(5px);
        border: none;
        border-radius: 12px;
    }

    .card-title,
    label {
        color: #ffffff;
    }

    .form-control {
        background-color: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    .form-control::placeholder {
        color: #cccccc;
    }

    .form-control:focus {
        background-color: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: none;
    }

    .form-text,
    .text-muted {
        color: #bbbbbb !important;
    }

    a {
        color: #0d6efd;
    }

    a:hover {
        color: #66b2ff;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card transparent-card shadow">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Forgot Password</h2>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <p class="text-muted mb-4 text-center">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form method="POST"
                      action="<?php echo BASE_URL; ?>users/forgotPassword"
                      id="forgotPasswordForm"
                      class="needs-validation"
                      novalidate>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                               id="email"
                               name="email"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required
                               autocomplete="email"
                               placeholder="your.email@example.com"
                               autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['email']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-envelope"></i> Send Reset Link
                    </button>
                </form>

                <div class="text-center">
                    <p class="mb-0 text-muted">
                        Remember your password?
                        <a href="<?php echo BASE_URL; ?>users/login" class="text-decoration-none">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');

    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Auto-focus email if empty
    const emailInput = document.getElementById('email');
    if (!emailInput.value) {
        emailInput.focus();
    }
});
</script>
