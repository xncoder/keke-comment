<?php	defined ( 'ADMIN_KEKE' ) or exit ( 'Access Denied' );
$views = array ('index','buy','config');
(! empty ( $view ) && in_array ( $view, $views ))  or  $view = 'index';
require ADMIN_ROOT . 'admin_payitem_' . $view . '.php';
