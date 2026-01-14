<?php
/**
 * Checkout Confirmation View
 */
$course = $data['course'];
$user = $data['user'];
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="card-title mb-4">Confirm Purchase</h2>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                        <p class="text-muted">
                            <i class="bi bi-person"></i> Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?>
                        </p>
                        <p><?php echo nl2br(htmlspecialchars(mb_substr($course['description'], 0, 300))); ?>
                           <?php if (strlen($course['description']) > 300) echo '...'; ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <?php if ($course['thumbnail']): ?>
                            <img src="<?php echo BASE_URL; ?>uploads/courses/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                 class="img-fluid rounded" 
                                 alt="<?php echo htmlspecialchars($course['title']); ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Order Summary</h5>
                        <table class="table">
                            <tr>
                                <td>Course:</td>
                                <td class="text-end"><?php echo htmlspecialchars($course['title']); ?></td>
                            </tr>
                            <tr>
                                <td>Price:</td>
                                <td class="text-end"><strong>$<?php echo number_format($course['price'], 2); ?></strong></td>
                            </tr>
                            <tr class="table-active">
                                <td><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($course['price'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Payment Method</h5>
                        <p class="text-muted">
                            <i class="bi bi-paypal"></i> You will be redirected to PayPal to complete your payment securely.
                        </p>
                        <p class="small text-muted">
                            <i class="bi bi-shield-check"></i> Your payment is processed securely by PayPal. We never store your payment details.
                        </p>
                    </div>
                </div>

                <form method="POST" action="<?php echo BASE_URL; ?>checkout/payWithPaypal/<?php echo $course['id']; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?php echo BASE_URL; ?>courses/show/<?php echo $course['id']; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Course
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-paypal"></i> Pay with PayPal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

