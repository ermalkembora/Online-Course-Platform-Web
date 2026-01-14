<?php
/**
 * Admin Users Index View
 */
$users = $data['users'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>users/createUser" class="btn btn-success me-2">
                    <i class="bi bi-plus-circle"></i> Add New User
                </a>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        No users found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th>Email Verified</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                            <?php if ($user['profile_picture']): ?>
                                                <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                                     alt="Profile" 
                                                     class="rounded-circle ms-2" 
                                                     style="width: 30px; height: 30px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['roles']): ?>
                                                <?php 
                                                $roles = explode(', ', $user['roles']);
                                                foreach ($roles as $role): 
                                                    $badgeClass = $role === 'admin' ? 'bg-danger' : ($role === 'instructor' ? 'bg-primary' : 'bg-secondary');
                                                ?>
                                                    <span class="badge <?php echo $badgeClass; ?> me-1"><?php echo htmlspecialchars($role); ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No roles</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['email_verified']): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Not Verified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>users/profile/<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-info me-1">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>users/editUser/<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-primary me-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>users/deleteUser/<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone!');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

