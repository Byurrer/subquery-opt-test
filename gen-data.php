<?php

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
