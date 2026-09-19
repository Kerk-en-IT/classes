<?php
if (!function_exists('varDump')) :
	/**
	 * var_dump all content
	 *
	 * @param  mixed $params
	 * @return void
	 */
	function varDump(...$params)
	{
		\KerkEnIT\varDump($params);
	}
endif;

if (!function_exists('varDie')) :
	function varDie(...$params)
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
	function log(...$params)
	{
		\KerkEnIT\log($params);
	}
endif;

if (!function_exists('error')) :
	function error(...$params)
	{
		\KerkEnIT\error($params);
	}
endif;
?>