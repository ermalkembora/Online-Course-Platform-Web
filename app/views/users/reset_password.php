<?php
/**
 * Reset Password View
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
                <h2 class="card-title text-center mb-4">Reset Password</h2>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <p class="text-muted mb-4 text-center">
                    Enter your new password below.
                </p>

                <form method="POST"
                      action="<?php echo BASE_URL; ?>users/resetPassword"
                      id="resetPasswordForm"
                      class="needs-validation"
                      novalidate>

                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password"
                               class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               autocomplete="new-password"
                               placeholder="At least 8 characters">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['password']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters with uppercase, lowercase, and number.
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            Password must contain: at least 8 characters, one uppercase letter, one lowercase letter, and one number.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password"
                               class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                               id="confirm_password"
                               name="confirm_password"
                               required
                               autocomplete="new-password"
                               placeholder="Re-enter your password">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['confirm_password']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Passwords must match.
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-key"></i> Reset Password
                    </button>
                </form>

                <div class="text-center">
                    <p class="mb-0 text-muted">
                        <a href="<?php echo BASE_URL; ?>users/login" class="text-decoration-none">
                            Back to Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    // Custom password validation
    password.addEventListener('input', function() {
        const value = this.value;
        let isValid = true;
        let message = '';

        if (value.length < 8) {
            isValid = false;
            message = 'Password must be at least 8 characters';
        } else if (!/[A-Z]/.test(value)) {
            isValid = false;
            message = 'Password must contain at least one uppercase letter';
        } else if (!/[a-z]/.test(value)) {
            isValid = false;
            message = 'Password must contain at least one lowercase letter';
        } else if (!/[0-9]/.test(value)) {
            isValid = false;
            message = 'Password must contain at least one number';
        }

        if (isValid) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity(message);
        }
    });

    // Confirm password validation
    confirmPassword.addEventListener('input', function() {
        if (this.value !== password.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    // Form submission validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
