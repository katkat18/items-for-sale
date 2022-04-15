<?php
require 'includes/functions.php';

define('EXPIRE_TIME', 99*365*24*60*60);
define('INITIAL', -1);

$message = '';
$cookies = [];
$recents = [];

session_start();

if(isset($_COOKIE['error_message'])){
    $message = '<div class="alert alert-danger text-center">'
        . $_COOKIE['error_message'] .
        '</div>';

        //expire cookie
        setcookie('error_message', null, time() - 3600);
}

//file upload
if(count($_FILES) > 0){
    $check = checkNewItem($_FILES, $_POST);

    if($check !== true){
        $message = '
        <div class="alert alert-danger text-center">
            '. $check .'
        </div>
        ';
    }else{
        saveItem(trim($_SESSION['email']), $_FILES);
    }
}

//update recently viewed -- if a new item is viewed
if(isset($_COOKIE['temp'])){
    $newproduct = $_COOKIE['temp'];

    //get previous ids
    if(isset($_COOKIE['cookie'])){
        foreach($_COOKIE['cookie'] as $cookievalue){
            if($cookievalue != $newproduct){
                $cookies[] = $cookievalue;
            }
        }
    }

    //add the most recent id
    array_unshift($cookies, $newproduct);

    //check if there are more than 4 ids, remove the oldest one first
    if(count($cookies) > 4){
        array_pop($cookies);
    }

    //update cookies
    $count = 0;
    while(isset($cookies[$count])){ 
        setcookie('cookie['.$count.']', $cookies[$count], time() + EXPIRE_TIME);
        $count++;
    }

    //expire recent
    setcookie('temp', null, time() - 3600);
    
    //make a refresh to update
    header('Refresh:0');

}else{
    //just get recent cookies
    if(isset($_COOKIE['cookie'])){
        foreach($_COOKIE['cookie'] as $cookievalue){
            $cookies[] = $cookievalue;
        }
    }
}

//find products in FIFO order
foreach($cookies as $cookie){
    $recents[] = getProduct($cookie);
}

//comment out below for FIFO(above), otherwise it's user first, then price
$firstname = array_column($recents, 'firstname');
$lastname = array_column($recents, 'lastname');
$price = array_column($recents, 'price');
    
array_multisort($lastname, SORT_ASC, $firstname, SORT_ASC, $price, SORT_DESC, $recents);

//check for search
if(isset($_GET['search'])){
    $products = searchForProducts(trim($_GET['search']));
}else{
    if(isset($_SESSION['loggedin'])){
        $products = getAllProducts($_SESSION['email']);
    }else{
        $products = getProducts();
    }
}

if(isset($_SESSION['loggedin'])){
    $pins = getAllPins($_SESSION['email']);
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
                <?php echo $message; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
            <?php if(isset($_SESSION['loggedin'])): ?>
                <button class="btn btn-default" data-toggle="modal" data-target="#newItem"><i class="fa fa-photo"></i> New Item</button>
                <a href="logout.php" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>
                <!--<a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#login"><i class="fa fa-sign-in"> </i> Login</a>
                <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#signup"><i class="fa fa-user"> </i> Sign Up</a> -->
            <?php else: ?>
                <button class="btn btn-default" data-toggle="modal" data-target="#login" ><i class="fa fa-photo"></i> New Item</button>
                <!--<a href="#" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a> -->
                <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#login"><i class="fa fa-sign-in"> </i> Login</a>
                <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#signup"><i class="fa fa-user"> </i> Sign Up</a>
            <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <h2 class="login-panel text-muted">
                    Recently Viewed
                </h2>
                <hr/>
            </div>
        </div>
        <div class="row">
            <?php foreach($recents as $recent): ?>
                <div class="col-md-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                                <?php echo $recent['title']; ?>
                            <span class="pull-right text-muted">
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if($recent['email'] == $_SESSION['email']): ?>
                                    <a class="" href="delete.php?id=<?php echo $recent['product_id']?>" data-toggle="tooltip" title="Delete item">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            </span>
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <a href="product.php?product=<?php echo $recent['product_id']; ?>">
                                    <img class="img-rounded img-thumbnail" src="<?php echo 'products/'.$recent['picture'];?>" witdth="100%" height="100%"/>
                                </a>
                            </p>
                            <p class="text-muted text-justify">
                                <?php echo $recent['description']; ?>
                            </p>
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if(alreadyVoted($recent['product_id'], $_SESSION['email'])): ?>
                                        <i class="fa fa-thumbs-down pull-left" style="color:red;"><?php echo $recent['votes']; ?></i>
                                <?php else: ?>
                                    <a class="pull-left" href="downvote.php?pid=<?php echo $recent['product_id']; ?>" data-toggle="tooltip" title="Downvote item" >
                                        <i class="fa fa-thumbs-down"><?php echo $recent['votes']; ?></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="panel-footer ">
                            <span><a href=<?php echo 'mailto:'.$recent['email']?> data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i><?php echo $recent['firstname'] .' '. $recent['lastname']; ?></a></span>
                            <span class="pull-right">$<?php echo $recent['price']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?> 

        </div>

        <div class="row">
            <div class="col-md-3">
                <h2 class="login-panel text-muted">
                    Items For Sale
                </h2>
                <hr/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                    <form class="form-inline" name="searchProducts" action="index.php" method="get">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <input name="search" type="text" class="form-control" placeholder="Search" />
                            </div>
                        </div>
                        <input type="submit" class="btn btn-default" value="Search"/>
                        <button class="btn btn-default" data-toggle="tooltip" title="Shareable Link!"><i class="fa fa-share"></i></button>
                    </form>
                <br/>
            </div>
        </div>

        <div class="row">
        <?php if(isset($_SESSION['loggedin'])): ?>
        <?php foreach($pins as $pin): ?>
                <div class="col-md-3">
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <a class="" href="unpin.php?pin=<?php echo $pin['pin_id'];?>" data-toggle="tooltip" title="Unpin item">
                                    <i class="fa fa-dot-circle-o"></i>
                                </a>
                            <?php endif; ?>
                            <span>
                                <?php echo $pin['title']; ?>
                            </span>
                            <span class="pull-right">
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if($pin['email'] == $_SESSION['email']): ?>
                                    <a class="" href="delete.php?id=<?php echo $pin['product_id']?>" data-toggle="tooltip" title="Delete item">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif;?>
                            </span>
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <a class="product" data-toggle="" data-target="#product" href="product.php?product=<?php echo $pin['product_id']; ?>">
                                    <img class="img-rounded img-thumbnail" src="<?php echo 'products/'.$pin['picture'];?>" witdth="100%" height="100%"/>
                                </a>
                            </p>
                            <p class="text-muted text-justify">
                                <?php echo $pin['description']; ?>
                            </p>
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if(alreadyVoted($pin['product_id'], $_SESSION['email'])): ?>
                                        <i class="fa fa-thumbs-down pull-left" style="color:red;"><?php echo $pin['votes']; ?></i>
                                <?php else: ?>
                                    <a class="pull-left" href="downvote.php?pid=<?php echo $pin['product_id']; ?>" data-toggle="tooltip" title="Downvote item" >
                                        <i class="fa fa-thumbs-down"><?php echo $pin['votes']; ?></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="panel-footer ">
                            <span><a href="<?php echo 'mailto:'.$pin['email']?>" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i><?php echo $pin['firstname'] .' '. $pin['lastname']; ?></a></span>
                            <span class="pull-right">$<?php echo $pin['price']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>    
            <?php endif; ?>
            <?php foreach($products as $product): ?>
                <div class="col-md-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <a class="" href="pin.php?pid=<?php echo $product['product_id'];?>&owner=<?php echo $product['user_id']; ?>" data-toggle="tooltip" title="Pin item">
                                    <i class="fa fa-thumb-tack"></i>
                                </a>
                            <?php endif; ?>
                            <span>
                                <?php echo $product['title']; ?>
                            </span>
                            <span class="pull-right">
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if($product['email'] == $_SESSION['email']): ?>
                                    <a class="" href="delete.php?id=<?php echo $product['product_id']?>" data-toggle="tooltip" title="Delete item">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif;?>
                            </span>
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <a class="product" data-toggle="" data-target="#product" href="product.php?product=<?php echo $product['product_id']; ?>">
                                    <img class="img-rounded img-thumbnail" src="<?php echo 'products/'.$product['picture'];?>"/>
                                </a>
                            </p>
                            <p class="text-muted text-justify">
                                <?php echo $product['description']; ?>
                            </p>
                            <?php if(isset($_SESSION['loggedin'])): ?>
                                <?php if(alreadyVoted($product['product_id'], $_SESSION['email'])): ?>
                                        <i class="fa fa-thumbs-down pull-left" style="color:red;"><?php echo $product['votes']; ?></i>
                                <?php else: ?>
                                    <a class="pull-left" href="downvote.php?pid=<?php echo $product['product_id']; ?>" data-toggle="tooltip" title="Downvote item" >
                                        <i class="fa fa-thumbs-down"><?php echo $product['votes']; ?></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="panel-footer ">
                            <span><a href="<?php echo 'mailto:'.$product['email']?>" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i><?php echo $product['firstname'] .' '. $product['lastname']; ?></a></span>
                            <span class="pull-right">$<?php echo $product['price']; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>    

        </div> <!-- -->
        
    </div>

</div>

<div id="login" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form name="login" role="form" method="post" action="redirect.php?from=login">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Login</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control"
                           name="email" 
                           type="text">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control"
                           name="password"
                           type="password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Login!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="signup" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form name="signup" role="form" method="post" action="redirect.php?from=signup">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Sign Up</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>First Name</label>
                    <input class="form-control"
                           name="firstname" 
                           type="text">
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input class="form-control" 
                           name="lastname"
                           type="text">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input class="form-control"
                           name="email"
                           type="text">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" 
                           name="password"
                           type="password">
                </div>
                <div class="form-group">
                    <label>Verify Password</label>
                    <input class="form-control"
                           name="verify_password" 
                           type="password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Sign Up!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="newItem" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
    <form role="form" method="post" action="index.php" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">New Item</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input class="form-control"
                           name="title"
                           type="text">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input class="form-control"
                           name="price"
                           type="number"
                           min="0.00"
                           max="9999999.99"
                           step="0.01">
                           
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input class="form-control"
                           name="description"
                           type="text">
                </div>
                <div class="form-group">
                    <label>Picture</label>
                    <input class="form-control"
                           name="picture"
                           type="file">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="Post Item!"/>
            </div>
        </div><!-- /.modal-content -->
    </form>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="product" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
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
                            Noodles
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <img class="img-rounded img-thumbnail" src="products/f88008dc63a67983e5824dafa0935662.png"/>
                            </p>
                            <p class="text-muted text-justify">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam et accumsan mauris, non faucibus massa. Maecenas ac dolor aliquet, euismod nisl ut, congue quam.
                            </p>
                        </div>
                        <div class="panel-footer ">
                            <span><a href=""><i class="fa fa-envelope"></i> Alex Akins</a></span>
                            <span class="pull-right">$11.99</span>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</body>
<script src="js/jquery.min.js"></script>
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
</html>