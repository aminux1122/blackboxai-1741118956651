</main><!-- End Main Content Container -->

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Contact Info -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><?php echo translate('contact_us'); ?></h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Rue Mohammed V, Casablanca</p>
                    <p><i class="fas fa-phone me-2"></i> +212 522 123 456</p>
                    <p><i class="fas fa-envelope me-2"></i> contact@salon-beaute.ma</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><?php echo translate('quick_links'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/services.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i><?php echo translate('our_services'); ?>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/rdv.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i><?php echo translate('book_appointment'); ?>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/produits.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i><?php echo translate('our_products'); ?>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/gallery.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i><?php echo translate('gallery'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/contact.php" class="text-light text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i><?php echo translate('contact'); ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Opening Hours -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><?php echo translate('opening_hours'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="far fa-clock me-2"></i>
                            <?php echo translate('monday_friday'); ?>: 9:00 - 20:00
                        </li>
                        <li class="mb-2">
                            <i class="far fa-clock me-2"></i>
                            <?php echo translate('saturday'); ?>: 9:00 - 18:00
                        </li>
                        <li>
                            <i class="far fa-clock me-2"></i>
                            <?php echo translate('sunday'); ?>: <?php echo translate('closed'); ?>
                        </li>
                    </ul>

                    <!-- Newsletter -->
                    <div class="mt-4">
                        <h5 class="mb-3"><?php echo translate('newsletter'); ?></h5>
                        <form action="<?php echo SITE_URL; ?>/newsletter.php" method="POST" class="d-flex">
                            <input type="email" name="email" class="form-control me-2" placeholder="<?php echo translate('your_email'); ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row mt-4">
                <div class="col-12">
                    <hr class="bg-light">
                    <p class="text-center mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - 
                        <?php echo translate('all_rights_reserved'); ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="<?php echo SITE_URL; ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="<?php echo SITE_URL; ?>/vendor/jquery/jquery.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="<?php echo SITE_URL; ?>/vendor/sweetalert2/sweetalert2.all.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Additional JS based on page -->
    <?php if(isset($additional_js)): ?>
        <?php foreach($additional_js as $js): ?>
            <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
