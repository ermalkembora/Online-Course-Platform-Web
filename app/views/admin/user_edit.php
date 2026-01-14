<?php
/**
 * Admin User Edit View
 */
$user = $data['user'];
$roles = $data['roles'] ?? [];
$userRoles = $data['userRoles'] ?? [];
$errors = $data['errors'] ?? [];
$currentRoleId = null;

// Get current role ID (assuming single role for simplicity)
if (!empty($userRoles)) {
    $roleName = $userRoles[0];
    foreach ($roles as $role) {
        if ($role['name'] === $roleName) {
            $currentRoleId = $role['id'];
            break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit User: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <a href="<?php echo BASE_URL; ?>admin/users_index" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>

                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <!-- User Details Form -->
                <form method="POST" id="userForm" class="needs-validation mb-4" novalidate>
                    <input type="hidden" name="action" value="update">
                    
                    <h5 class="mb-3">User Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" 
                                   class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" 
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
                                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                </form>

                <hr class="my-4">

                <!-- Role Management -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="update_role">
                    
                    <h5 class="mb-3">User Role</h5>
                    
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Select Role</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <option value="">Select a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" 
                                        <?php echo ($currentRoleId == $role['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($role['name'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check"></i> Update Role
                    </button>
                </form>

                <hr class="my-4">

                <!-- Danger Zone -->
                <div class="border border-danger rounded p-3">
                    <h5 class="text-danger mb-3">Danger Zone</h5>
                    
                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone!');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

