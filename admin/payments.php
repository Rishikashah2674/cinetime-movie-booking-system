<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';

// Fetch payments with user and booking details
$payments = $conn->query("
    SELECT p.payment_id, p.razorpay_order_id, p.amount, p.status, p.paid_at, 
           u.name as user_name, u.email
    FROM payments p
    LEFT JOIN user u ON p.user_id = u.user_id
    WHERE p.status = 'Success' OR p.status = 'success'
    ORDER BY p.paid_at DESC
");
?>

<div class="card">
    <div class="card-header">
        Successful Payment Transactions
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($payments && $payments->num_rows > 0): ?>
                        <?php while($pay = $payments->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $pay['payment_id'] ?></td>
                            <td class="font-monospace text-secondary"><?= htmlspecialchars($pay['razorpay_order_id'] ?? 'N/A') ?></td>
                            <td>
                                <div><?= htmlspecialchars($pay['user_name'] ?? 'Guest') ?></div>
                                <div class="text-secondary small"><?= htmlspecialchars($pay['email'] ?? '') ?></div>
                            </td>
                            <td class="text-success fw-bold">₹<?= number_format($pay['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Success</span>
                            </td>
                            <td><?= date('d M Y, h:i A', strtotime($pay['paid_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No successful payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
