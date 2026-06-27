<?php
include_once "header.php"; // Include the navbar
?>

<div class="container py-5">
    <div class="p-5 bg-light rounded">
        <h2 class="text-center mb-4">Contact Us</h2>
        <p class="lead text-center">Have questions or need help? Feel free to reach out to us.</p>

        <form action="send_message.php" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Your Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Message</button>
        </form>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>

<?php
include_once "footer.php"; // Include the footer
?>
