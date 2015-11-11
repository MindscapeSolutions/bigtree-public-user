<?
$result = PublicUser::login(array('email' => $_POST['e'], 'password' => $_POST['p']));
echo $result;
?>

