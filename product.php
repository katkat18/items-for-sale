<?php
require 'includes/functions.php';

$message = '';
$pid = filterID($_GET['product']);

if($pid == null){
    header('Location: index.php');
    exit(); 
}

$product = getProduct($pid);

if($product){
    addCookie($pid);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>COMP 3015</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div id="wrapper">

    <div class="container">

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <h1 class="login-panel text-center text-muted">
                    COMP 3015 Final Project
                </h1>
                <hr/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-offset-3 col-md-6">
                <div>
                    <p>
                        <a class="btn btn-default" href="index.php">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    </p>
                </div>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <?php echo $product['title']; ?>
                    </div>
                    <div class="panel-body text-center">
                        <p>
                            <img class="img-rounded img-thumbnail" src="<?php echo 'products/'.$product['picture']?>"/>
                        </p>
                        <p class="text-muted text-justify">
                            <?php echo $product['description']; ?>
                        </p>
                    </div>
                    <div class="panel-footer ">
                        <span><a href="<?php echo 'mailto:'.$product['email']?>"><i class="fa fa-envelope"></i><?php echo $product['firstname'] .' '. $product['lastname']; ?></a></span>
                        <span class="pull-right">$<?php echo $product['price']; ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<div id="newPost" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">New Profile</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input class="form-control disabled" disabled>
                </div>
                <div class="form-group">
                    <label>Profile Picture</label>
                    <input class="form-control" type="file" name="picture">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Submit!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</html>
