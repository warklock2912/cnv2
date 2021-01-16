<?php
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(__FILE__));

session_start();



function saveVistorData($isnew = false)
{
    if ($isnew == true) {
        $fp = fopen(BP . DS .'visitordata.csv', 'a');
        fputcsv($fp,  array(
            'session_id' => session_id(),
            'visit_at' => date('Y-m-d H:i:s'),
            'ref_link' => $_GET['ref_link']
        ));
        fclose($fp);
    }
}

function checkIfInQueue()
{
        $isInQueue = false;
        $vistordata = fopen(BP . DS ."visitordata.csv", "a");
        if ($vistordata) {
            while (($line = fgetcsv($vistordata)) !== false) {
                if(isset($line['0']))
                {
                    if($line['0'] == $_COOKIE['queue_session_id']) /// still in queue
                    {
                        $isInQueue = true;
                    }
                }
            }
        }else
        {
            $isInQueue = true;
        }
        return $isInQueue;
}

if (!isset($_COOKIE['queue_session_id'])) {
    saveVistorData(true);
    $cookie_name = "queue_session_id";
    $cookie_value = session_id();
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
}

?>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Creative - Start Bootstrap Theme</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v5.13.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
    <!-- Third party plugin CSS-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="carnival-landing/css/styles.css" rel="stylesheet" />
</head>
<body id="page-top">
<!-- Navigation-->
<nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
    <div class="container">
        <a class="navbar-brand js-scroll-trigger" href="#page-top">CarnivalBkk</a>
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>

    </div>
</nav>
<!-- Masthead-->
<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-end">
                <h1 class="text-uppercase text-white font-weight-bold">Please wait for 1 minute before accessing our system</h1>
                <hr class="divider my-4" />
            </div>
            <div class="col-lg-8 align-self-baseline">
                <p class="text-white-75 font-weight-light mb-5">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book</p>
            </div>
        </div>
    </div>
</header>


<!-- Bootstrap core JS-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
<!-- Third party plugin JS-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
<!-- Core theme JS-->
<!--<script src="js/scripts.js"></script>-->
</body>
</html>

<?php if(!(isset($_COOKIE['queue_valid']) && $_COOKIE['queue_valid'] == 1)) : ?>
<script type="text/javascript">
    function checkQueue () {
        $.ajax({
            url:"checkQueue.php",    //the page containing php script
            type: "post",    //request type,
            dataType: 'json',
            success:function(result){
                if(!result.inQueue){
                    window.location.replace("<?php echo $_GET['ref_link'].'?t='.time() ?>")
                }
            }
        });
    }

    setInterval(function(){
        checkQueue()
    }, 10000)
</script>
<?php endif; ?>




