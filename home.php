<?php
// START GLOBAL VARIABLES
session_start();

if (!isset($_SESSION['user-session']))
  header("Location: index.php");

$dbhost = 'localhost';
// username ng mysql. normally root to since xampp gamit mo goods na
$dbusername = 'root';
// password ng db. kung meron ka nilagay na password if ever na tinanong ni xampp nung nag install ka
$dbpassword = '';
// name ng database na kokonektahan neto. hindi to database table, database mismo to
$dbname = 'yayawautorepairshop';
// initialize connection, eto gagamitin mo sa twing mag access ka db
$connection = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);

// check lang kung successful yung connection mo
if (mysqli_connect_error()) {
    $errorMessage = 'Failed to connect to database';
}

// Clear cart items if the clear_cart parameter is set
if (isset($_GET['clear_cart'])) {
  $user_id = intval($_SESSION['user-session']);
  mysqli_query($connection, "DELETE FROM cart_items WHERE users_id = $user_id");
}

else {
    $result = mysqli_query($connection, "SELECT * FROM items ORDER BY description");
    $data = [];

    while (($row = mysqli_fetch_assoc($result))) {
        $data[] = $row;
    }
}

if (isset($_POST['send_message'])) {
  
  
  $name = mysqli_real_escape_string($connection, $_POST['name']);
  $user_email = mysqli_real_escape_string($connection, $_POST['emails']);
  $subject = mysqli_real_escape_string($connection, $_POST['subject']);
  $message = mysqli_real_escape_string($connection, $_POST['message']);
  
  // Insert into database using prepared statement
  $insert_query = "INSERT INTO contact (name, user_email, subject, message) VALUES (?, ?, ?, ?)";
  
  // Prepare the statement
  $stmt = mysqli_stmt_init($connection);
  if (mysqli_stmt_prepare($stmt, $insert_query)) {
    // Bind parameters and execute the statement
    mysqli_stmt_bind_param($stmt, "ssss", $name, $user_email, $subject, $message);
    
    if (mysqli_stmt_execute($stmt)) {
      // Data inserted successfully
      $_SESSION['message_sent'] = true; // Set session variable for success
      header("Location: contact_thankyou.php"); // Redirect to thank you page
      exit(); // Ensure no further code execution on this page
  } else {
      echo '<p class="error-message">Oops! Something went wrong. Please try again later.</p>';
  }
    
    // Close statement
    mysqli_stmt_close($stmt);
  } else {
    echo '<p class="error-message">Error: ' . mysqli_error($connection) . '</p>';
  }

 }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet"
    />

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="home.css" />
    <title>Yayaw Auto Repair Shop</title>
  </head>
  <body> 
    <header class="header">
      <nav>
        <div class="navigation__bar">
          <div class="logo navigation__logo">
            <a href="#"><img src="image/logo.jpg" alt="logo" /></a>
          </div>
          <div class="navigation__menu__btn" id="menu-btn">
            <i class="ri-menu-3-line"></i>   <!-- for cp type yung 3 lines sa upper right -->
          </div>
        </div>
        <ul class="navigation__links" id="navigation-links">
          <li><a href="#home">HOME</a></li>
          <li><a href="#about">ABOUT</a></li>
          <li><a href="#service">SERVICE</a></li>
          <li><a href="#products">PRODUCT</a></li>
          <li><a href="#contact">CONTACT</a></li>
          <a target="login" class="btn" href="logout.php">Log-Out</a>  
          
          <a target="login" class="btn" href="cart.php">Shop Now</a>
        </ul>
      </nav>
      <div class="section__container header__container" id="home">
        <div class="header__content">
          <h1>WELCOME TO YAYAW REPAIR SHOP</h1>
          
        </div>
      </div>
    </header>

    <section class="banner__container">
      <div class="banner__card">
        <h4>Drive with Confidence, We’ll Handle the Rest!</h4>
      </div>
      <div class="banner__card">
        <h4>Quality Repairs, Honest Service.</h4>
      </div>
      <div class="banner__image">
        <img src="image/a2ca42b0-c862-45f2-8954-0b64c5362deb.jfif" alt="banner" />
      </div>
    </section>

    <section class="section__container experience__container" id="about">
      <div class="experience__image">
        <img src="image/experience header container.jfif" alt="experience" />
      </div>
      <div class="experience__content">
        <p class="section__subheader">been so many years in the field of auto repair shop </p>
        <h2 class="section__header">
          We Have 10 Years Of Experience
        </h2>
        <p class="section__description">
        Yayaw Auto Repair Shop was founded on year 2014. We offer different services like Engine Overhauling,
         Transmission Overhauling, Diagnostic Service, Brake Repair, Change Oil, and Engine Sensor.
          All of the supplies that you need based on the service we offer is available in our shop.
           Our experienced technicians ensure top-quality service for every vehicle. Customer satisfaction is our top priority,
            and we strive to provide efficient and reliable repairs.
        </p>
        <button class="btn">Scroll down</button>
      </div>
    </section>

    <section class="service" id="service">
      <div class="section__container service__container">
        <p class="section__subheader">WHY CHOOSE YAYAW AUTO REPAIR SHOP?</p>
        <h2 class="section__header">Speedy Fix Auto Repair</h2>
        <p class="section__description">

          Why You Choose Yayaw Auto Repair Shop? Because Our commitment to excellence extends beyond just repairs.
           We pride ourselves on delivering outstanding customer service, transparent communication, and fair pricing.
            Whether you're coming in for routine maintenance or a complex repair, you can trust that your vehicle is in capable hands at Yayaw Auto Repair

        </p>
        <div class="service__grid">
          <div class="service__card">
            <img src="image/Transmission Overhauling (automatic & manual).jfif" alt="service" />
            <h4>Transmission Overhauling (automatic & manual)</h4>
            <p>
                Transmission overhauling for both manual and automatic transmissions
                is crucial for several reasons, emphasizing safety, performance, reliability,
                 and cost-effectiveness

            </p>
          </div>
          <div class="service__card">
            <img src="image/Engine Overhauling (gasoline & diesel).jfif" alt="service" />
            <h4>Engine Overhauling (gasoline & diesel)</h4>
            <p>
                Engine overhauling for gasoline and diesel engines is vital for
                maintaining and improving engine performance, efficiency, and reliability.
                It enhances fuel economy, extends engine life, ensures compliance with
                 emissions standards, and contributes to a smoother and more enjoyable driving experience. 
            </p>
          </div>
          <div class="service__card">
            <img src="image/Brake services & abs repair.jpg" alt="service" />
            <h4>Brake Service & abs Repair</h4>
            <p>
                Regular brake cleaning helps ensure that your brakes engage every time you press on the brake pedal.
            </p>
          </div>
          <div class="service__card">
            <img src="image/Diagnostic servo & engine sensor.jpg" alt="service" />
            <h4>Diagnostic servo & Engine Sensor</h4>
            <p>
                Facilitate early fault detection, optimize system performance, ensure compliance with regulations,
                 and enhance overall vehicle safety and efficiency. Regular monitoring, calibration, and maintenance
                  of these components are essential practices for maximizing vehicle performance and longevity.

            </p>
          </div>
          <div class="service__card">
            <img src="image/change oil.jpg" alt="service" />
            <h4>Change Oil</h4>
            <p>
                Regular engine Change Oil not only keeps your engine clean, it also improves its performance. Oil naturally carries dirt, debris, and various other particles as it flows into the engine.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Products Section with Swiper Integration -->
    <section class="products py-5" id="products">
  <div class="container">
    <p class="section__subheader text-center">OUR PRODUCTS</p>
    <h2 class="section__header text-center">Quality Products for Your Vehicle</h2>

    <div id="productCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
      <div class="carousel-inner">
        <?php 
        $totalItems = count($data);
        for ($i = 0; $i < $totalItems; $i += 2): 
        ?>
          <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
            <div class="row">
              <?php for ($j = 0; $j < 2 && $i + $j < $totalItems; $j++): ?>
                <div class="col-md-6">
                  <div class="card product__card mb-4">
                    <?php 
                      // echo "<pre>";
                      // print_r($data[$i + $j]);
                      // exit;
                    ?>
                    <img src="<?php echo $data[$i + $j]['imageurl']; ?>" class="card-img-top" alt="<?php echo $data[$i + $j]['description']; ?>" />
                    <div class="card-body text-center">
                      <h5 class="card-title"><?php echo $data[$i + $j]['description']; ?></h5>
                      <span class="product__price">₱<?php echo $data[$i + $j]['price']; ?></span>
                    </div>
                  </div>
                </div>
              <?php endfor; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <a class="carousel-control-prev" href="#productCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#productCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </div>
</section>

      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>
</section>

    
<section class="contact" id="contact">
  <div class="section__container contact__container">
  
  <!-- Contact Form and Information -->
  <div class="container">
    <div class="contact-form">
      <h2>Contact Us</h2>
      <p class="section__subheader">CONTACT US</p>
      <h2 class="section__header">Trust Us, We fix it</h2>
      <p class="section__description">
        We’re here to help with all your automotive needs. Whether you have a question about our services,
        need to schedule an appointment, or require emergency assistance, our team is ready to assist you.
      </p>
      <form action="home.php" method="post">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name.." required>

        <label for="email">Email</label>
  <input type="email" id="email" name="emails" placeholder="Your email.." pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid Gmail address (example@gmail.com)" required>

  <div class="form-group">
                    <label for="subject">Kind of Service</label>
                    <select id="subject" name="subject" class="form-control" required>
                        <option value="">Select a service...</option>
                        <option value="Transmission Overhauling (automatic & manual)">Transmission Overhauling (automatic & manual)</option>
                        <option value="Engine Overhauling (gasoline & diesel)">Engine Overhauling (gasoline & diesel)</option>
                        <option value="Brake Service & ABS Repair">Brake Service & ABS Repair</option>
                        <option value="Diagnostic servo & Engine Sensor">Diagnostic servo & Engine Sensor</option>
                        <option value="Change Oil">Change Oil</option>
                    </select>
                </div>

        <label for="message">Message</label>
        <textarea id="message" name="message" placeholder="Write something.." style="height:300px" required></textarea>

        <button class="btn" type="submit" name="send_message">Send Message</button>
      </form>
    </div>

    <div class="contact-info">
      <h3>Contact Information</h3>
      <p><strong>Address:</strong> angat, bulacan</p>
      <p><strong>Email:</strong> Yayawautorepairshop@gmail.com</p>
      <p><strong>Phone:</strong> 0933-572-9904 / 0997-217-5292</p>
    </div>
  </div>
</section>




<footer class="footer">
  <div class="section__container footer__container">
    <div class="footer__column">
      <div class="logo footer__logo">
        <a href="#"><img src="image/logo.jpg" alt="logo" /></a>
      </div>
      <p class="section__description">
        Owner: Erick Maigting
      </p>
      <p class="section__description">
        Business start: 2014
      </p>
      <ul class="footer__socials">
        <li><a href="https://www.facebook.com/profile.php?id=100094762587018"><i class="ri-facebook-fill"></i></a></li>
        <li><a href="https://www.facebook.com/erick.maigting?mibextid=ZbWKwL"><i class="ri-user-fill"></i></a></li>
        <li><a href="https://www.youtube.com/@yayawautorepairshop"><i class="ri-youtube-fill"></i></a></li>
      </ul>
    </div>

    <div class="footer__column">
      <h4>Our Services</h4>
      <ul class="footer__links">
        <li><a href="#">Transmission Overhauling (automatic & manual)</a></li>
        <li><a href="#">Engine Overhauling (gasoline & diesel)</a></li>
        <li><a href="#">Brake services & ABS repair</a></li>
        <li><a href="#">Diagnostic servo & engine sensor</a></li>
        <li><a href="#">Change oil</a></li>
      </ul>
    </div>

    <div class="footer__column">
      <h4>Contact Info</h4>
      <ul class="footer__links">
        <li>
          <p>Experience the magic of a rejuvenated ride as we pamper your car with precision care</p>
        </li>
        <li>
          <p>Contact details: <span>0933-572-9904 / 0997-217-5292</span></p>
        </li>
        <li>
          <p>Email: <span>Yayawautorepairshop@gmail.com</span></p>
        </li>
      </ul>
  </div>
</footer>
<div class="footer__bar">
  Thank you for visiting us!
</div>

    <!-- JavaScript libraries and script -->
  <script src="https://unpkg.com/scrollreveal"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script src="script.js"></script>

  <!-- Initialize Swiper -->
  <script>
    var swiper = new Swiper(".swiper-container", {
      slidesPerView: "auto",
      spaceBetween: 30,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
    });
  </script>
</body>
</html>