<?php
//  Footer 
?>

<footer class="site-footer py-5">
    <div class="container">

        <div class="row g-4 mb-4">

            <div class="col-12 col-sm-6 col-lg-3">
                <p class="footer-brand mb-3">SkinCareBeauty</p>
                <p class="footer-tagline">
                    Des produits naturels et éthiques pour sublimer votre peau au quotidien.
                </p>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <h2 class="footer-heading mb-3">Boutique</h2>
                <ul class="footer-nav list-unstyled mb-0">
                    <li><a href="<?= url('/produits?filter=nouveautes') ?>">Nouveautés</a></li>
                    <li><a href="<?= url('/produits?filter=bestsellers') ?>">Bestsellers</a></li>
                    <li><a href="<?= url('/produits?filter=promotions') ?>">Promotions</a></li>
                    <li><a href="<?= url('/produits?filter=coffrets') ?>">Coffrets</a></li>
                </ul>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <h2 class="footer-heading mb-3">Aide</h2>
                <ul class="footer-nav list-unstyled mb-0">
                    <li><a href="<?= url('/contact.php') ?>">Contact</a></li>
                    <li><a href="#">Livraison</a></li>
                    <li><a href="#">Retours</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <h2 class="footer-heading mb-3">Suivez-nous</h2>
                <div class="d-flex gap-2">
                    <a href="https://www.instagram.com/skincarebeauty" class="footer-social" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.facebook.com/skincarebeauty" class="footer-social" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
                    </a>
                    <a href="https://www.twitter.com/skincarebeauty" class="footer-social" aria-label="Twitter" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

        </div>

        <hr class="footer-divider my-3">

        <p class="footer-copy text-center mb-0">
            &copy; <?= date('Y') ?> SkinCareBeauty. Tous droits réservés.
        </p>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('/public/scripts/cart_ajax.js') ?>"></script>
</body>
</html>