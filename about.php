<?php include 'header.php'; ?>
<style>
    .custom-about-container {
        background-color: #f5e8c7;
        padding: 20px;
        border-radius: 5px;
        max-width: 600px;
        margin: 0 auto 20px;
    }
    .custom-tab {
        display: inline-block;
        padding: 8px 16px;
        margin-right: 10px;
        border-radius: 5px;
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s; /* Smooth transition for hover effect */
    }
    .custom-tab-mission { background-color: #279ebd; } /* Base color */
    .custom-tab-vision { background-color: #279ebd; } /* Base color */
    .custom-tab-mission:hover, .custom-tab-vision:hover {
        background-color: #1e7a8d; /* Darker shade on hover, similar to btn-primary hover */
    }
    .custom-purpose {
        font-size: 1.5rem;
        font-weight: bold;
        color: #4682b4;
        margin-bottom: 10px;
    }
    .custom-text {
        color: #666;
        margin-bottom: 15px;
        display: none;
    }
    .custom-text.active { display: block; }
 
</style>
<!-- Page Header Start -->
<div class="container-fluid page-header mb-5 wow fadeIn" data-wow-delay="0.1s">
    <div class="container text-center">
        <h1 class="display-4 text-white animated slideInDown mb-4">About Shivanantham Foundation</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Home</a></li>
                <li class="breadcrumb-item text-primary active" aria-current="page">About</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header End -->
<!-- About Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <img class="img-fluid rounded" src="img/about-1.jpg" alt="About Shivanantham Foundation">
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="h-100">
                    <div class="d-inline-block rounded-pill bg-secondary text-primary py-1 px-3 mb-3">Who We Are</div>
                    <h2 class="mb-4">Empowering Communities, Transforming Lives</h2>
                    <p class="mb-4">Shivanantham Foundation is a non-profit organization dedicated to uplifting underprivileged communities through education, healthcare, and sustainable development. Our mission is to create lasting change by empowering individuals and families to build a brighter future.</p>
                    <!-- Mission and Vision Container Start -->
                    <div class="custom-about-container">
                        <div class="mb-3">
                            <span class="custom-tab custom-tab-mission" onclick="showContent('mission')">Mission</span>
                            <span class="custom-tab custom-tab-vision" onclick="showContent('vision')">Vision</span>
                        </div>
                        <div class="custom-purpose">OUR PURPOSE</div>
                        <p class="custom-text active" id="mission-text">To empower underprivileged communities by providing access to quality education, healthcare, and sustainable opportunities, fostering self-reliance and hope for a better future.</p>
                        <p class="custom-text" id="vision-text">A world where every individual has the opportunity to thrive, with equal access to education, health, and sustainable resources, creating vibrant and empowered communities.</p>
                     </div>
                    <!-- Mission and Vision Container End -->
                    <ul>
                        <li>Providing scholarships and educational resources for children</li>
                        <li>Organizing free health camps and awareness programs</li>
                        <li>Supporting women’s empowerment and vocational training</li>
                        <li>Promoting environmental sustainability and clean water initiatives</li>
                    </ul>
                    <p class="mb-4">Join us in our journey to make a difference. Together, we can transform lives and create hope for generations to come.</p>
                    <a class="btn btn-primary py-2 px-3 me-3" href="donation.php">
                        Donate Now
                        <div class="d-inline-flex btn-sm-square bg-white text-primary rounded-circle ms-2">
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                    <a class="btn btn-outline-primary py-2 px-3" href="volunteer.php">
                        Become a Volunteer
                        <div class="d-inline-flex btn-sm-square bg-primary text-white rounded-circle ms-2">
                            <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->
<?php include 'footer.php'; ?>
<script>
    function showContent(type) {
        const missionText = document.getElementById('mission-text');
        const visionText = document.getElementById('vision-text');
        if (type === 'mission') {
            missionText.classList.add('active');
            visionText.classList.remove('active');
        } else if (type === 'vision') {
            visionText.classList.add('active');
            missionText.classList.remove('active');
        }
    }
</script>