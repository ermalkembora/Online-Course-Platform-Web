<?php
/**
 * Third-Party API Logs View
 */
$logs = $data['logs'] ?? [];
$pagination = $data['pagination'] ?? [];
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Third-Party API Logs</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>admin/users_index" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-cloud"></i> API Communication Logs (<?php echo number_format($pagination['total']); ?> total)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info">
                        No third-party API logs found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service</th>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Request Type</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Status Code</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars(strtoupper($log['service_name'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['user_name']): ?>
                                                <?php echo htmlspecialchars($log['user_name']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($log['user_email']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['course_title']): ?>
                                                <?php echo htmlspecialchars($log['course_title']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($log['request_type'] ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($log['short_message'] ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'secondary';
                                            if ($log['status'] === 'success') $statusClass = 'success';
                                            elseif ($log['status'] === 'error') $statusClass = 'danger';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($log['status'] ?? 'unknown')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($log['status_code']): ?>
                                                <span class="badge bg-<?php echo $log['status_code'] >= 400 ? 'danger' : 'success'; ?>">
                                                    <?php echo $log['status_code']; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-info" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#logModal<?php echo $log['id']; ?>">
                                                <i class="bi bi-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal for log details -->
                                    <div class="modal fade" id="logModal<?php echo $log['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Log Details #<?php echo $log['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Service:</strong> <?php echo htmlspecialchars($log['service_name']); ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Request Type:</strong> <?php echo htmlspecialchars($log['request_type'] ?? '-'); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($log['transaction_id']): ?>
                                                        <div class="mb-3">
                                                            <strong>Transaction ID:</strong>
                                                            <code><?php echo htmlspecialchars($log['transaction_id']); ?></code>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($log['request_data']): ?>
                                                        <div class="mb-3">
                                                            <strong>Request Data:</strong>
                                                            <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"><?php 
                                                                $requestData = json_decode($log['request_data'], true);
                                                                echo htmlspecialchars(json_encode($requestData, JSON_PRETTY_PRINT));
                                                            ?></pre>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($log['response_data']): ?>
                                                        <div class="mb-3">
                                                            <strong>Response Data:</strong>
                                                            <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"><?php 
                                                                $responseData = json_decode($log['response_data'], true);
                                                                echo htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT));
                                                            ?></pre>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($log['error_message']): ?>
                                                        <div class="alert alert-danger">
                                                            <strong>Error:</strong> <?php echo htmlspecialchars($log['error_message']); ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>IP Address:</strong> <?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Created:</strong> <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav aria-label="Third-party logs pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/thirdPartyLogs?page=<?php echo $pagination['current_page'] - 1; ?>">
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
                                            <a class="page-link" href="<?php echo BASE_URL; ?>admin/thirdPartyLogs?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo BASE_URL; ?>admin/thirdPartyLogs?page=<?php echo $pagination['current_page'] + 1; ?>">
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

