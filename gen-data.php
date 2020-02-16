<?php
/**
 * gen-data.php
 * Генерация данных (пачками) для таблиц
 * Всего 2 таблицы: t1 и t2 (которая имеет данные ссылающиеся на t1)
 * Использование: .../gen-data.php?table=NAME&count=COUNT где:
 *  - NAME - название таблицы (t1 или t2, при этом для t2 создаются данные на основании данных в t1)
 *  - COUNT - количество данных * SIZE_PATCH_GEN (config.php)
 * PHP Version 7.4
 * 
 * @author Buturlin Vitaliy (Byurrer), email: byurrer@mail.ru, site: byurrer.ru
 * @copyright 2020 Buturlin Vitaliy
 * @license MIT https://opensource.org/licenses/mit-license.php
 */

//##########################################################################

include("config.php");
include("db.php");

header("Content-type: text/plain; charset=utf-8");

$db = new CDB(DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASSWORD);

$sTable = $_GET["table"];
$iCount = $_GET["count"];

if($sTable !== 't1' && $sTable !== 't2' || $iCount < 1)
	exit("wrong data");

//##########################################################################

if($sTable == 't1')
{
	for($i=0, $il=$iCount; $i<$il; ++$i)
	{
		$sQuery = "INSERT IGNORE INTO t1 (`text`) VALUES ";

		for($k=0, $kl=SIZE_PATCH_GEN; $k<$kl; ++$k)
		{
			$sQuery .= "('".bin2hex(random_bytes(24))."')";
			if($k < SIZE_PATCH_GEN-1)
				$sQuery .= ", ";
		}

		$db->query($sQuery, null, false);
	}
}

//**************************************************************************

if($sTable == 't2')
{
	$aRes = $db->query("SELECT COUNT(*) as c FROM t1", null, true);
	$iCountT1 = $aRes[0]["c"];

	for($i=0, $il=$iCount; $i<$il; ++$i)
	{
		$sQuery = "INSERT IGNORE INTO t2 (`t1`, `status`) VALUES ";

		for($k=0, $kl=SIZE_PATCH_GEN; $k<$kl; ++$k)
		{
			$sQuery .= "(".random_int(1, $iCountT1).",".random_int(0, 2).")";
			if($k < SIZE_PATCH_GEN-1)
				$sQuery .= ", ";
		}

		$db->query($sQuery, null, false);
	}
}

//##########################################################################
