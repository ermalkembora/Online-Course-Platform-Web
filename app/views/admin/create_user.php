<?php
/**
 * Admin Create User View
 */
$email = $data['email'] ?? '';
$first_name = $data['first_name'] ?? '';
$last_name = $data['last_name'] ?? '';
$errors = $data['errors'] ?? [];
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Add New User</h2>
                    <a href="<?php echo BASE_URL; ?>users/manageUsers" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="createUserForm" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" 
                                   class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?php echo htmlspecialchars($first_name); ?>" 
                                   required 
                                   minlength="2">
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" 
                                   class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?php echo htmlspecialchars($last_name); ?>" 
                                   required 
                                   minlength="2">
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" 
                               class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                        <?php else: ?>
                            <div class="form-text">
                                Password must contain: at least 8 characters, one uppercase letter, one lowercase letter, and one number.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Create User
                        </button>
                        <a href="<?php echo BASE_URL; ?>users/manageUsers" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createUserForm');
    const password = document.getElementById('password');
    
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

