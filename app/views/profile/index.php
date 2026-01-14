<?php
/**
 * User Profile View
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
        backdrop-filter: blur(4px);
        border-radius: 18px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.45);
    }

    /* Remove Bootstrap white table background */
    .table,
    .table th,
    .table td {
        background-color: transparent !important;
        color: #e0e0e0;
    }

    /* Title */
    .card-title {
        color: #ffffff !important;
        font-weight: 600;
    }

    .profile-avatar {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid rgba(255,255,255,0.35);
        margin: 0 auto;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-initials {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: #ffffff;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card transparent-card">
                <div class="card-body p-5">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title mb-0">
                            <?= $isOwnProfile ? 'My Profile' : 'User Profile'; ?>
                        </h2>

                        <?php if ($isOwnProfile): ?>
                            <a href="<?= BASE_URL ?>profile/edit" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </a>
                        <?php elseif ($isAdmin): ?>
                            <a href="<?= BASE_URL ?>users/editUser/<?= $user['id'] ?>" class="btn btn-primary">
                                Edit User
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- CONTENT -->
                    <div class="row align-items-center">

                        <!-- AVATAR -->
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <div class="profile-avatar">
                                    <img src="<?= BASE_URL ?>uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
                                </div>
                            <?php else: ?>
                                <div class="profile-initials">
                                    <?= strtoupper($user['first_name'][0] . $user['last_name'][0]); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- DETAILS -->
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="35%">Full Name:</th>
                                    <td><?= $fullName ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email Verified:</th>
                                    <td>
                                        <?php if ($user['email_verified']): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Verified</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Member Since:</th>
                                    <td><?= date('F d, Y', strtotime($user['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Last Activity:</th>
                                    <td>
                                        <?= $user['last_activity']
                                            ? date('F d, Y H:i', strtotime($user['last_activity']))
                                            : '<span class="text-muted">Never</span>' ?>
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
