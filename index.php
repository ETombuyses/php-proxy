<html>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>proxy image</title>
</head>
<body>
<?php 

//image we need to display on the page ---------------------------------
//change the url and the image name to use another image.
$url = 'https://www.actugaming.net/wp-content/uploads/2019/03/one-piece-world-seeker.jpeg';
$image_name = 'onepiece.jpg';
$prefix = 'http://localhost:8080' . $_SERVER['REQUEST_URI'];

//the folder where the image will be stored
$folder = 'images/';
$image_path = $folder . $image_name;

//the sizes (width) you want to resize the image to
$mobile_width = 320;
$tablet_width = 640;
$desktop_width = 1366;



// Connection to the database ----------------------------------------
$pdo = new PDO(
  'mysql:host=localhost;dbname=hetic_proximage;',
  'root',
  '',
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
  ]
);




// Device detection --------------------------------------------------
$useragent = $_SERVER['HTTP_USER_AGENT'];
$tablet = false;
$mobile = false;
$desktop = false;

if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $useragent)) {
  $tablet = true;
} elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|iphone|ipod|midp|wap|phone|android|iemobile)/i', $useragent)) {
  $mobile = true;
} elseif (preg_match('/windows|win32|macintosh/i', $useragent)) {
  $desktop = true;
}



//resize function -------------------------------------------------------------

function resize_image($resource_path, $new_width, $folder, $file_name) {
  list($width, $height) = getimagesize($resource_path);
  
  $coefficient = $new_width/$width;
  $new_height = $height * $coefficient;
  
  // Load
  $thumb = imagecreatetruecolor($new_width, $new_height);
  $source = imagecreatefromjpeg($resource_path);
  
  // Resize
  imagecopyresized($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
  
  // Output 
  imagejpeg($thumb, $folder . $new_width . $file_name);
}

// function to display the right image size according to the identified device ----
function display_image($mobile, $tablet, $desktop , $url, $pdo, $prefix) {
  if ($mobile) {
    $resultat = $pdo->query("SELECT chemin_mobile FROM images WHERE url ='$url'");
    $path = $resultat->fetch(PDO::FETCH_ASSOC);
    ?>
    <img src="<?php echo $prefix . $path['chemin_mobile'] ?>">
    <?php
  } elseif ($tablet) {
    $resultat = $pdo->query("SELECT chemin_tablet FROM images WHERE url ='$url'");
    $path = $resultat->fetch(PDO::FETCH_ASSOC);
    ?>
    <img src="<?php echo $prefix . $path['chemin_tablet'] ?>">
    <?php
  } elseif ($desktop) {
    $resultat = $pdo->query("SELECT chemin_pc FROM images WHERE url ='$url'");
    $path = $resultat->fetch(PDO::FETCH_ASSOC);
    ?>
    <img src="<?php echo $prefix . $path['chemin_pc'] ?>">
    <?php
  }
}


//check if the image has already been downloaded -------------------------
$resultat = $pdo->query("SELECT url FROM images WHERE url ='$url'");
$img = $resultat->fetch(PDO::FETCH_ASSOC);

//if already downloaded, display the right size
if ($img['url']) {
 display_image($mobile, $tablet, $desktop, $url, $pdo, $prefix);
}

//if not already on the database
else {  
  define('LOCAL_IMG_DIR' , './images/'); 
  $online_images[] = array('online_image_url' => $url, 'local_img_name' => $image_name); 

  foreach ($online_images as $image_to_download) { 
    $image_online_url = file_get_contents($image_to_download['online_image_url']); 
    if ($image_online_url != '') { 
      if (file_put_contents(LOCAL_IMG_DIR.$image_to_download['local_img_name'], $image_online_url)){        
        resize_image($image_path, $desktop_width, $folder, $image_name);
        resize_image($image_path, $tablet_width, $folder, $image_name);
        resize_image($image_path, $mobile_width, $folder, $image_name);
        
        $path_mobile = $folder . $mobile_width . $image_name;
        $path_tablet = $folder . $tablet_width . $image_name;
        $path_desktop = $folder . $desktop_width . $image_name;
        
        $pdo-> exec("
        INSERT INTO images VALUES (
          '',
          '$url',
          '$path_mobile',
          '$path_tablet',
          '$path_desktop'
        )"
        );

       display_image($mobile, $tablet, $desktop, $url, $pdo, $prefix);
        
      } else { 
        echo '<br/>Erreur d\'écriture vers : '.LOCAL_IMG_DIR.$image_to_download['local_img_name']; 
      } 
    } else { 
      echo '<br>Impossible de récupérer '.$image_to_download['online_image_url']; 
    } 
  }
}
?>
</body>
</html>