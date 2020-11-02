<?php 
    session_start();
    $user="Guest";
    if(isset($_SESSION['user']))
        $user=$_SESSION['user'];

    function create_card($thumbnail,$name){
        echo '<div class="card" style="margin-left: 0;">';
        echo '<img src="'.$thumbnail.'" class="card-img-top" alt="#">';
        echo '<div class="card-body" style="text-align: center;">';
        echo '<p class="card-text">'.$name.'</p>';
        echo '</div>';
        echo '</div>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/content.css">
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

    <script src="js/index.js"></script>
</body>
</html>
