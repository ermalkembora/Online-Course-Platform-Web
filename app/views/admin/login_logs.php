<?php
/**
 * Login Attempts Logs View
 */
$logs = $data['logs'] ?? [];
$pagination = $data['pagination'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Login Attempts Logs</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>admin/users_index" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock"></i> Login Attempts (<?php echo number_format($pagination['total']); ?> total)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">
                        No login attempts found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                    <th>User Agent</th>
                                    <th>Attempted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <?php if ($log['user_name']): ?>
                                                <?php echo htmlspecialchars($log['user_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['user_email']): ?>
                                                <?php echo htmlspecialchars($log['user_email']); ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($log['email']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                        </td>
                                        <td>
                                            <?php if ($log['success']): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Success
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i> Failed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                                <?php 
                                                $ua = $log['user_agent'] ?? '';
                                                echo htmlspecialchars(mb_substr($ua, 0, 50));
                                                if (strlen($ua) > 50) echo '...';
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?php echo date('M d, Y H:i:s', strtotime($log['attempted_at'])); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Login logs pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/loginLogs?page=<?php echo $pagination['current_page'] - 1; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php if ($i == $pagination['current_page']): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo BASE_URL; ?>admin/loginLogs?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/loginLogs?page=<?php echo $pagination['current_page'] + 1; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <p class="text-center text-muted mt-2">
                            Showing page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?> 
                            (<?php echo number_format($pagination['total']); ?> total records)
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

