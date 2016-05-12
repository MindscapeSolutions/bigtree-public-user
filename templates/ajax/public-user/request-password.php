<?

$result = PublicUser::requestPassword(array('email' => $_POST['e'], 'code' => $_POST['c'], 'password' => $_POST['p']));
echo $result;

?>
