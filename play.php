<?php 
    // session_start();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Oswald&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/play.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <title>Music Player</title>
</head>
<body>
    
    <div class="container">
        <?php include 'index.php'?>
    </div>
    <!-- Tracks used in this music/audio player application are free to use. I downloaded them from Soundcloud and NCS websites. I am not the owner of these tracks. -->
    
    <div id="app-cover">
        <div id="player">
            <div id="player-track">
                <p id="album-name"></p>
                <!-- <p id="track-name"></p> -->
                <div id="track-time">
                    <div id="current-time"></div>
                    <div id="track-length"></div>
                </div>
                <div id="s-area">
                    <div id="ins-time"></div>
                    <div id="s-hover"></div>
                    <div id="seek-bar"></div>
                </div>
            </div>
            <div id="player-content">
                <div class="playerbg"></div>
                <div id="album-art">

                    <?php
                    
                        if(isset($_GET['id'])){
                            $id=$_GET['id'];
                            $sql="
                                SELECT * FROM `music` WHERE `id`=$id; 
                            ";
                            $result=$conn->query($sql);
                    
                            $row=$result->fetch_row();
                            echo '<script>var arrayFromPhp='.json_encode($row).';</script>';
                            echo '<img src="'.$row[1].'" class="active" id="_1">';
                            echo '<img src="https://raw.githubusercontent.com/himalayasingh/music-player-1/master/img/_2.jpg" id="_2">';
                        }
                    ?>
                    <div id="buffer-box">Buffering ...</div>
                </div>
                <div id="player-controls">
                    <div class="control">
                        <div class="button" id="play-previous">
                            <i class="fas fa-backward"></i>
                        </div>
                    </div>
                    <div class="control">
                        <div class="button" id="play-pause-button">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    <div class="control">
                        <div class="button" id="play-next">
                            <i class="fas fa-forward"></i>
                        </div>
                    </div>
                    <div class="control">
                        <div class="button" id="loop">
                            <i class="fa fa-repeat" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/play.js"></script>
</body>
</html>