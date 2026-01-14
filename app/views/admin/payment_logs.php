<?php
/**
 * Payment Logs View
 */
$logs = $data['logs'] ?? [];
$pagination = $data['pagination'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payment Logs</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>admin/users_index" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-credit-card"></i> Payment Transactions (<?php echo number_format($pagination['total']); ?> total)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">
                        No payment records found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Method</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($log['user_name']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['user_email']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($log['course_title']); ?>
                                        </td>
                                        <td>
                                            <code class="small"><?php echo htmlspecialchars(mb_substr($log['transaction_id'], 0, 20)); ?>...</code>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                <?php echo strtoupper($log['currency'] ?? 'USD'); ?> 
                                                <?php echo number_format($log['amount'], 2); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'secondary';
                                            if ($log['status'] === 'succeeded') $statusClass = 'success';
                                            elseif ($log['status'] === 'failed' || $log['status'] === 'cancelled') $statusClass = 'danger';
                                            elseif ($log['status'] === 'pending') $statusClass = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($log['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['payment_method']): ?>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $log['payment_method']))); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Payment logs pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/paymentLogs?page=<?php echo $pagination['current_page'] - 1; ?>">
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
                                            <a class="page-link" href="<?php echo BASE_URL; ?>admin/paymentLogs?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/paymentLogs?page=<?php echo $pagination['current_page'] + 1; ?>">
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

