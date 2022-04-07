<?php
define('SALT', 'katrinas_salty_salt_for_this_app');
define('MAX_FILE_SIZE', 5000000);

define('HOST', '127.0.0.1');
define('PORT', '8889');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DATABASE_NAME', 'comp3015_final');


function connect(){
    $link = mysqli_connect(HOST, DB_USERNAME, DB_PASSWORD, DATABASE_NAME, PORT);

    if(!$link){
        exit('MySQL Connection Error: ' . mysqli_connect_error());
    }

    return $link;
}


function findUser($email, $password){
    $found = false; 

    $link = connect();
    $hash = md5($password . SALT);

    $query = 'select * from users where email = "'.$email.'" && password = "'.$hash.'"';
    $results = mysqli_query($link, $query);

    //if at least one exists in the database (hoping to have no duplicates)
    if(mysqli_fetch_array($results)){
        $found = true;
    }

    mysqli_close($link);
    return $found;

}

function saveUser($data){
    $firstname = filterName(trim($data['firstname']));
    $lastname = filterName(trim($data['lastname']));
    $email = trim($data['email']);
    $password = md5($data['password'] . SALT);

    $link = connect();
    $query = 'insert into users(firstname, lastname, email, password)
              values ("'.$firstname.'", "'.$lastname.'", "'.$email.'", "'.$password.'")';
    
    $success = mysqli_query($link, $query);

    mysqli_close($link);

    return $success;
}

function validateName($name){
    return preg_match('/^[a-z][a-z\'\s]{1,34}$/i', $name);
}

function filterName($name){
    //additional whitespace to a single whitespace
    $filter0 = preg_replace("/(\s){2,}/", ' ', $name);

    //get rid of extra ' and replace with a single '
    $filter1 = preg_replace("/\'{2,}/","'", $filter0);

    //if it's not letters and symbols(' - \s), replace with an empty string
    $filter2 = preg_replace("/[^a-z\'\s]/i", '', $filter1);

    return $filter2;
}

function validateEmail($email){
    return preg_match('/((^[a-z][a-z\d._]{5,}[a-z\d]@bcit\.ca)|(^[a-z][a-z\d._]{3,}[a-z\d](@gmail\.com|@yahoo\.com))|(^[a-z][a-z\d._]{1,}[a-z\d](@outlook\.com|@hotmail\.com))|(^[a-z][a-z\d._]{2,}[a-z\d]@iCloud\.com))$/i', $email);
}

function validateSignup($data){
    $valid = true;

    if(trim($data['firstname'])       == '' ||
       trim($data['lastname'])        == '' ||
       trim($data['email'])           == '' ||
       trim($data['password'])        == '' ||
       trim($data['verify_password']) == ''  ){
        $valid = false;

    }elseif(!validateName(filterName(trim($data['firstname'])))){     
        $valid = false;
    }elseif(!validateName(filterName(trim($data['lastname'])))){
        $valid = false;
    }elseif(!validateEmail(trim($data['email']))){
        $valid = false;
    }elseif(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!#^*?])[a-zA-Z\d!#^*?]{8,}$/', $data['password'])){
        $valid = false;
    }elseif($data['password'] != $data['verify_password']){
        $valid = false; 
    }

    return $valid; 
}

function checkNewItem($file, $data){
    $valid = false;

    if(!($file['picture']['size'] < MAX_FILE_SIZE && ($file['picture']['type'] == 'image/jpeg' || $file['picture']['type'] == 'image/png'))){
        return 'Unable to upload image!';
    }elseif(!preg_match('/^[\w\s,!\': \/\(\)\-|]{1,150}$/i', trim($data['title']))){
        return 'only alphanumeric characters allowed!';
    }elseif(!preg_match('/^[\d,]{1,9}(\.[\d]{2})?$/', filterPrice(trim($data['price'])))){
        return 'only numbers, decimal and $ allowed!';
    }elseif(!preg_match('/^[\w\s,!\': \/\(\)\-|]{1,250}$/i', trim($data['description']))){
        return 'only alphanumeric characters allowed!';
    }else{
        return true; 
    }
}

function filterPrice($price){
    $filter = number_format($price, 2);

    return $filter;
}

function saveItem($email, $file){
    $image = md5($email . time());

    //returns false upon fail
    $moved = move_uploaded_file($file['picture']['tmp_name'], 'products/' . $image);

    //if successful, add file name into the database
    if($moved){
        $link = connect(); 
        //find id of current user 
        $query = 'select user_id from users where email="'.$email.'"';
        
        $result = mysqli_query($link, $query);

        $id = mysqli_fetch_array($result);

        if($id[0]){
            //store it in the database
            $query = 'insert into products (title, price, description, picture, user_id)
                      values ("'.trim($_POST['title']).'","'.filterPrice(trim($_POST['price'])).'","'.trim($_POST['description']).'","'.$image.'","'.$id[0].'")';
            $result = mysqli_query($link, $query);

        }

        mysqli_close($link);
        return $result;
    }
    return false; 
}

function getAllProducts($email){
    $user = getIdFromEmail($email); 
    $link = connect();
    $query = 'select p.product_id, p.title, p.price, p.description, p.picture, p.votes, u.user_id, u.firstname, u.lastname, u.email 
              from products p 
              inner join users u 
              on p.user_id = u.user_id
              where p.product_id NOT IN (
                  select product_id 
                  from pins
                  where pinner = "'.$user.'") && (unix_timestamp(p.created) < unix_timestamp(date_add(p.created, interval 1 hour)))';
    $products = mysqli_query($link, $query);
    $rows = mysqli_fetch_array($products);

    mysqli_close($link);
    return $products;
}

function getProducts(){
    $link = connect();
    $query = 'select p.product_id, p.title, p.price, p.description, p.picture, p.votes, u.user_id, u.firstname, u.lastname, u.email 
              from products p 
              inner join users u 
              on p.user_id = u.user_id
              where (unix_timestamp(p.created) < unix_timestamp(date_add(p.created, interval 1 hour)))';
    $products = mysqli_query($link, $query);
    $rows = mysqli_fetch_array($products);

    mysqli_close($link);
    return $products;
}

function getProduct($pid){
    $link = connect();
    $query = 'select p.product_id, p.title, p.price, p.description, p.picture, p.votes, u.firstname, u.lastname, u.email 
              from products p 
              inner join users u 
              on p.product_id = "'.$pid.'" && u.user_id = p.user_id && (unix_timestamp(p.created) < unix_timestamp(date_add(p.created, interval 1 hour)))';
    $product = mysqli_query($link, $query);
    $rows = mysqli_fetch_array($product);

    mysqli_close($link);

    //something went wrong
    if(!$rows){
        return false;
    }
    
    return $rows;
}

function filterID($id){
    return preg_replace("/[^\d]/", '', $id);
}

//takes product id and user's id 
function addCookie($pid){
    setcookie('temp', $pid, time() + 60*60);
}

function getIdFromEmail($email){
    $link = connect();
    $query = 'select user_id 
             from users where email="'.$email.'"';
    $result = mysqli_query($link, $query);
    $uid = mysqli_fetch_array($result);

    mysqli_close($link);
    return $uid[0];
}

function deleteProduct($pid, $email){ 
    $uid = getIdFromEmail($email);
    $link = connect();
    $query = 'delete from products where product_id = "'.$pid.'" && user_id = "'.$uid.'"';
    $success = mysqli_query($link, $query);
    
    mysqli_close($link);
    return $success;
}

function addPin($pid, $oid, $email){
    $pinner = getIdFromEmail($email);
    $link = connect(); 
    $query = 'insert into pins(pinner, user_id, product_id)
              values ("'.$pinner.'", "'.$oid.'", "'.$pid.'")';
    $success = mysqli_query($link, $query);
    
    if(!$success){
        echo mysqli_error(); 
    }

    return $success;
}

function removePin($pin, $email){
    $pinner = getIdFromEmail($email);
    $link = connect(); 
    $query = 'delete from pins
              where pin_id = "'.$pin.'" && pinner = "'.$pinner.'"';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    return $success;
}

function getAllPins($email){
    $uid = getIdFromEmail($email);
    $link = connect(); 
    $query = 'select a.pin_id, a.user_id, p.product_id, p.title, p.price, p.description, p.picture, p.votes, u.firstname, u.lastname, u.email 
              from products p, users u, pins a
              where a.user_id = u.user_id && a.product_id = p.product_id && a.pinner="'.$uid.'" && (unix_timestamp(p.created) < unix_timestamp(date_add(p.created, interval 1 hour)))';

    $pins = mysqli_query($link, $query);

    mysqli_close($link);
    return $pins;
}

function searchForProducts($str){
    $term = preg_replace("/[^\w\s,!\': \/\(\)\-|]/i", '', $str);
    $link = connect(); 
    $query = 'select p.product_id, p.title, p.price, p.description, p.picture, p.votes, u.user_id, u.firstname, u.lastname, u.email 
    from products p 
    inner join users u 
    on p.user_id = u.user_id && (p.title like "%'.$term.'%" || p.description like "%'.$term.'%") && (unix_timestamp(p.created) < unix_timestamp(date_add(p.created, interval 1 hour)))';
    
    $products = mysqli_query($link, $query);

    mysqli_close($link);
    return $products;
}

function addDownvote($pid, $email){
    $downvoter = getIdFromEmail($email);
    $link = connect(); 
    $query = 'insert into downvotes(user_id, product_id)
              values ("'.$downvoter.'", "'.$pid.'")';
    $success = mysqli_query($link, $query);

    mysqli_close($link);
    return $success;
}

function updateVotes($pid){
    $link = connect(); 
    $query = 'select count(*) 
              from downvotes 
              where product_id = "'.$pid.'"';
    $result = mysqli_query($link, $query);
    $count = mysqli_fetch_array($result);

    if($count[0] > 5){
        //delete the product
        $query = 'delete from products where product_id = "'.$pid.'"';
        $success = mysqli_query($link, $query);
        $delete = true;
    }else{
        //update count
        $query = 'update products 
                  set votes="'.$count[0].'"
                  where product_id = "'.$pid.'"';
        $success = mysqli_query($link, $query);
        $delete = false;
    }

    mysqli_close($link);
    return $delete;
}

function alreadyVoted($pid, $email){
    $found = false;
    $oid = getIdFromEmail($email);
    $link = connect();
    $query = 'select *
              from downvotes
              where user_id="'.$oid.'" && product_id="'.$pid.'"';
    $result = mysqli_query($link, $query);

    if(mysqli_fetch_array($result)){
        $found = true;
    }

    mysqli_close($link);
    return $found;
}