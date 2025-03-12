<?php
/**
 * Autoloader
 *
 * @author     Marco van 't Klooster, Kerk en IT <
 * @version    1.0
 */
foreach (glob(realpath(__DIR__) . '/class.*.php') as $filename) {
	//if($filename == __FILE__) {
	//	continue;
	//}
	require_once($filename);
}
?>