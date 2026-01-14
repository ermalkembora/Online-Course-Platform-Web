<?php
/**
 * Registration View
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
                <h2 class="card-title text-center mb-4">Create Account</h2>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST"
                      action="<?php echo BASE_URL; ?>users/register"
                      id="registerForm"
                      class="needs-validation"
                      novalidate>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text"
                               class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>"
                               id="full_name"
                               name="full_name"
                               value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                               required
                               minlength="2"
                               maxlength="100"
                               placeholder="First Last">
                        <?php if (isset($errors['full_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo htmlspecialchars($errors['full_name']); ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Please provide your full name (first and last name).
                            </div>
                        <?php endif; ?>
                        <div class="form-text">Enter your first and last name</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                               id="email"
                               name="email"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required
                               placeholder="your.email@example.com">
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

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password"
                               class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                               id="password"
                               name="password"
                               required
                               minlength="8"
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
                            Password must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password"
                               class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                               id="confirm_password"
                               name="confirm_password"
                               required
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
                        Register
                    </button>
                </form>

                <div class="text-center">
                    <p class="mb-0 text-muted">
                        Already have an account?
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
    const form = document.getElementById('registerForm');
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
