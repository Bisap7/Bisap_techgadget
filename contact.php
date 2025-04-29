<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // In a real application, you would send an email here
    // For this demo, we'll just store it in session
    $_SESSION['message'] = 'Thank you for your message! We will get back to you soon.';
    $_SESSION['message_type'] = 'success';
    redirect('contact.php');
}

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Contact Us</h2>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Send Us a Message</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Our Information</h5>
                </div>
                <div class="card-body">
                    <h5><i class="bi bi-geo-alt"></i> Address</h5>
                    <p>123 Tech Street<br>Gadget City, GC 12345</p>
                    
                    <h5 class="mt-4"><i class="bi bi-telephone"></i> Phone</h5>
                    <p>+1 (123) 456-7890</p>
                    
                    <h5 class="mt-4"><i class="bi bi-envelope"></i> Email</h5>
                    <p>info@techgadgetstore.com</p>
                    
                    <h5 class="mt-4"><i class="bi bi-clock"></i> Business Hours</h5>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                    Saturday: 10:00 AM - 4:00 PM<br>
                    Sunday: Closed</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>