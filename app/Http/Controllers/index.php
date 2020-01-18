<?php
    include_once ('admin/dbbridge/DBManager.php');
    $db = new DBManager();
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <title>BumpstoBaby</title>
    <!-- Bootstrap CSS -->
  
    <link rel="stylesheet" href="css/themify-icons.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Open+Sans" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="vendors/owl-carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="vendors/animate-css/animate.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- main css -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
  <link rel="stylesheet" href="css/bootstrap.css">
<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
<link href="s3-slider.css" rel="stylesheet" type="text/css">
<link href="new.css" rel="stylesheet" type="text/css">
</head>
<body>
<header>
    <div class="page-wrapper chiller-theme toggled">
      <a id="show-sidebar" class="btn btn-sm btn-dark" href="#">
        <i class="fas fa-bars"></i>
      </a>
      <nav id="sidebar" class="sidebar-wrapper">
        <div class="sidebar-content">
          <div class="sidebar-header">
            <div class="logoc">
              <img class="img-responsive img-rounded" src="img/logo2.png" alt="logo2">
            </div>
          </div>
          <!-- sidebar-header  -->
            <section class="portfolio_areas area-padding" id="portfolio">
                <div class="filters portfolio-filter">
                    <ul>
                        <li id="home">Home</li><hr style="opacity: 0.65">
                        <li id="about" >About</li><hr style="opacity: 0.65">
                        <li id="session" >Session Information </li><hr style="opacity: 0.65">
                        <li id='gallery'> Portfolio
                            <ul>
                                <li><a href="gallery.php?id=2">Newborn</a>
                                </li>
                                <li><a href="gallery.php?id=3">Maternity </a>
                                </li>
                                <li><a href="gallery.php?id=4">Cake Smash </a>
                                </li>
                            </ul>
                        </li><hr style="opacity: 0.65">
                        <li id="pricing" >Pricing</li><hr style="opacity: 0.65">
                    </ul>
                </div>
            </section>
        </div>
      </nav>
  </div>                    
 </header>

<div class="container">
	<div id="sliders">
	    <div id="slider">
            <?php
                $where=" where fld_type='1' ";
                $sliders = $db->get_all_sliders($where);
                if (count($sliders) > 0){
                    foreach ($sliders as $slider){
                        $image = 'slider_images/'.$slider['fld_image'];
                        ?>
                        <div class="slide ">
                            <img src="<?=$image?>"/>
                        </div>
            <?php
                    }
                }
            ?>
	    </div><br>
	    <div class="area-heading" style="margin-bottom:0px">
	        <h4>Capturing the moments of today that will wow your heart tomorrow</h4>
	    </div><br>
	</div>
        <!--================ Start Portfolio Area =================-->

        <section class="portfolio_area area-padding p_area" id="portfolio" style="display:none">

                <div class="area-heading">
                    <h3>My Photoshoot <span>Gallery</span></h3>
                    <p>She'd earth that midst void creeping him above seas.</p>
                </div>

                <div class="filters portfolio-filter">
                    <ul>
                        <li class="active" data-filter="*">all</li>
                        <li data-filter=".2">weeding </li>
                        <li data-filter=".3"> motion</li>
                        <li data-filter=".4">portrait</li>
                        <li data-filter=".5">fashion</li>
                    </ul>
                </div>

                <div class="filters-content">
                    <div class="row portfolio-grid">
                        <div class="grid-sizer col-md-3 col-lg-4"></div>
                        <?php
                        $where=" where fld_type='5' ";
                        $sliders = $db->get_all_sliders($where);
                        if (count($sliders) > 0){
                            foreach ($sliders as $slider){
                                $image = 'slider_images/'.$slider['fld_image'];
                                ?>
                                <div class="col-lg-4 col-md-6 all <?=$slider['fld_gallery_type']?> ">
                                    <div class="single_portfolio">
                                        <img class="img-fluid w-100" src="<?=$image?>" alt="">
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
        </section>

        <!--================About  Area =================-->
      
        <section class="about-area area-padding" style="display:none">
            <?php
            $where = ' where fld_short_desc=1';
            $session_information = $db->getActiveServiceByServiceId($where);
            if (count($session_information) > 0){
                foreach ($session_information as $information){
                    if (!empty($information['fld_image'])){
                        $image = 'servicesimg/'.$information['fld_image'];
                        ?>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="img-styleBox">
                                    <div class="styleBox-border">
                                        <img class="styleBox-img1" src="<?=$image?>" alt="<?=$information['fld_title']?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-12 offset-md-0 offset-lg-1">
                                <div class="about-content">
                                    <h4><?=$information['fld_title']?></h4>
                                    <p>
                                        <?php
                                            echo htmlspecialchars_decode($information['fld_description']);
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
            <?php
                    }else{
                        ?>
                        <div class="row why_choose_me">
                            <div class="col-lg-12" style="text-align: center;margin-top: 40px;">
                                <h4 style="font-weight: 500;
                         font-size: 22px;
                         color: #bb7612;
                         margin-bottom: 25px;"> <?=$information['fld_title']?> </h4>
                            </div>
                            <div class="col-lg-10 offset-lg-1">
                                <p style="text-align: center;">
                                    <?php
                                        echo htmlspecialchars_decode($information['fld_description']);
                                    ?>
                                </p>
                            </div>
                        </div>
            <?php
                    }
                }
            }
            ?>
        </section>

        
        <!--================About Area End =================-->


        <!--================Testimonial section Start =================-->
     
    <section class="bg-gray area-padding bg-1 testimonial-area">
            
        <div class="area-heading">
            <!-- <h3>happy <span> clients</span> says</h3> -->
           <!--  <p>She'd earth that midst void creeping him above seas</p> -->
        </div>

    <div class="row">
        <div class="col-md-12 col-sm-12 col-lg-12 col-center m-auto" style="margin-top:-80px!important; ">
            <div id="myCarousel" class="carousel slide" data-ride="carousel">
                <!-- Wrapper for carousel items -->
                <div class="carousel-inner">
                    <?php
                        $active_testimonial = $db->getAllActiveTestimonials();
                        if (count($active_testimonial) > 0){
                            foreach ($active_testimonial as $index=>$testimonial){
                                $image = 'servicesimg/'.$testimonial['fld_image'];
                                if ($index == 0){
                                    $class = 'active';
                                } else{
                                    $class = '';
                                }
                                ?>
                                <div class="item carousel-item <?=$class?>">
                                    <div class="img-box">
                                        <img src="<?=$image?>" alt="">
                                    </div>
                                    <p class="testimonial">
                                        <?php
                                            echo htmlspecialchars_decode($testimonial['fld_description']);
                                        ?>
                                    </p>
                                    <p class="overview"><b>- <?=$testimonial['fld_name']?> </b></p>
                                </div>
                    <?php
                            }
                        }
                    ?>
                </div>
                <!-- Carousel controls -->
                <a class="carousel-control left carousel-control-prev" href="#myCarousel" data-slide="prev">
                    <i class="fa fa-angle-left"></i>
                </a>
                <a class="carousel-control right carousel-control-next" href="#myCarousel" data-slide="next">
                    <i class="fa fa-angle-right"></i>
                </a>
            </div>
        </div>
   
</div>
      
        </section>
        <!--================Testimonial section End =================-->

         <section class="blog-area area-padding recent-news pricing-area" style="display:none">

                <div class="col-sm-12 text-center top_heading">
                    <h3 style="font-size: 37px;">Full Pricing</h3>
                    <p>Complete the form below to INSTANTLY be redirected to my studio price guide!</p>
                </div>

                <form id="load_calculator">
                    <div class="column-setter span12">
                        <div class="col-sm-12" style="display: inline-flex;">
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                        <span class="wpcf7-form-control-wrap FirstName">
                            <input type="text" name="FirstName" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" tabindex="01" aria-required="true" aria-invalid="false" placeholder="First Name*">
                        </span>
                            </div>
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                        <span class="wpcf7-form-control-wrap LastName">
                            <input type="text" name="LastName" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" tabindex="02" aria-required="true" aria-invalid="false" placeholder="Last Name*">
                        </span>
                            </div>
                        </div>


                        <div style="clear:both"></div>
                        <div class="col-sm-12" style="display: inline-flex;">
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                        <span class="wpcf7-form-control-wrap Email">
                            <input type="email" name="Email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" tabindex="03" aria-required="true" aria-invalid="false" placeholder="Your Email*">
                        </span>
                            </div>
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                        <span class="wpcf7-form-control-wrap MobilePhone">
                            <input type="tel" name="MobilePhone" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-required wpcf7-validates-as-tel" tabindex="04" aria-required="true" aria-invalid="false" placeholder="Contact Number*">
                        </span>
                            </div>
                        </div>

                        <div style="clear:both"></div>
                        <div class="col-sm-12" style="display: inline-flex;">
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                                <label>What Is Your Enquiry About?*</label><br>
                                <span class="wpcf7-form-control-wrap JobType">
                            <select name="JobType" class="wpcf7-form-control wpcf7-select wpcf7-validates-as-required cstm-dd-icon" tabindex="09" aria-required="true" aria-invalid="false">
                                <option value="Maternity Session">Maternity Session</option>
                                <option value="Newborn Session">Newborn Session</option>
                                <option value="Newborn Session">4 months + session</option>
                                <option value="Newborn Session">Family session</option>
                                <option value="Newborn Session">Newborn with parent/sibling session</option>
                                <option value="Maternity &amp; Newborn Session">Maternity &amp; Newborn Session</option>
                                <option value="Cake Smash Session">Cake Smash Session</option>
                                <option value="Gift Voucher">Gift Voucher</option>


                            </div>
                            <div class="col-sm-6 span6 mtheme-block " style="padding: 0px;">
                                <label>Due Date or Preferred Session Date*</label><br>
                                <span class="wpcf7-form-control-wrap EventDate">
                        <input type="date" name="EventDate" value="" size="40" class="wpcf7-form-control wpcf7-date wpcf7-validates-as-required hasDatepicker" tabindex="05" aria-required="true" placeholder="Please Select" >
                    </span>
                            </div>
                        </div>
                        <div style="clear:both"></div>
                        <div class="col-sm-12 span12 mtheme-block">
                        <span class="wpcf7-form-control-wrap message">
                            <textarea name="message" placeholder="How Can I Help You?"> </textarea>

                            </textarea>
                        </span>
                        </div>
                        <div class="col-sm-12 span12 mtheme-block"><label>How Did You Find Me?*</label><br>
                            <span class="wpcf7-form-control-wrap Source">
                            <select name="Source" class="wpcf7-form-control wpcf7-select wpcf7-validates-as-required cstm-dd-icon" aria-required="true" aria-invalid="false" style=" width:94%;">
                                <option value="Google Search">Google Search</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Instagram">Instagram</option>
                                <option value="Word of Mouth">Word of Mouth</option>
                                <option value="Gumtree">Gumtree</option>
                                <option value="Other">Other</option>
                            </select>
                        </span>

                        </div>

                        <div class="span12 mtheme-block send_button" style="text-align:center;">
                            <!--                        <input type="submit" value="Send" class="wpcf7-form-control wpcf7-submit">-->
                            <button type="button" class="wpcf7-form-control wpcf7-submit" onclick="send_email()">Submit</button>
                        </div>

                    </div>
                </form>
        </section>


        <!--================ Session Information Blog Area =================-->
        
     	<section class="blog-area area-padding recent-news recent-session" style="display:none">
            <?php
                $where = ' where fld_short_desc=2';
                $session_information = $db->getActiveServiceByServiceId($where);
                if (count($session_information) > 0){
                    foreach ($session_information as $information){
                        $image = 'servicesimg/'.$information['fld_image'];
                        ?>
            <section id="cover_image">
                <img src="<?=$image?>" />
            </section>
                <div class="area-heading">
                    <h3 class="line"><?=$information['fld_title']?></h3>
                </div>
            <br>
                <div class="row">
                    <?php
                        echo htmlspecialchars_decode($information['fld_description']);
                    ?>
                </div>
            <?php
                    }
                }
            ?>
        </section>

        <!--================ start footer Area  =================-->

        <footer class="footer-area section_gap">

                <div class="row">
                    <div class="col-lg-4  col-md-6 col-sm-6">
                        <div class="single-footer-widget">
                            <h6>About Us</h6>

                            <p class="footer-text m-0">
                                Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved |<a href="https://www.thedigicrawl.com" target="_blank">Digicrawl</a>
                            </p>
                        </div>
                    </div>
                    <div class="offset-lg-1 col-lg-5   col-md-6 col-sm-6">
                        <div class="single-footer-widget">
                            <h6>Newsletter</h6>
                            <p>Stay updated with our latest trends</p>
                            <div class="" id="mc_embed_signup">

                                <div class="d-flex flex-row">

                                    <input class="form-control" name="EMAIL" placeholder="Enter Email" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter Email '" required="" type="email">


                                    <button class="click-btn btn btn-default"  type="button"><i class="ti-arrow-right"></i></button>
                                    <div style="position: absolute; left: -5000px;">
                                        <input name="b_36c4fd991d266f23781ded980_aefe40901a" tabindex="-1" value="" type="text">
                                    </div>

                                </div>
                                <div class="info"></div>
                            </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-sm-6">
                            <div class="single-footer-widget">
                                <h6>Follow Us</h6>
                                <p>Let us be social</p>
                                <div class="footer-social d-flex align-items-center">
                                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-dribbble"></i></a>
                                    <a href="#"><i class="fab fa-behance"></i></a>
                                </div>
                            </div>
                        </div>
                </div>
        </footer>
</div>
                <!--================ End footer Area  =================-->


<!-- page-wrapper -->






               <!-- Optional JavaScript -->
               <!-- jQuery first, then Popper.js, then Bootstrap JS -->
               <script src="js/jquery-3.3.1.min.js"></script>
               <script src="js/popper.js"></script>
               <script src="js/bootstrap.min.js"></script>
               <script src="js/stellar.js"></script>
               <script src="vendors/isotope/imagesloaded.pkgd.min.js"></script>
               <script src="vendors/isotope/isotope.pkgd.min.js"></script>
               <script src="vendors/owl-carousel/owl.carousel.min.js"></script>
               <script src="js/jquery.ajaxchimp.min.js"></script>
               <script src="js/jquery.counterup.min.js"></script>
               <script src="js/jquery.waypoints.min.js"></script>
               <script src="js/mail-script.js"></script>
               <script src="js/contact.js"></script>
               <script src="js/jquery.form.js"></script>
               <script src="js/jquery.validate.min.js"></script>
               <script src="js/mail-script.js"></script>
               <script src="js/theme.js"></script>
               <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="s3-slider.js"></script>

<script>
jQuery(function($){
  $('#slider').s3Slider({timeout:6000,fadeTime:500});
});
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<script type="text/javascript">
  $(".sidebar-dropdown > a").click(function() {
  $(".sidebar-submenu").slideUp(200);
  if (
    $(this)
      .parent()
      .hasClass("active")
  ) {
    $(".sidebar-dropdown").removeClass("active");
    $(this)
      .parent()
      .removeClass("active");
  } else {
    $(".sidebar-dropdown").removeClass("active");
    $(this)
      .next(".sidebar-submenu")
      .slideDown(200);
    $(this)
      .parent()
      .addClass("active");
  }
});

$("#close-sidebar").click(function() {
  $(".page-wrapper").removeClass("toggled");
});

$("#show-sidebar").click(function() {
  $(".page-wrapper").addClass("toggled");
});

///Hide show content

$("#about").click(function() {
  $(".about-area").show();
  $(".testimonial-area").hide();
  $("#sliders").hide();
  $(".recent-session").hide();
  $(".p_area").hide();
  $(".service-area").hide();
  $(".recent-area").hide();
  $(".pricing-area").hide();
});

$("#gallery").click(function() {
  $(".about-area").hide();
  $(".recent-session").hide();
  $("#sliders").hide();
  $(".recent-area").show();
  $(".testimonial-area").hide();
  $(".service-area").hide();
  $(".pricing-area").hide();
  $(".p_area").show();
});

$("#pricing").click(function() {
  $(".about-area").hide();
  $(".recent-session").hide();
  $(".p_area").hide();
  $("#sliders").hide();
  $(".recent-area").hide();
  $(".service-area").hide();
  $(".testimonial-area").hide();
  $(".pricing-area").show();
});

$("#recent").click(function() {
  $(".about-area").hide();
  $(".recent-session").hide();
  $(".p_area").hide();
  $("#sliders").hide();
  $(".pricing-area").hide();
  $(".service-area").hide();
  $(".testimonial-area").hide();
  $(".recent-area").show();
});

$("#home").click(function() {
  $(".about-area").hide();
  $(".recent-session").hide();
  $("#sliders").show();
  $(".p_area").hide();
  $(".pricing-area").hide();
  $(".recent-area").hide();
  $(".service-area").hide();
  $(".testimonial-area").show();
});

$("#service").click(function() {
  $(".about-area").hide();
  $(".p_area").hide();
  $(".pricing-area").hide();
  $(".recent-area").hide();
  $("#sliders").hide();
  $(".testimonial-area").hide();
  $(".recent-session").hide();
  $(".service-area").show();

});

$("#session").click(function() {
  $(".about-area").hide();
  $(".p_area").hide();
  $(".pricing-area").hide();
  $("#sliders").hide();
  $(".recent-area").hide();
  $(".testimonial-area").hide();
  $(".recent-session").show();
  $(".service-area").hide();

});

</script>
<script>
var slideIndex = 0;
carousel();

function carousel() {
  var i;
  var x = document.getElementsByClassName("mySlides");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none"; 
  }
  slideIndex++;
  if (slideIndex > x.length) {slideIndex = 1} 
  x[slideIndex-1].style.display = "block"; 
  setTimeout(carousel, 2000); 
}

</script>

<script type="text/javascript">
    function send_email(){
        formdata = $('#load_calculator').serialize();
        if(formdata.indexOf('=&') > -1 || formdata.substr(formdata.length - 1) == '='){
            //you've got empty values
            alert('you have to fill all information');
            return false;
        }
        $.ajax({
            type: 'POST',
            url:  'send_email1.php',
            data:formdata,
            success:function (data) {
                if (data == 1){
                    alert('Thanks for contacting us. Will contact back you shortly');
                    window.location.reload();
                } else{
                    alert('sorry, something is wrong, please try again');
                    window.location.reload();
                }
            },
            error:function (msg,data) {
                alert('sorry, something is wrong, please try again as our server is not responding');
                window.location.reload();
            }
        });
    }
</script>
</body>
</html>