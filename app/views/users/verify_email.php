<?php
/**
 * Email Verification Form View
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
    .text-muted,
    .small {
        color: #bbbbbb !important;
    }

    a {
        color: #0d6efd;
    }

    a:hover {
        color: #66b2ff;
    }

    .btn-outline-primary,
    .btn-outline-secondary {
        border-color: rgba(255, 255, 255, 0.3);
        color: #ffffff;
    }

    .btn-outline-primary:hover,
    .btn-outline-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: #0d6efd;
        color: #ffffff;
    }

    .letter-spacing {
        letter-spacing: 0.3em !important;
        font-weight: bold;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card transparent-card shadow">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Verify Your Email</h2>

                <?php if (!empty($email)): ?>
                    <div class="text-center mb-4">
                        <i class="bi bi-envelope-check fs-1 text-primary mb-3"></i>
                        <p class="mb-2 text-white">
                            We've sent a verification code to:
                        </p>
                        <p class="fw-bold text-primary">
                            <?php echo htmlspecialchars($email); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <p class="text-muted small text-center mb-4">
                    Please check your email and enter the 6-digit verification code below.
                    The code will expire in 30 minutes.
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST"
                      action="<?php echo BASE_URL; ?>users/verifyEmail"
                      id="verifyForm"
                      class="needs-validation"
                      novalidate>

                    <div class="mb-3">
                        <label for="code" class="form-label">Verification Code</label>
                        <input type="text"
                               class="form-control text-center fs-4 letter-spacing"
                               id="code"
                               name="code"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               required
                               placeholder="000000"
                               autocomplete="off"
                               autofocus
                               inputmode="numeric">
                        <div class="invalid-feedback">
                            Please enter the 6-digit verification code.
                        </div>
                        <div class="form-text text-center">
                            Enter the 6-digit code from your email
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-check-circle"></i> Verify Email
                    </button>
                </form>

                <div class="text-center">
                    <p class="text-muted small mb-3">
                        Didn't receive the code or code expired?
                    </p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>users/resendVerificationCode"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Resend Code
                        </a>
                        <a href="<?php echo BASE_URL; ?>users/register"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Register with Different Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');

    // Auto-format: only allow numbers
    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 6) {
            this.value = this.value.substring(0, 6);
        }
    });

    // Focus on code input
    codeInput.focus();

    // Form validation
    const form = document.getElementById('verifyForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
