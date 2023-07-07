<?php 
session_start();
if(!isset($_SESSION['email']))
{
    header("location:login.php");
}
else
{
?>
<!doctype html>
<html lang="en">
  <head>
    <title>Admin Dashboard</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
       
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="shortcut icon" href="../assets/images/favicon.PNG" type="image/x-icon">

  </head>
  <body>
   <!-- navbar start -->
   <nav class="navbar navbar-expand-sm navbar-dark" style="background-color:darkblue;">
   <img src="..\assets\images\logo.PNG" alt="add" class="img-thumbnail" style="width: 85px; height: 65px;">
       <a class="navbar-brand" href="main.php">Admin-Dashboard</a>
       <button class="navbar-toggler d-lg-none" type="button" data-toggle="collapse" data-target="#collapsibleNavId" aria-controls="collapsibleNavId"
           aria-expanded="false" aria-label="Toggle navigation"></button>
       <div class="collapse navbar-collapse" id="collapsibleNavId">
           <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
           </ul>
           <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="#" style="text-transform:uppercase"><?php echo $_SESSION['email']; ?> <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="logout.php">Log Out</a>
            </li>
           </ul>
           
           
       </div>
   </nav>
   <!-- navbar end -->
    <!-- dashboard start -->
    <div class="container-fluid" style="padding:20px">
    <div class="row">
    <div class="col-md-4" style="background-color:#C0C0C0;">
            <h2 class="display-4 text-center">Dashboard</h2>
            <a href="user-details.php" class="btn btn-success btn-block btn-lg">Display user</a>
            <a href="news.php" class="btn btn-success btn-block btn-lg"> Add News</a>
            <a href="view-news.php" class="btn btn-success btn-block btn-lg">View news</a>
            <a href="booking.php" class="btn btn-success btn-block btn-lg">View Ticket Booked</a>
            <a href="comment.php" class="btn btn-success btn-block btn-lg">View Comments</a>
            <a href="upload-photo.php" class="btn btn-success btn-block btn-lg">Upload photo</a>
            <a href="upload-video.php" class="btn btn-success btn-block btn-lg">Upload Video</a>
        </div>
        <div class="col-md-8">
            <h2 class="display-4">Welcome!</h2>
            <h2> 
                <?php 
                include '../config/connection.php';
                $query="select * from posts";
                $result=mysqli_query($conn,$query);
                if($result)
                {
                    $rowcount=mysqli_num_rows($result);
                    echo "Book Ticket: ".$rowcount;
                }
                ?>  
            </h2>
        </div>
    </div>        
    </div>
    <!-- dashboard end -->
  </body>
</html>
<?php
}
?>