<?php
if (!function_exists('varDump')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function varDump(mixed ...$params)
	{
		\KerkEnIT\varDump($params);
	}
endif;

if (!function_exists('varDie')) :
	function varDie(mixed ...$params)
	{
		\KerkEnIT\varDie($params);
	}
endif;

if (!function_exists('log')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function log(mixed ...$params)
	{
		\KerkEnIT\log($params);
	}
endif;

if (!function_exists('error')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function error(mixed ...$params)
	{
		\KerkEnIT\error($params);
	}
endif;
?>