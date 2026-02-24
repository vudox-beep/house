<?php
require_once 'models/User.php';
$user = new User();
if (method_exists($user, 'loginWithGoogle')) {
    echo "Method exists!";
} else {
    echo "Method does NOT exist!";
    $methods = get_class_methods($user);
    print_r($methods);
}
?>