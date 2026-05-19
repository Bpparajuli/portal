<style>
    /* Hover effects */
    .social-icons a:hover {
        transform: translateY(-3px);
        background: #820b5c !important;
        box-shadow: 0 5px 15px #820b5c25;
    }

    .list-unstyled li a:hover {
        color: #820b5c !important;
        padding-left: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        footer {
            text-align: center;
        }

        .social-icons {
            justify-content: center;
        }
    }
</style>
<footer class="bg-dark text-white pt-5 pb-4"
    style="background: linear-gradient(35deg, #1a0262 0%, #000000 40%,  #820b5c 100%);">
    <div class="container">
        <div class="row g-4">
            <!-- Company Info -->
            <div class="col-lg-4 col-md-6">
                <h4 class="mb-3 fw-bold">Idea Consultancy</h4>
                <p class="text-white-50 mb-3">Your trusted partner for educational and career guidance. Helping students
                    achieve their dreams since 2020.</p>
                <div class="social-icons mt-3">
                    <a href="https://www.facebook.com/ideaconsultancyservice" class="text-white me-3 d-inline-block"
                        target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/ideaconsultancy7/" class="text-white me-3 d-inline-block"
                        target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.tiktok.com/@ideaconsultancy" class="text-white me-3 d-inline-block"
                        target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="https://www.youtube.com/@Ideaconsultancy" class="text-white me-3 d-inline-block"
                        target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://twitter.com/IdeaConsultant7" class="text-white me-3 d-inline-block" target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/in/idea-consultancy-0a5908285/" class="text-white d-inline-block"
                        target="_blank"
                        style="transition: all 0.3s; background: rgba(255,255,255,0.1); width: 36px; height: 36px; line-height: 36px; text-align: center; border-radius: 50%;">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>


            <!-- Branch 1 -->
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-semibold"><i class="fas fa-building me-2"></i> Putalisadak Branch</h5>
                <div class="mb-2">
                    <i class="fas fa-map-marker-alt text-white-50 me-2"></i>
                    <span class="text-white-50">Putalisadak, Kathmandu, Nepal</span>
                </div>
                <div class="mb-2">
                    <i class="fas fa-phone text-white-50 me-2"></i>
                    <span class="text-white-50">+977 9761799575 / 01-4547547</span>
                </div>
                <div>
                    <i class="fas fa-envelope text-white-50 me-2"></i>
                    <span class="text-white-50">info@ideaconsultancy.com</span>
                </div>
            </div>

            <!-- Branch 2 -->
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-semibold"><i class="fas fa-building me-2"></i> New Baneshwor Branch</h5>
                <div class="mb-2">
                    <i class="fas fa-map-marker-alt text-white-50 me-2"></i>
                    <span class="text-white-50">New Baneshwor, Kathmandu, Nepal</span>
                </div>
                <div class="mb-2">
                    <i class="fas fa-phone text-white-50 me-2"></i>
                    <span class="text-white-50">+977 9705547547 / 01-5318333</span>
                </div>
                <div>
                    <i class="fas fa-envelope text-white-50 me-2"></i>
                    <span class="text-white-50">baneshwor@ideaconsultancy.com</span>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <hr class="mt-4 mb-3" style="border-color: rgba(255,255,255,0.1);">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-white-50 small">© {{ date('Y') }} Idea Consultancy. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <p class="mb-0 text-white-50 small">
                    <i class="fas fa-heart text-danger"></i> Designed with care for students
                </p>
            </div>
        </div>
    </div>
</footer>
