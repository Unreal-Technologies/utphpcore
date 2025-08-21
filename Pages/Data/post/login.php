<?php
if(!isset($_POST['username']) || !isset($_POST['password']))
{
    echo new \Utphpcore\Core\Modalresult(false, 'Invalid input.');
    exit;
}
$dbc = Utphpcore\IO\Data\Db\Database::getInstance('Core');
$dbc -> query('select `id` from `user` where `username` = "'.$_POST['username'].'" and `password` = user_password("'.$_POST['password'].'")');
$result = $dbc -> execute();
if($result['iRowCount'] === 0)
{
    echo new \Utphpcore\Core\Modalresult(false, 'Wrong Username or Password.');
    exit;
}
else 
{
    $userId = $result['aResults'][0]['id'];

    $dbc -> query('select `instance-id` from `user-instance` where `user-id` = '.$userId);
    $result = $dbc -> execute();
    
    $isAdministrator = false;
    $instanceIds = [];
    foreach($result['aResults'] as $row)
    {
        if($row['instance-id'] === null)
        {
            $isAdministrator = true;
            break;
        }
        else
        {
            $instanceIds[] = $row['instance-id'];
        }
    }
    
    $token = new \Utphpcore\Core\AuthenticationToken($userId, $instanceIds, $isAdministrator);
    Utphpcore\Data\Cache::set(Utphpcore\Data\CacheTypes::Session, \Utphpcore\Core::Authentication, $token);
    
    $result = new \Utphpcore\Core\Modalresult(true, 'Login Success.');
    $result -> reload();
    
    echo $result;
    exit;
}