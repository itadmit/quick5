<?php
/**
 * יציאה מהמערכת
 */

require_once 'includes/auth.php';

$auth = new Authentication();
$result = $auth->logout();

// העברה לעמוד הבית
header('Location: /admin/login.php?message=' . urlencode('יצאת מהמערכת בהצלחה'));
exit; 