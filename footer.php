</div> <!-- Closing container div from header -->

<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Tech Gadget Store</h5>
                <p>Your one-stop shop for the latest tech gadgets and accessories.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white">Home</a></li>
                    <li><a href="products.php" class="text-white">Products</a></li>
                    <li><a href="about.php" class="text-white">About Us</a></li>
                    <li><a href="contact.php" class="text-white">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <address>
                    <p><i class="bi bi-geo-alt"></i> 123 Tech Street, Gadget City</p>
                    <p><i class="bi bi-envelope"></i> info@techgadgetstore.com</p>
                    <p><i class="bi bi-phone"></i> +1 (123) 456-7890</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?= date('Y') ?> Tech Gadget Store. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Cart quantity update
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (this.classList.contains('minus')) {
                value = value > 1 ? value - 1 : 1;
            } else {
                value = value + 1;
            }
            
            input.value = value;
        });
    });
</script>
</body>
</html>