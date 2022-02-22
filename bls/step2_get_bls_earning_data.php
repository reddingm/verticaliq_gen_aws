<?php

include("../viqgen-db.php");
include("../lib/php/functions.php");

//$ind_sql = "select * from bls_industries where source NOT LIKE '%/%' and source != 'Bill' and source != '' and source = '65621200' LIMIT 1 ";
//$ind_sql = "select * from bls_industries where source NOT LIKE '%/%' and source != 'Bill' and source != '' ";
$ind_sql = "select industry_id, bls_wage_source from industry_elements where bls_wage_source != 'NA' and bls_wage_source != '' order by industry_id";

$result = $conn->query($ind_sql);

$i=1;
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		//echo $row["industry_id"]." -- ".$row["bls_emp_source"]."\n\n";
		deleteOldBLSEarRow($row["industry_id"]);
		
		$sources = explode("/", $row["bls_wage_source"]);
		
		foreach ($sources as $value) {
			//getBLSWageData($row["industry_id"], $value);
		  curlBLSWageData($row["industry_id"], $value, $i);
      $i++;
    }
		
		insertBLSWageChartRow($row["industry_id"]);
		
	}
}

?>