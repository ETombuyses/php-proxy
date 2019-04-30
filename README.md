# php-proxy

This is a proxy created for a course.

What it does : 

- download an image based on its url
- resize it to 3 different sizes
- store those images in the hetic_proxymage database
- display the right image size according to the user's device

How to use it : 

1. create a local database whith the following code : 

CREATE DATABASE hetic_proxymage;

USE hetic_proxymage;

    CREATE TABLE images (
        id_image INT(5) NOT NULL AUTO_INCREMENT,
        url TEXT NOT NULL,
        chemin_mobile TEXT NOT NULL,
        chemin_tablet TEXT NOT NULL,
        chemin_pc TEXT NOT NULL,
        PRIMARY KEY (id_image)
    ) ENGINE=InnoDB ;


2. download the project and place it on your local server (XAMPP , WAMP ...)

3. open localhost and open the index.php of the project. 


4. Make sure your localhost port matches the port in the $prefix variable (very beginning of the php code).

$prefix = 'http://localhost:8080' . $_SERVER['REQUEST_URI'];

5. Modify the $url and $image_name if you want to download another image !

$url = 'https://www.actugaming.net/wp-content/uploads/2019/03/one-piece-world-seeker.jpeg';
$image_name = 'onepiece.jpg';