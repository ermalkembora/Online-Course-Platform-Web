<?php
/**
 * Edit Profile View
 */
$user = $data['user'];
$errors = $data['errors'] ?? [];
?>

<style>
    body {
        background: url('/e-learning-platform/assets/sources/home.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #f8f9fa;
        min-height: 100vh;
    }

    .transparent-card {
        background-color: rgba(34, 87, 143, 0.13);
        backdrop-filter: blur(3px);
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }

    .card-title,
    label {
        color: #ffffff;
    }

    .form-text,
    .text-muted {
        color: #e0e0e0;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-outline-secondary {
        color: #bbbbbb;
        border-color: #bbbbbb;
    }

    .btn-outline-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .img-thumbnail {
        background-color: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
    }

    input.form-control {
        background-color: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.3);
    }

    input.form-control:focus {
        background-color: rgba(255, 255, 255, 0.25);
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: none;
    }

    .invalid-feedback {
        color: #ffb3b3;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card transparent-card">
                <div class="card-body p-5">
                    <h2 class="card-title mb-4">Edit Profile</h2>

                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($errors['general']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="editProfileForm" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                       id="first_name" 
                                       name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
                                       required 
                                       minlength="2"
                                       maxlength="50">
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['first_name']); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="invalid-feedback">Please provide a valid first name.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                       id="last_name" 
                                       name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
                                       required 
                                       minlength="2"
                                       maxlength="50">
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['last_name']); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="invalid-feedback">Please provide a valid last name.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   style="color: black;"
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   disabled>
                            <div class="form-text">Email cannot be changed. Contact administrator if you need to change it.</div>
                        </div>

                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <?php if ($user['profile_picture']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                         alt="Current Profile Picture" 
                                         class="img-thumbnail" 
                                         style="max-height: 150px;">
                                    <p class="text-muted small mb-0">Current picture</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   class="form-control <?php echo isset($errors['profile_picture']) ? 'is-invalid' : ''; ?>" 
                                   id="profile_picture" 
                                   name="profile_picture" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <?php if (isset($errors['profile_picture'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['profile_picture']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Upload a profile picture (JPG, PNG, GIF, or WebP, max 5MB)</div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-white">Change Password (Optional)</h5>
                        <p class="text-white small">Leave blank if you don't want to change your password.</p>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" 
                                   class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   id="password" 
                                   name="password" 
                                   minlength="8"
                                   autocomplete="new-password">
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['password']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">Password must be at least 8 characters with uppercase, lowercase, and number.</div>
                            <?php endif; ?>
                            <div class="form-text">
                                Password must contain: at least 8 characters, one uppercase letter, one lowercase letter, and one number.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   autocomplete="new-password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($errors['confirm_password']); ?>
                                </div>
                            <?php else: ?>
                                <div class="invalid-feedback">Passwords must match.</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                            <a href="<?php echo BASE_URL; ?>profile" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editProfileForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    // Password validation (only if password is provided)
    password.addEventListener('input', function() {
        const value = this.value;
        
        if (value.length === 0) {
            this.setCustomValidity('');
            return;
        }
        
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
        if (password.value.length > 0) {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('');
        }
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
