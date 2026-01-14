<?php
/**
 * Email Verification Success View
 */
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-success">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                
                <h2 class="card-title text-success mb-3">Email Verified Successfully!</h2>
                
                <p class="lead mb-4">
                    Your email address <strong><?php echo htmlspecialchars($email); ?></strong> has been verified.
                </p>
                
                <p class="text-muted mb-4">
                    You can now log in to your account and start learning!
                </p>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>users/login" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Go to Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect to login after 5 seconds (optional)
setTimeout(function() {
    // Uncomment to enable auto-redirect:
    // window.location.href = '<?php echo BASE_URL; ?>auth/login';
}, 5000);
</script>

