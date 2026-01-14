<?php
echo '<pre style="color:white; background:black; padding:10px;">';
echo 'isOwnProfile = ';
var_dump($isOwnProfile);
echo 'session user id = ';
var_dump($_SESSION['user_id'] ?? null);
echo 'profile user id = ';
var_dump($user['id'] ?? null);
echo '</pre>';
?>

<?php
/**
 * User Profile View
 * Can be used by normal users (own profile) or admins (any profile)
 */
$user = $data['user'];
$userRoles = $data['userRoles'] ?? [];
$isAdmin = $data['isAdmin'] ?? false;
$isOwnProfile = $data['isOwnProfile'] ?? false;
$fullName = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
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
    th,
    label {
        color: #ffffff;
    }

    td,
    .text-muted {
        color: #e0e0e0;
    }

    .table-borderless th {
        color: #ffffffff;
        font-weight: 500;
    }

    .badge {
        opacity: 0.95;
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

    a {
        color: #66b2ff;
    }

    a:hover {
        color: #99ccff;
    }

    .profile-initials {
        background-color: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(4px);
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7 col-xl-6">
            <div class="card transparent-card">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title mb-0">
                            <?php echo $isOwnProfile ? 'My Profile' : 'User Profile'; ?>
                        </h2>
                        <div>
                            <?php if ($isOwnProfile): ?>
                                <a href="<?php echo BASE_URL; ?>users/edit" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Profile
                                </a>
                            <?php elseif ($isAdmin): ?>
                                <a href="<?php echo BASE_URL; ?>users/editUser/<?php echo $user['id']; ?>" class="btn btn-primary me-2">
                                    <i class="bi bi-pencil"></i> Edit User
                                </a>
                                <a href="<?php echo BASE_URL; ?>users/manageUsers" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Users
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row align-items-start">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <?php if ($user['profile_picture']): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                     alt="Profile Picture"
                                     class="img-thumbnail rounded-circle border border-dark border-1"
                                     style="width: 200px; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="profile-initials rounded-circle d-inline-flex align-items-center justify-content-center"
                                     style="width: 200px; height: 200px; font-size: 4rem;">
                                    <span class="text-white">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <table class="" style="background-color: rgba(34, 87, 143, 0.13);
                                                   backdrop-filter: blur(3px);">
                                <tr>
                                    <th width="30%">Full Name:</th>
                                    <td><?php echo $fullName; ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <?php if ($isAdmin && !empty($userRoles)): ?>
                                    <tr>
                                        <th>Roles:</th>
                                        <td>
                                            <?php foreach ($userRoles as $role): 
                                                $badgeClass = $role === 'admin' ? 'bg-danger' : ($role === 'instructor' ? 'bg-primary' : 'bg-secondary');
                                            ?>
                                                <span class="badge <?php echo $badgeClass; ?> me-1">
                                                    <?php echo htmlspecialchars(ucfirst($role)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Email Verified:</th>
                                    <td>
                                        <?php if ($user['email_verified']): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Verified</span>
                                            <?php if ($isOwnProfile): ?>
                                                <a href="<?php echo BASE_URL; ?>users/verifyEmail" class="btn btn-sm btn-outline-primary ms-2">
                                                    Verify Now
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Member Since:</th>
                                    <td><?php echo date('F d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Activity:</th>
                                    <td>
                                        <?php if ($user['last_activity']): ?>
                                            <?php echo date('F d, Y H:i', strtotime($user['last_activity'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>