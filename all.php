<?php 
    session_start();
    $user="Guest";
    if(isset($_SESSION['user']))
        $user=$_SESSION['user'];

    $severname="localhost";
    $username="root";
    $password="";
    $dbname="Music_Player";
    
    $conn=mysqli_connect($severname,$username,$password);
            
    if(!$conn)
        die("Connection failed".mysqli_connect_error());
    mysqli_select_db($conn, $dbname);

    if(isset($_POST['did'])){
        $id=$_POST['did'];
        $sql="
            SELECT * FROM `music` WHERE `id`='$id'
        ";
        $result=$conn->query($sql);
        $row = mysqli_fetch_array($result);
        print_r($row);
        exit;
    }

    require_once('vendor/autoload.php'); 
    use YouTube\YouTubeDownloader;
    $yt = new YouTubeDownloader();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Oswald&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <title>Music Player</title>
</head>
<body>

<div class="overlay"></div>
    <!-- Navigation and SideNavigation Begins -->
    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="#" class="open">My Music</a>
        <a href="all.php?q=recent" class="open">New Releases</a>
        <a href="#" class="open">Top Artists</a>
        <a href="categories.php" class="open">Categories</a>
        <div class="sub-menu">
            <a href="#0" class="open">Category0</a>
            <a href="#0" class="open">Category1</a>
            <a href="#0" class="open">Category2</a>
            <a href="#0" class="open">Category3</a>
        </div>
    </div>

    <div class="navbar">
        <span class="nav-item" id="nav-item-1">
            <a href="#" onclick="openNav()" class="side-nav-icon"><i class="fas fa-bars"></i></a>
        </span>
        <span class="nav-item" id="nav-item-2">
            <ul type="none">
                <li><a href="index.php" class="open nav-link">Home</a></li>
                <li>About</li>
                <li>Contact</li>
            </ul>
        </span>
        <span class="nav-item" style="float:right;" id="nav-item-3">
            <i class="fas fa-user-circle" aria-hidden="true"></i>
            <label><?php echo($user);?></label>
        </span>
        <span class="nav-item searchbar" id="nav-item-4">
            <form action="index.php" method="post">
                <input type="text" placeholder="Search By Song Name" class="search" name="elem" autocomplete="off">
            </form>
            <i style="float:right;" class="fa fa-search"></i>
        </span>
    </div>

    <?php 
        $yt_id=array();$name=array();
        $img=array();$duration=array();

        if(isset($_POST['elem'])){
            $elem=$_POST['elem'];
            $str="https://freemp3downloads.online/download?url=".$elem;
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);

            $doc->loadHTMLFile($str);
            
            foreach($doc->getElementsByTagName('img') as $a) {
                if ($a->getAttribute('class') === 'card-img-top') {
                    array_push($img,$a->getAttribute('src'));
                    array_push($name,$a->getAttribute('alt'));
                }
            }

            foreach($doc->getElementsByTagName('small') as $a) {
                if ($a->getAttribute('class') === 'text-muted') 
                    array_push($duration,$a->nodeValue);
            }

            foreach($doc->getElementsByTagName('a') as $a) {
                if ($a->getAttribute('class') === 'card-link') {
                    $parts = explode('?url=', $a->getAttribute('href'));
                    array_push($yt_id,$parts[1]);
                }
            }

            $links = $yt->getDownloadLinks("https://www.youtube.com/watch?v=".$yt_id[0]);

            $audio=end($links);
            $url=$conn->real_escape_string($audio['url']);

            $name=$conn->real_escape_string($name[0]);
            $name=substr($name,0,49);

            $thumbnail=$conn->real_escape_string($img[0]);
            $duration=$conn->real_escape_string($duration[0]);

            $apikey='AIzaSyDW7C7b4M9M_ijpx6NmzrZ0MaH3janKVcc';
            $googleApiUrl='https://youtube.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id='.$yt_id[0].'&key='.$apikey;
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
                
            curl_close($ch);
                
            $data = json_decode($response);
            $data = json_decode(json_encode($data), true);

            $date=$data['items'][0]['snippet']['publishedAt'];
            $date=explode('T',$date);
            $date=$date[0];

            $likes=$data['items'][0]['statistics']['likeCount'];
        
            $sql="INSERT INTO `music`(`name`,`thumbnail`,`src`,`duration`,`release_date`,`likes`) VALUES ('$name','$thumbnail','$url','$duration','$date','$likes');";
            $result=$conn->query($sql);
           
            if($result)
                $id=$conn->insert_id;
            else
                echo 'Error: '.$conn->error;

            header("Location:/Music-Player-master/play.php?id=".$id);    
        }
    ?>
    <!-- Navigation and SideNavigation Ends -->

    <?php
        if(!isset($_GET['q']))
            header("Location:index.php");

        function number_shorten($number, $precision=2, $divisors=null) {

            if (!isset($divisors)){
                $divisors = array(
                    pow(1000, 0) => '', 
                    pow(1000, 1) => 'K', 
                    pow(1000, 2) => 'M', 
                    pow(1000, 3) => 'B', 
                    pow(1000, 4) => 'T', 
                    pow(1000, 5) => 'Qa', 
                    pow(1000, 6) => 'Qi'
                );    
            }
    
            foreach ($divisors as $divisor => $shorthand) {
                if (abs($number) < ($divisor * 1000)) {
                    // We found a match!
                    break;
                }
            }
            return number_format($number/$divisor,$precision).$shorthand;
        }

        if(isset($_POST['id']) && isset($_POST['flag'])){
                $id=$_POST['id'];
                $flag=$_POST['flag'];

                $sql="
                    SELECT * FROM `music` WHERE `id`='$id'
                ";
                $result=$conn->query($sql);
                $row = mysqli_fetch_array($result);
                $likes=$row['likes'];

                if($flag==1)
                    $sql="UPDATE `music` SET `likes`=$likes+1 WHERE `id`='$id'";
                else 
                    $sql="UPDATE `music` SET `likes`=$likes-1 WHERE `id`='$id'";
                
                $conn->query($sql); 
                exit;
            }
    ?>  

    <table id="table1">
        <tbody>
            <?php
                if($_GET['q']=="popular"){
                    echo "<h2>Popular And Trending</h2>";
                    $sql="
                    SELECT * FROM `music` ORDER BY `likes` DESC; 
                    ";
                    $result=$conn->query($sql);

                    while($rows=$result->fetch_row()){
                        echo '<tr class="popular_a">';
                        echo '<td><img src="'.$rows[1].'" alt="#"></td>';
                        echo '<td><p class="card-text">'.$rows[2].'</p><p>'.$rows[3].'</p></td>';
                        echo '<td><i id="'.$rows[0].'" class="far fa-heart" onclick="changeHeart('.$rows[0].')"></i><span id="n'.$rows[0].'"> '.number_shorten($rows[8]).'</span></td>';
                        echo '<td><a href=play.php?id='.$rows[0].'><i id="p'.$rows[0].'" class="fas fa-play"></i></a></td>';
                        echo '<td><i id="d'.$rows[0].'" class="fa fa-plus" aria-hidden="true" onclick="open_modal('.$rows[0].')"></i></td>';
                        echo '</tr>';
                    }
                }

                elseif($_GET['q']=="recent"){
                    echo "<h2>Recent Releases</h2>";
                    $sql="
                    SELECT * FROM `music` ORDER BY `release_date` DESC; 
                    ";
                    $result=$conn->query($sql);

                    while($rows=$result->fetch_row()){
                        echo '<tr class="recent_a">';
                        echo '<td><img src="'.$rows[1].'" alt="#"></td>';
                        echo '<td><p class="card-text">'.$rows[2].'</p><p>'.$rows[3].'</p></td>';
                        echo '<td><i id="'.$rows[0].'" class="far fa-heart" onclick="changeHeart('.$rows[0].')"></i><span id="n'.$rows[0].'"> '.number_shorten($rows[8]).'</span></td>';
                        echo '<td><a href=play.php?id='.$rows[0].'><i id="p'.$rows[0].'" class="fas fa-play"></i></a></td>';
                        echo '<td><i id="d'.$rows[0].'" class="fa fa-plus" aria-hidden="true" onclick="open_modal('.$rows[0].')"></i></td>';
                        echo '</tr>';
                    }
                }
            ?>
        </tbody>
    </table>

    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="ditem" style="text-align:center">
                <img id="dimage" src="#" alt="#">
            </div>

            <table style="margin: 0 auto;">
                <tr class="ditem">
                    <td class="attr"><img class="icon" src="./img/music.jpg" alt="#"> Title</td>
                    <td class="val" id="dtitle"></td>
                </tr>
                <tr class="ditem">
                    <td class="attr"><img class="icon" src="./img/mic.jpg" alt="#"> Artist</td>
                    <td class="val" id="dartist"></td>
                </tr>
                <tr class="ditem">
                    <td class="attr"><img class="icon" src="./img/calendar.png" alt="#"> Released On</td>
                    <td class="val" id="drelease"></td>
                </tr>
                <tr class="ditem">
                    <td class="attr"><img class="icon" src="./img/clock.png" alt="#"> Duration</td>
                    <td class="val" id="dduration"></td>
                </tr>
            </table>

            <div style="display: flex; flex-direction: column; text-align: center">
                <a class="btn" href="https://www.google.com/">Add To Queue</a>
                <a class="btn" href="#">Add To Playlist</a>
                <a class="btn" href="#">Download</a>
                
            </div>
        </div>
    </div>

    <script src="js/all.js"></script>
    <script src="js/index.js"></script>
                
</body>
</html>