<?php

include("../viqgen-db.php");
include("../lib/php/functions.php");

//$ind_sql = "select * from bls_industries where source NOT LIKE '%/%' and source != 'Bill' and source != '' and source = '65621200' LIMIT 1 ";
//$ind_sql = "select * from bls_industries where source NOT LIKE '%/%' and source != 'Bill' and source != '' ";
$ind_sql = "select industry_id, bls_ppi_source from industry_elements where bls_ppi_source != 'NA' and bls_ppi_source != '' order by industry_id";

$result = $conn->query($ind_sql);

$i=1;
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		//echo $row["industry_id"]." -- ".$row["bls_ppi_source"]."\n\n";
		deleteOldBLSPPIRow($row["industry_id"]);

		$sources = explode("/", $row["bls_ppi_source"]);

		foreach ($sources as $value) {
			//getBLSPPIData($row["industry_id"], $value);
		  curlBLSPPIData($row["industry_id"], $value, $i);
      $i++;
    }

		insertBLSPPIChartRow($row["industry_id"]);

	}
}

?>