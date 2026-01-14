<?php
/**
 * Email Verification Failed View
 */
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-danger">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                </div>
                
                <h2 class="card-title text-center text-danger mb-3">Verification Failed</h2>
                
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error ?? 'Invalid or expired verification code.'); ?>
                </div>
                
                <p class="text-muted mb-4">
                    The verification code you entered is invalid or has expired. 
                    Please check your email for the correct code or request a new one.
                </p>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>users/verifyEmail" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise"></i> Try Again
                    </a>
                    
                    <form method="POST" action="<?php echo BASE_URL; ?>users/resendVerification" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId ?? ''); ?>">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-envelope-plus"></i> Resend Verification Code
                        </button>
                    </form>
                    
                    <a href="<?php echo BASE_URL; ?>users/register" class="btn btn-outline-secondary">
                        <i class="bi bi-person-plus"></i> Register Again
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</div>

