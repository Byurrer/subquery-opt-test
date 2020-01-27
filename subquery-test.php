<?php

include("config.php");
include("db.php");

header("Content-type: text/plain; charset=utf-8");

$db = new CDB(DB_HOST, DB_NAME, DB_CHARSET, DB_USER, DB_PASSWORD);
/*$db->query("SET GLOBAL innodb_buffer_pool_size=5242880;
SET GLOBAL innodb_buffer_pool_chunk_size = 5242880;", null, false);*/

//**************************************************************************

// 1) запрос с подзапросом, самый простой способ
$fTime1 = microtime(true);
$aRes1 = $db->query("SELECT  * FROM t1 WHERE id IN(SELECT `t1` FROM `t2` WHERE status=0) LIMIT 1");
$sErr1 = $db->getErrorText();
$fTime1 = microtime(true)-$fTime1;

//**************************************************************************

// 2) формирование запроса с перебросом данных на пхп
$fTime2 = microtime(true);
$aInner = $db->query("SELECT  `t1` FROM `t2` WHERE status=0", null, true);
$aInner2 = [];
foreach($aInner as $value)
	$aInner2[] = $value["t1"];
$sInner = implode(",", $aInner2);
$aRes2 = $db->query("SELECT  * FROM t1 WHERE id IN($sInner) LIMIT 1");
$sErr2 = $db->getErrorText();
$fTime2 = microtime(true)-$fTime2;

//**************************************************************************

// 3) формирование запроса с перебросом данных на php, при этом список создается на стороне бд
$fTime3 = microtime(true);
$db->query("SET group_concat_max_len = CAST('-1' AS UNSIGNED)", null, false);
$aInner3 = $db->query("SELECT  GROUP_CONCAT(`t1`) AS t1 FROM `t2` WHERE status=0", null, true);
$sInner3 = $aInner3[0]["t1"];
$aRes3 = $db->query("SELECT  * FROM t1 WHERE id IN($sInner3) LIMIT 1");
$sErr3 = $db->getErrorText();
$fTime3 = microtime(true)-$fTime3;

//**************************************************************************

// 4) генерация запроса без вложенного подзапроса на стороне субд
$fTime4 = microtime(true);
$db->query(
	"SET group_concat_max_len = CAST('-1' AS UNSIGNED);
	SET @s1 = CONCAT(
		\"SELECT  * FROM t1 WHERE id IN('\",
			REPLACE(
				(SELECT  GROUP_CONCAT(`t1`) AS t1 FROM `t2` WHERE status=0), 
				\",\", 
				\"','\"
			),
		\"') LIMIT 1\"
	);
	PREPARE stmt1 FROM @s1;
",
null, false
);
$aRes4 = $db->query("EXECUTE stmt1;", null, true);
/*$aRes4 = $db->query("SELECT @s1 as s;");
exit($aRes4[0]["s"]);
$aRes4 = $db->query($aRes4[0]["s"]);*/
$sErr4 = $db->getErrorText();
$fTime4 = microtime(true)-$fTime4;

//##########################################################################

/*$aVerify = [];
foreach($aRes2 as $value)
	$aVerify[] = $value["id"];

foreach($aRes2 as $value)
{
	if(!in_array($value["id"], $aVerify))
		echo "aRes2 id ".$value["id"]." not found\n";
}

foreach($aRes3 as $value)
{
	if(!in_array($value["id"], $aVerify))
		echo "aRes3 id ".$value["id"]." not found\n";
}

foreach($aRes4 as $value)
{
	if(!in_array($value["id"], $aVerify))
		echo "aRes4 id ".$value["id"]." not found\n";
}*/

print_r( [
	"time1" => $fTime1, "time2" => $fTime2, "time3" => $fTime3, "time4" => $fTime4, 
	"err1" => $sErr1, "err2" => $sErr2, "err3" => $sErr3, "err4" => $sErr4,
	"res1_count" => count($aRes1), "res2_count" => count($aRes2), "res3_count" => count($aRes3), "res4_count" => count($aRes4),
	"count_inner" => count($aInner),
]);
