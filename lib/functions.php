<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
function mysql_esc($value){
	global $conn;
	$returnstring = mysqli_real_escape_string($conn, $value);

	return $returnstring;
}

function mnz($value){
	//make negative zero
	//for pie charts, if negative make zero
	if($value < 0){
		return 0;
	}else{
		return $value;
	}
}

function getIndustries(){

	global $conn;
  $indsql = "select * from industries order by id";
	//$indsql = "select * from industries where published = 1 order by id ";
	$industries = $conn->query($indsql);
	
	return $industries;	

}

function getIndustrieswithLimit($limit){

	global $conn;
  $indsql = "select * from industries order by id LIMIT ".$limit;
	$industries = $conn->query($indsql);
	
	return $industries;	

}

function getIndustriesMerged(){
	global $conn;
  $indsql = "select i.id, i.name, i.report_cover_image, ip.report_cover_image as prod_report_cover_image
      from industries i
      LEFT JOIN industries ip on i.id = ip.id
      order by i.name";
	//$indsql = "select * from industries where published = 1 order by id ";
	$industries = $conn->query($indsql);
	
	return $industries;	
  
}

function deleteOldIndData($indid){
	global $conn;
	$delsql = "delete from chartgen where industry_id = $indid";
	$industries = $conn->query($delsql);
}

function getIndustryTableData($indid){
	global $conn;
	
	$indsql = "select * from industries where id = '$indid' LIMIT 1";
	$inddata = $conn->query($indsql);
	$inddatarow = $inddata->fetch_assoc();
	
	return $inddatarow;
}

/*
function getBizMiner($indid){
	global $conn;
	
	$bizsql = "select * from bizminers where industry_id = $indid and comp_class = 'industry-wide' order by year DESC LIMIT 1";

	$bizminer = $conn->query($bizsql);
	$bizminerrow = $bizminer->fetch_assoc();
	
	return $bizminerrow;

}
*/

function getPW($indid){
	global $conn;
	
	$pwsql = "select * from industry_financial_benchmarks where industry_id = $indid and comp_class = 'industry-wide' order by year DESC LIMIT 1";

	$pwminer = $conn->query($pwsql);
	$pwminerrow = $pwminer->fetch_assoc();
	
	return $pwminerrow;

}

function getBusinessValuationRows($indid, $col){
	global $conn;
	
	$bvsql = "select sale_date, $col from business_valuations where industry_id = $indid and $col != ''";
	$bvdata = $conn->query($bvsql);
	//$inddatarow = $inddata->fetch_assoc();

	$data_array = array();

	$dataString = '';
	$i=0;
	if ($bvdata->num_rows > 0) {
		while($row = $bvdata->fetch_assoc()) {
			//$data_array[$row["year"]] = $row["value"];
			$dataString .= "['".$row["sale_date"]."',".$row[$col]."]";
			$i++;
			if($i != $bvdata->num_rows){
				$dataString .= ",";
			}
		}
	}

	//$bizminer = $conn->query($bizsql);
	//$bizminerrow = $bizminer->fetch_assoc();
	//echo $dataString;
	//die();
	return $dataString;

}

function getIndStructure($indid){
	global $conn;
  
  //Legal Structure - Corporation 53
	//Legal Structure - S-Corporation 54
  //Legal Structure - Individual Proprietorship 55
  //Legal Structure - Partnership 56
  //Legal Structure - Non-profit/Other 57
  
  
	$isdatasql = "select * from target_data where (target_id = '53' or target_id = '54' or target_id = '55' or target_id = '56' or target_id = '57') and industry_id = $indid";
	$isdata = $conn->query($isdatasql);

  $data_array = array();
  
	if ($isdata->num_rows > 0) {
		while($row = $isdata->fetch_assoc()) {
      $data_array[$row["target_id"]] = $row["data_value"];
		}
	}
  
  //print_r($data_array);
  //die();
  $dataString = "['Corporations', ".$data_array[53]."],
    ['S-Corporations', $data_array[54]],
    ['Individual Proprietorships', $data_array[55]],
    ['Partnerships', $data_array[56]],
    ['Non-profit/Other', $data_array[57]]";

	return $dataString;

}
function hasIndStructure($indid){
	
  global $conn;
	$isdatasql2 = "select * from target_data where (target_id = '53' or target_id = '54' or target_id = '55' or target_id = '56' or target_id = '57') and industry_id = $indid";
	$isdata2 = $conn->query($isdatasql2);
  
  if ($isdata2->num_rows > 0) {
    $hasIS = "true"; 
  }else{
    $hasIS =  "false";
  }
  return $hasIS;
  
}

/*v1
function getChartConfig($indid){
	global $conn;
	
	$indsql = "select ck.*, cs.*
		from chartkey ck, chartsectors cs
		where ck.industry_id = '$indid'
		and ck.sector = cs.sector_title 
		and ck.year = 2015 LIMIT 1";
	$industries = $conn->query($indsql);
	$configrow = $industries->fetch_assoc();

	return $configrow;

}

function getWCMChart($chartconfig,$bizminerdata){
	$dataarray = array();
	if($chartconfig['days_inventory'] == 1){
		$dataarray['col1data1'] = $bizminerdata['days_inventory'];
		$dataarray['col1data2'] = $chartconfig['sector_days_inventory'];
		$dataarray['col1title'] = "Days Inventory";
	}else if($chartconfig['salary_wages_percent'] == 1){
		$dataarray['col1data1'] = $bizminerdata['salary_wages_percent'];
		$dataarray['col1data2'] = $chartconfig['sector_salary_wages_percent'];
		$dataarray['col1title'] = "Salaries to Sales";
	}
	
	
	if($chartconfig['days_receivables'] == 2){
		$dataarray['col2data1'] = $bizminerdata['days_receivables'];
		$dataarray['col2data2'] = $chartconfig['sector_days_receivables'];
		$dataarray['col2title'] = "Days Receivables";
	}else if($chartconfig['salary_wages_percent'] == 2){
		$dataarray['col2data1'] = $bizminerdata['salary_wages_percent'];
		$dataarray['col2data2'] = $chartconfig['sector_salary_wages_percent'];
		$dataarray['col2title'] = "Salaries to Sales";
	}else if($chartconfig['bad_debt_percent'] == 2){
		$dataarray['col2data1'] = $bizminerdata['bad_debt_percent'];
		$dataarray['col2data2'] = $chartconfig['sector_bad_debt_percent'];
		$dataarray['col2title'] = "Bad Debt to Sales";
	}
	
	if($chartconfig['days_payable'] == 3){
		$dataarray['col3data1'] = $bizminerdata['days_payable'];
		$dataarray['col3data2'] = $chartconfig['sector_days_payable'];
		$dataarray['col3title'] = "Days Payables";
	}else if($chartconfig['rent_percent'] == 3){
		$dataarray['col3data1'] = $bizminerdata['rent_percent'];
		$dataarray['col3data2'] = $chartconfig['sector_rent_percent'];
		$dataarray['col3title'] = "Rent to Sales %";
	}

	return $dataarray;
	
}

function getProfChart($chartconfig,$bizminerdata){
	$dataarray = array();
	if($chartconfig['gross_margin_percent'] == 1){
		$dataarray['col1data1'] = $bizminerdata['gross_margin_percent'];
		$dataarray['col1data2'] = $chartconfig['sector_gross_margin_percent'];
		$dataarray['col1title'] = "Gross Margin %";
	}else if($chartconfig['ebitda_percent'] == 1){
		$dataarray['col1data1'] = $bizminerdata['ebitda_percent'];
		$dataarray['col1data2'] = $chartconfig['sector_ebitda_percent'];
		$dataarray['col1title'] = "EBITDA to Sales %";
	}
	
	
		$dataarray['col2data1'] = $bizminerdata['operating_income_percent'];
		$dataarray['col2data2'] = $chartconfig['sector_operating_income_percent'];
		$dataarray['col2title'] = "Operating Income %";
	

		$dataarray['col3data1'] = $bizminerdata['pre_tax_return_on_assets'];
		$dataarray['col3data2'] = $chartconfig['sector_pre_tax_return_on_assets'];
		$dataarray['col3title'] = "Pre-Tax Return On Assets %";
	
	return $dataarray;
	
}

*/
/*Financial Benchmark Data 2 */

function getChartConfig2($indid){
	global $conn;
	
	$indsql = "select i.*, ie.* from industries i, industry_elements ie where i.id = ie.industry_id and i.id = '$indid'";
	$industries = $conn->query($indsql);
	$ind_row = $industries->fetch_assoc();

	return $ind_row;
}

function getSectorLookupData($sector_code){	
	global $conn;
	
	$sector_cols = "select * from chart_sector_lookup where sector_code = '$sector_code'";
	$columns = $conn->query($sector_cols);
	$columns_row = $columns->fetch_assoc();

	return $columns_row;
}

/*
function getSectorBZData($sector_naics){
	global $conn;
	
	$s_bz_sql = "select * from bizminers where naics = '$sector_naics' and comp_class = 'industry-wide' order by year DESC LIMIT 1";		
	$sector_bz = $conn->query($s_bz_sql);
	$sector_bz_row = $sector_bz->fetch_assoc();

	return $sector_bz_row;
}
*/

function getSectorPWData($sector_naics){
	global $conn;
	
	$s_pw_sql = "select * from industry_financial_benchmarks where naics = $sector_naics and comp_class = 'industry-wide' order by year DESC LIMIT 1";		
  $sector_pw = $conn->query($s_pw_sql);
	if($sector_pw){
	  $sector_pw_row = $sector_pw->fetch_assoc();
  }
  
	return $sector_pw_row;
}

function getWCMChart2($chartconfig,$bizminerdata,$sector_lookup_data,$sector_bz_data){
	$dataarray = array();
	
	if($sector_lookup_data['wcm_col1'] == 'days_inventory'){
		$dataarray['col1data1'] = $bizminerdata['days_inventory'];
		$dataarray['col1data2'] = $sector_bz_data['days_inventory'];
		$dataarray['col1title'] = "Days Inventory";
	}else if($sector_lookup_data['wcm_col1'] == 'days_receivables'){
		$dataarray['col1data1'] = $bizminerdata['days_receivables'];
		$dataarray['col1data2'] = $sector_bz_data['days_receivables'];
		$dataarray['col1title'] = "Days Receivables";
	}else if($sector_lookup_data['wcm_col1'] == 'salary_wages_percent'){
		$dataarray['col1data1'] = $bizminerdata['salary_wages_percent'];
		$dataarray['col1data2'] = $sector_bz_data['salary_wages_percent'];
		$dataarray['col1title'] = "Salary & Wages %";
	}

	if($sector_lookup_data['wcm_col2'] == 'days_inventory'){
		$dataarray['col2data1'] = $bizminerdata['days_inventory'];
		$dataarray['col2data2'] = $sector_bz_data['days_inventory'];
		$dataarray['col2title'] = "Days Inventory";
	}else if($sector_lookup_data['wcm_col2'] == 'days_payable'){
		$dataarray['col2data1'] = $bizminerdata['days_payable'];
		$dataarray['col2data2'] = $sector_bz_data['days_payable'];
		$dataarray['col2title'] = "Days Payables";
	}else if($sector_lookup_data['wcm_col2'] == 'rent_percent'){
		$dataarray['col2data1'] = $bizminerdata['rent_percent'];
		$dataarray['col2data2'] = $sector_bz_data['rent_percent'];
		$dataarray['col2title'] = "Rent to Sales %";
	}else if($sector_lookup_data['wcm_col2'] == 'salary_wages_percent'){
		$dataarray['col2data1'] = $bizminerdata['salary_wages_percent'];
		$dataarray['col2data2'] = $sector_bz_data['salary_wages_percent'];
		$dataarray['col2title'] = "Salary & Wages %";
	}
	
	if($sector_lookup_data['wcm_col3'] == 'bad_debt_percent'){
		$dataarray['col3data1'] = $bizminerdata['bad_debt_percent'];
		$dataarray['col3data2'] = $sector_bz_data['bad_debt_percent'];
		$dataarray['col3title'] = "Bad Debt %";
	}else if($sector_lookup_data['wcm_col3'] == 'days_payable'){
		$dataarray['col3data1'] = $bizminerdata['days_payable'];
		$dataarray['col3data2'] = $sector_bz_data['days_payable'];
		$dataarray['col3title'] = "Days Payables";
	}else if($sector_lookup_data['wcm_col3'] == 'rent_percent'){
		$dataarray['col3data1'] = $bizminerdata['rent_percent'];
		$dataarray['col3data2'] = $sector_bz_data['rent_percent'];
		$dataarray['col3title'] = "Rent to Sales %";
	}else if($sector_lookup_data['wcm_col3'] == 'salary_wages_percent'){
		$dataarray['col3data1'] = $bizminerdata['salary_wages_percent'];
		$dataarray['col3data2'] = $sector_bz_data['salary_wages_percent'];
		$dataarray['col3title'] = "Salary & Wages %";
	}
	
	return $dataarray;
		
}

function getProfChart2($chartconfig,$bizminerdata,$sector_lookup_data,$sector_bz_data){
	$dataarray = array();
	if($sector_lookup_data['prof_col1'] == 'gross_margin_percent'){
		$dataarray['col1data1'] = $bizminerdata['gross_margin_percent'];
		$dataarray['col1data2'] = $sector_bz_data['gross_margin_percent'];
		$dataarray['col1title'] = "Gross Margin %";
	}else if($sector_lookup_data['prof_col1'] == 'operating_income_percent'){
		$dataarray['col1data1'] = $bizminerdata['operating_income_percent'];
		$dataarray['col1data2'] = $sector_bz_data['operating_income_percent'];
		$dataarray['col1title'] = "Operating Income %";
	}

	if($sector_lookup_data['prof_col2'] == 'operating_income_percent'){
		$dataarray['col2data1'] = $bizminerdata['operating_income_percent'];
		$dataarray['col2data2'] = $sector_bz_data['operating_income_percent'];
		$dataarray['col2title'] = "Operating Income %";
	}else if($sector_lookup_data['prof_col2'] == 'pre_tax_return_on_assets'){
		$dataarray['col2data1'] = $bizminerdata['pre_tax_return_on_assets'];
		$dataarray['col2data2'] = $sector_bz_data['pre_tax_return_on_assets'];
		$dataarray['col2title'] = "Pre-Tax Return On Assets %";
	}
	
	if($sector_lookup_data['prof_col3'] == 'pre_tax_return_on_assets'){
		$dataarray['col3data1'] = $bizminerdata['pre_tax_return_on_assets'];
		$dataarray['col3data2'] = $sector_bz_data['pre_tax_return_on_assets'];
		$dataarray['col3title'] = "Pre-Tax Return On Assets %";
	}else if($sector_lookup_data['prof_col3'] == 'ebitda_percent'){
		$dataarray['col3data1'] = $bizminerdata['ebitda_percent'];
		$dataarray['col3data2'] = $sector_bz_data['ebitda_percent'];
		$dataarray['col3title'] = "EBITDA to Sales %";
	}

	return $dataarray;
}

/*END Financial Benchmark Data 2 */


function getIndForChart($indid){
	global $conn;
	
	$ifsql = "select * from industry_forecasts where industry_id = $indid";
	$forecast = $conn->query($ifsql);
	$forecastrow = $forecast->fetch_assoc();
	
	return $forecastrow;

}

function insertChartData($indid, $chart, $imgdata){
	global $conn;
	
	//del old chart
	$del_old_chart = "delete from chartgen where industry_id = '$indid' and chart = '$chart'";
	$deloldchart = $conn->query($del_old_chart);
	
	$insertSQL = "insert into chartgen set industry_id = $indid, chart = '$chart', chartdata = '$imgdata' ";
	$insert = $conn->query($insertSQL);
	
}

function determineOutputFileName($shortname, $charttype){
	// $root_dir = "/Users/reddingm/Code/VerticalIQ_generators/charts/output";
	$root_dir = getcwd() . '/output';

	switch($charttype){
		case 'benchmarks_cash_liquidity':
			return $root_dir."/".$shortname."_cash-liquidity.png";
			break;
		case 'benchmarks_working_capital':
			return $root_dir."/".$shortname."_working-capital-mgt.png";
			break;
		case 'benchmarks_profitability':
			return $root_dir."/".$shortname."_profitability.png";
			break;
		case 'snapshot_income_statement':
			return $root_dir."/".$shortname."_income-statement.png";
			break;
		case 'snapshot_working_capital':
			return $root_dir."/".$shortname."_working-capital.png";
			break;	
		case 'size_forecast':
			return $root_dir."/".$shortname."_forecast.png";
			break;
		case 'trends_employment':
			return $root_dir."/".$shortname."_bls_employees.png";
			break;
		case 'trends_wages':
			return $root_dir."/".$shortname."_bls_wages.png";
			break;
  	case 'trends_ppi':
  		return $root_dir."/".$shortname."_bls_ppi.png";
  		break;
		case 'hfo_revenue':
			return $root_dir."/".$shortname."_how_firms_operate_revenue.png";
			break;
		case 'hfo_revenue_per_employee':
			return $root_dir."/".$shortname."_how_firms_operate_rev_per_emp.png";
			break;
		case 'bv_price_to_sales':
			return $root_dir."/".$shortname."_bv_price_to_sales.png";
			break;
		case 'bv_price_to_gross_profits':
			return $root_dir."/".$shortname."_bv_price_to_gross_profits.png";
			break;
		case 'bv_price_to_ebitda':
			return $root_dir."/".$shortname."_bv_price_to_ebitda.png";
			break;
		case 'bv_price_to_ebit':
			return $root_dir."/".$shortname."_bv_price_to_ebit.png";
			break;
		case 'porter_chart':
			return $root_dir."/".$shortname."_porter_chart.svg";
			break;	
    case 'wc_cash_flow_stress':
  		return $root_dir."/".$shortname."_wc_cash_flow_stress.png";
  		break;
    case 'imports_exports':
  		return $root_dir."/".$shortname."_imports_exports.png";
  		break;
    case 'industry_structure':
    	return $root_dir."/".$shortname."_legal_structure.png";
    	break;
    case 'global_exports':
    	return $root_dir."/".$shortname."_global_exports.png";
    	break;
    case 'global_imports':
    	return $root_dir."/".$shortname."_global_imports.png";
    	break;      
    case 'global_trade':
    	return $root_dir."/".$shortname."_global_trade.png";
    	break;  
	}	
}

function determineEmailFileName($shortname, $charttype){
	// $root_dir = "/Users/reddingm/Code/VerticalIQ_generators/charts/output";
	$root_dir = getcwd() . '/insight_images';

	switch($charttype){
		case 'benchmarks_cash_liquidity':
			return $root_dir."/".$shortname."_cash-liquidity.png";
			break;
		case 'benchmarks_working_capital':
			return $root_dir."/".$shortname."_working-capital-mgt.png";
			break;
		case 'benchmarks_profitability':
			return $root_dir."/".$shortname."_profitability.png";
			break;
		case 'snapshot_income_statement':
			return $root_dir."/".$shortname."_income-statement.png";
			break;
		case 'snapshot_working_capital':
			return $root_dir."/".$shortname."_working-capital.png";
			break;	
		case 'size_forecast':
			return $root_dir."/".$shortname."_forecast.png";
			break;
		case 'trends_employment':
			return $root_dir."/".$shortname."_bls_employees.png";
			break;
		case 'trends_wages':
			return $root_dir."/".$shortname."_bls_wages.png";
			break;
  	case 'trends_ppi':
  		return $root_dir."/".$shortname."_bls_ppi.png";
  		break;
		case 'hfo_revenue':
			return $root_dir."/".$shortname."_how_firms_operate_revenue.png";
			break;
		case 'hfo_revenue_per_employee':
			return $root_dir."/".$shortname."_how_firms_operate_rev_per_emp.png";
			break;
		case 'bv_price_to_sales':
			return $root_dir."/".$shortname."_bv_price_to_sales.png";
			break;
		case 'bv_price_to_gross_profits':
			return $root_dir."/".$shortname."_bv_price_to_gross_profits.png";
			break;
		case 'bv_price_to_ebitda':
			return $root_dir."/".$shortname."_bv_price_to_ebitda.png";
			break;
		case 'bv_price_to_ebit':
			return $root_dir."/".$shortname."_bv_price_to_ebit.png";
			break;
		case 'porter_chart':
			return $root_dir."/".$shortname."_porter_chart.svg";
			break;	
    case 'wc_cash_flow_stress':
  		return $root_dir."/".$shortname."_wc_cash_flow_stress.png";
  		break;
    case 'imports_exports':
  		return $root_dir."/".$shortname."_imports_exports.png";
  		break;
    case 'industry_structure':
    	return $root_dir."/".$shortname."_legal_structure.png";
    	break;
	}	
}

function determineOutputJustFileName($shortname, $charttype){
	// $root_dir = "/Users/reddingm/Code/VerticalIQ_generators/charts/output";
	$root_dir = getcwd() . '/output';

	switch($charttype){
		case 'benchmarks_cash_liquidity':
			return $shortname."_cash-liquidity.png";
			break;
		case 'benchmarks_working_capital':
			return $shortname."_working-capital-mgt.png";
			break;
		case 'benchmarks_profitability':
			return $shortname."_profitability.png";
			break;
		case 'snapshot_income_statement':
			return $shortname."_income-statement.png";
			break;
		case 'snapshot_working_capital':
			return $shortname."_working-capital.png";
			break;	
		case 'size_forecast':
			return $shortname."_forecast.png";
			break;
		case 'trends_employment':
			return $shortname."_bls_employees.png";
			break;
		case 'trends_wages':
			return $shortname."_bls_wages.png";
			break;
  	case 'trends_ppi':
  		return $shortname."_bls_ppi.png";
  		break;
		case 'hfo_revenue':
			return $shortname."_how_firms_operate_revenue.png";
			break;
		case 'hfo_revenue_per_employee':
			return $shortname."_how_firms_operate_rev_per_emp.png";
			break;
		case 'bv_price_to_sales':
			return $shortname."_bv_price_to_sales.png";
			break;
		case 'bv_price_to_gross_profits':
			return $shortname."_bv_price_to_gross_profits.png";
			break;
		case 'bv_price_to_ebitda':
			return $shortname."_bv_price_to_ebitda.png";
			break;
		case 'bv_price_to_ebit':
			return $shortname."_bv_price_to_ebit.png";
			break;
		case 'porter_chart':
			return $shortname."_porter_chart.svg";
			break;	
    case 'wc_cash_flow_stress':
  		return $shortname."_wc_cash_flow_stress.png";
  		break;
    case 'imports_exports':
  		return $shortname."_imports_exports.png";
  		break;
    case 'industry_structure':
    	return $shortname."_legal_structure.png";
    	break;
	}	
}

/* BLS Functions */
/* Emp */
function getBLSSourceData($industry_id){
	global $conn;
	
	$bls_sr_sql = "select * from industry_elements where industry_id = '$industry_id'";
	$bls_sr = $conn->query($bls_sr_sql);
	$bls_sr_row = $bls_sr->fetch_assoc();

	return $bls_sr_row;
}

function getBLSEmpData($industry_id, $source_id){
	global $conn;
	//global $viq_bls_api_key;
	global $matteus10_bls_api_key;
  global $agilefy_bls_api_key;
  global $matticloud_bls_emp_api_key;
	
	echo $industry_id." -- ".$source_id."\n";
	//echo $viq_bls_api_key;
	//die();
	//$bls_industries_id = $row["id"];
	//$source = $source_id;
	//$full_ind = $source_id;

	$url = 'https://api.bls.gov/publicAPI/v2/timeseries/data/';
	$method = 'POST';
	$query = array(
		'seriesid'  => array('CEU'.$source_id.'01'),
		'startyear' => '2007',
		'endyear'   => '2020',
		'annualaverage' => 'true',
		'registrationkey' => $matticloud_bls_emp_api_key,
		'calculations' => 'true'
		);

	//print_r($query);
	//die();
	//echo "\n\n\n";
	$pd = json_encode($query);
  echo $pd;
  echo "\n";
  //die();
	$contentType = 'Content-Type: application/json';
	$contentLength = 'Content-Length: ' . strlen($pd);
	
	$bls_result = file_get_contents(
		$url, null, stream_context_create(
			array(
				'http' => array(
					'method' => $method,
					'header' => $contentType . "\r\n" . $contentLength . "\r\n",
					'content' => $pd
				),
			)
		)
	);
	print_r($bls_result);
  die();
	$array = json_decode( $bls_result, true );
	$i =0;
	if($array){
		//print_r($array);
		
		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];
	
			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];
	
			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];
	
			insertBLSEmpRow($rA);
	
		}
		$i++;
	}
	
}

function curlBLSEmpData($industry_id, $source_id, $count){
	global $conn;
	//global $viq_bls_api_key;
	global $bls2021_1;
  global $bls2021_2;
  
  if($count <= 425){
    $bls_key = $bls2021_1;
  }else{
    $bls_key = $bls2021_2;
  }
  
  echo $industry_id." -- ".$source_id." -- count: ".$count." -- key:".$bls_key." \n";
  
	$query = array(
		'seriesid'  => array('CEU'.$source_id.'01'),
		'startyear' => '2007',
		'endyear'   => '2021',
		'annualaverage' => 'true',
		'registrationkey' => $bls_key,
		'calculations' => 'true'
	);

	$data_string = json_encode($query);
  
  $ch = curl_init('https://api.bls.gov/publicAPI/v2/timeseries/data/');                                                                      
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
      'Content-Type: application/json',                                                                                
      'Content-Length: ' . strlen($data_string))                                                                       
  );                                                                                                                   
                                                                                                                     
  $result = curl_exec($ch);

	$array = json_decode( $result, true );
	$i =0;
	if($array){
		//print_r($array);
		
		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];
	
			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];
	
			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];
	
			insertBLSEmpRow($rA);
	
		}
    $i++;
    
  }
}

function deleteOldBLSEmpRow($industry_id) {
	global $conn;
	
	$del_bls_api_data_emp = "delete from bls_api_data_emp where industry_id = '$industry_id'";
	$industries = $conn->query($del_bls_api_data_emp);
	//echo "deleted bls_api_data_emp industry_id = ".$industry_id."\n\n";

}

function insertBLSEmpRow($rA) {
	global $conn;
	
	$sql = "insert into bls_api_data_emp set";
	$sql .= " `industry_id` = '".$rA['industry_id']."',";
	$sql .= " `source_id` = '".$rA['source_id']."',"; 
 	$sql .= " `year` = '".$rA['year']."',"; 
 	$sql .= " `period` = '".$rA['period']."',"; 
 	$sql .= " `periodName` = '".$rA['periodName']."',"; 
	$sql .= " `latest` = '".$rA['latest']."',"; 
 	$sql .= " `value` = '".$rA['value']."',"; 
 	
	$sql .= " `footnotes_code` = '".$rA['footnotes_code']."',"; 
	$sql .= " `footnotes_text` = '".$rA['footnotes_text']."',"; 

	$sql .= " `cal_nc_1` = '".$rA['cal_nc_1']."',"; 
	$sql .= " `cal_nc_3` = '".$rA['cal_nc_3']."',";
	$sql .= " `cal_nc_6` = '".$rA['cal_nc_6']."',"; 
	$sql .= " `cal_nc_12` = '".$rA['cal_nc_12']."',";
	
	$sql .= " `cal_pct_1` = '".$rA['cal_pct_1']."',"; 
	$sql .= " `cal_pct_3` = '".$rA['cal_pct_3']."',";
	$sql .= " `cal_pct_6` = '".$rA['cal_pct_6']."',"; 
	$sql .= " `cal_pct_12` = '".$rA['cal_pct_12']."'";

  $result = $conn->query($sql);
}

function insertBLSEmpChartRow($industry_id){
	//echo $industry_id."\n\n\n";
	global $conn;
	$bls_source_data = getBLSSourceData($industry_id);
	$bls_mostrecent = getMostRecentBLSEmpRow($industry_id);
	$bls_mostrecent_yearback = getMostRecentLastYearBLSEmpRow($industry_id, $bls_mostrecent['year'], $bls_mostrecent['periodName']);
	$bls_lasttenannual = get10AnnualBLSEmpRow($industry_id);
	//echo $bls_source_data['bls_emp_name']."\n";
	//echo $bls_mostrecent['value']."\n";
	//echo $bls_mostrecent_yearback['value']."\n";
	//die();
	
	$emp_title = buildEmpTitleString($bls_source_data['bls_emp_name'], $bls_mostrecent['value'], $bls_mostrecent_yearback['value']);
	$emp_text = buildEmpTextString($bls_source_data['bls_emp_name'], $bls_mostrecent['value'], $bls_mostrecent['periodName'], $bls_mostrecent_yearback['value']);
	
	//$current_label = formatBLSMonth($bls_mostrecent['periodName'])."-".trimBLSYear($bls_mostrecent['year']);
	//$current_value = $bls_mostrecent['value'];

	//$bls_mostrecent_yearback = getMostRecentLastYearBLSEmpRow($industry_id, $bls_mostrecent['year'], $bls_mostrecent['period']);
	//$bls_lasttenannual = get10AnnualBLSEmpRow($industry_id);
	
	$del_sql = "delete from bls_chart_emp_data where industry_id =  '$industry_id'";
	$delete = $conn->query($del_sql);
	
	//print_r($bls_lasttenannual);
	//echo "matt";
	//die();
	
	$sql = "insert into bls_chart_emp_data set";
	$sql .= " `industry_id` 	= '".$industry_id."',";
	$sql .= " `current_label` = '".formatBLSMonth($bls_mostrecent['periodName'])."-".trimBLSYear($bls_mostrecent['year'])."',";
	$sql .= " `current_value` = '".$bls_mostrecent['value']."',";
	$sql .= " `prev_label` 		= '".formatBLSMonth($bls_mostrecent_yearback['periodName'])."-".trimBLSYear($bls_mostrecent_yearback['year'])."',";
	$sql .= " `prev_value` 		= '".$bls_mostrecent_yearback['value']."',";
	$sql .= " `val_2008` 			= '".$bls_lasttenannual['2008']."',";
	$sql .= " `val_2009` 			= '".$bls_lasttenannual['2009']."',";
	$sql .= " `val_2010` 			= '".$bls_lasttenannual['2010']."',";
	$sql .= " `val_2011` 			= '".$bls_lasttenannual['2011']."',";
	$sql .= " `val_2012` 			= '".$bls_lasttenannual['2012']."',";
	$sql .= " `val_2013` 			= '".$bls_lasttenannual['2013']."',";
	$sql .= " `val_2014` 			= '".$bls_lasttenannual['2014']."',";
	$sql .= " `val_2015` 			= '".$bls_lasttenannual['2015']."',";
	$sql .= " `val_2016` 			= '".$bls_lasttenannual['2016']."',";
	$sql .= " `val_2017` 			= '".$bls_lasttenannual['2017']."',";
	$sql .= " `val_2018` 			= '".$bls_lasttenannual['2018']."',";
  $sql .= " `val_2019` 			= '".$bls_lasttenannual['2019']."',";
  $sql .= " `val_2020` 			= '".$bls_lasttenannual['2020']."',";
  $sql .= " `val_2021` 			= '".$bls_lasttenannual['2021']."',";
	$sql .= " `emp_title` 			= '".mysql_esc($emp_title)."',";	
	$sql .= " `emp_text` 			= '".mysql_esc($emp_text)."'";
	//echo $sql."\n\n\n\n\n\n\n";
	$result = $conn->query($sql);
	
}

function getMostRecentBLSEmpRow($industry_id){
	global $conn;

	//original
	//$mr_bls_emp_sql = "select ROUND(SUM(value),2) as value, year, periodName from bls_api_data_emp where industry_id = '$industry_id' and periodName != 'Annual' and latest = 'true'";
	//Switching from latest = true to footnotes_text = preliminary
  //$mr_bls_emp_sql = "select ROUND(SUM(value),2) as value, year, periodName from bls_api_data_emp where industry_id = '$industry_id' and periodName != 'Annual' and footnotes_text = 'preliminary'";
  //most recent row, no prelim or latest flag
  //$mr_bls_emp_sql = "select ROUND(value,2) as value, year, periodName from bls_api_data_emp where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 1";
  /*
  //2020 work
  //Annual row was marked as latest for some data. now, if annual is latest, we trash and pull next row.
  $mr_bls_emp_sql = "select * from bls_api_data_emp where industry_id = '$industry_id' order by year DESC, period DESC LIMIT 1";
	$mr_bls_emp = $conn->query($mr_bls_emp_sql);
  $mr_bls_emp_row = $mr_bls_emp->fetch_assoc();
  
  if($mr_bls_emp_row["periodName"] != 'Annual' && $mr_bls_emp_row["latest"] == 'true'){
    return $mr_bls_emp_row;
  }else{
    $mr_bls_emp_sql2 = "select * from bls_api_data_emp where industry_id = '$industry_id' order by year DESC, period DESC LIMIT 1,1";
  	$mr_bls_emp2 = $conn->query($mr_bls_emp_sql2);
    $mr_bls_emp_row2 = $mr_bls_emp2->fetch_assoc();
    return $mr_bls_emp_row2;
  }
  */
  //2020 work 2
  $mr_bls_emp_sql = "select * from bls_api_data_emp where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 4";
  $mr_bls_emp = $conn->query($mr_bls_emp_sql);

  $data_array = array();
  
	if ($mr_bls_emp->num_rows > 0) {
    $prev_periodName = '';
		while($row = $mr_bls_emp->fetch_assoc()) {
      if($prev_periodName == ''){
        //echo "in 1";
        //add first one to db
        //set prev = first one
        $data_array['year'] = $row["year"];
        $data_array['periodName'] = $row["periodName"];
        $data_array['value'] = $row["value"];
        
        $prev_periodName = $row["periodName"];
      }elseif($prev_periodName == $row["periodName"]){
        //echo "in 2";
        //add next one to previous
        $data_array['value'] = $data_array['value'] + $row["value"];
        $prev_periodName = $row["periodName"];
      }else{
        //echo "in 3";
        //do nothing;
      }  
		}
	}
  //echo "\n\n";
  //print_r($data_array);
  return $data_array;

}

function getMostRecentLastYearBLSEmpRow($industry_id, $year, $periodName){
	global $conn;
	
	$year_back = $year -1;
	
	$mr_bls_ly_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_emp where industry_id = '$industry_id' and periodName != 'Annual' and year = '$year_back' and periodName = '$periodName'";
	$mr_bls_ly_emp = $conn->query($mr_bls_ly_emp_sql);
	$mr_bls_ly_emp_row = $mr_bls_ly_emp->fetch_assoc();

	return $mr_bls_ly_emp_row;
}

function get10AnnualBLSEmpRow($industry_id){
	global $conn;
	
	$annual_bls_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_emp where `industry_id` = '$industry_id' and periodName = 'Annual' group by year order by year DESC LIMIT 30";
	$annual_bls_emp = $conn->query($annual_bls_emp_sql);
	//$annual_bls_emp_rows = $annual_bls_emp->fetch_assoc();
	//echo $annual_bls_emp_sql;
	//die();
	$data_array = array();
	$i = 0;
	if ($annual_bls_emp->num_rows > 0) {
		while($row = $annual_bls_emp->fetch_assoc()) {
			//$data_array[$i]['year'] = $row["year"];
			//$data_array[$i]['value'] = $row["value"];
			$data_array[$row["year"]] = $row["value"];
			//$i++;
		}
	}

//	die();
//	print_r($data_array);
//	die();
	return $data_array;
	
}

function getBLSEmpForChart($industry_id){
	global $conn;
	
	$bls_emp_sql = "select * from bls_chart_emp_data where industry_id = '$industry_id'";
	$bls_emp_data = $conn->query($bls_emp_sql);
	$bls_emp_row = $bls_emp_data->fetch_assoc();

	return $bls_emp_row;
	
}

function buildEmpTitleString($title, $current_value, $prev_value){
	$percent_change = number_format(((($current_value / $prev_value) - 1) * 100),2);
	if($percent_change >= 0.50){
		$header_metric = 'increases';
	}else if($percent_change <= -0.50){
		$header_metric = 'decreases';
	}else{
		$header_metric = 'is relatively flat';
	}
	
	return "Employment by ".$title." ".$header_metric;
}

function buildEmpTextString($title, $current_value, $current_month, $prev_value){
	
	$percent_change = ((($current_value / $prev_value) - 1) * 100);
	
	return "Overall employment by ".$title." changed ".number_format($percent_change, 1)."% in ".$current_month." compared to a year ago, according to the latest data from the Bureau of Labor Statistics.";
}

/* Wages */
function getBLSWageData($industry_id, $source_id){
	global $conn;
	global $ba_bls_api_key;
	global $bagmail_bls_api_key_alt;
  global $agilefy_bls_api_key2;
  global $matticloud_bls_ear_api_key;
	
	echo $industry_id." -- ".$source_id."\n";

	$url = 'https://api.bls.gov/publicAPI/v2/timeseries/data/';
	$method = 'POST';
	$query = array(
		'seriesid'  => array('CEU'.$source_id.'08'),
		'startyear' => '2007',
		'endyear'   => '2020',
		'annualaverage' => 'true',
		'registrationkey' => $matticloud_bls_ear_api_key,
		'calculations' => 'true'
		);

	//, 'CEU'.$full_ind.'08'
	$pd = json_encode($query);
	$contentType = 'Content-Type: application/json';
	$contentLength = 'Content-Length: ' . strlen($pd);

	$bls_result = file_get_contents(
		$url, null, stream_context_create(
			array(
				'http' => array(
					'method' => $method,
					'header' => $contentType . "\r\n" . $contentLength . "\r\n",
					'content' => $pd
				),
			)
		)
	);
	//var_dump($http_response_header);
	//print_r($result);

	//die();
	$array = json_decode( $bls_result, true );
	$i =0;
	if($array){
		//print_r($array);
				
		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];
	
			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];
	
			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];
			
			insertBLSEarRow($rA);
	
		}
		$i++;
	}
}

function curlBLSWageData($industry_id, $source_id, $count){
	global $conn;
	//global $viq_bls_api_key;
	global $bls2021_3;
  global $bls2021_4;

  if($count <= 425){
    $bls_key = $bls2021_3;
  }else{
    $bls_key = $bls2021_4;
  }

  echo $industry_id." -- ".$source_id." -- count: ".$count." -- key:".$bls_key." \n";
  
	$query = array(
		'seriesid'  => array('CEU'.$source_id.'08'),
		'startyear' => '2007',
		'endyear'   => '2021',
		'annualaverage' => 'true',
		'registrationkey' => $bls_key,
		'calculations' => 'true'
		);

	$data_string = json_encode($query);

  $ch = curl_init('https://api.bls.gov/publicAPI/v2/timeseries/data/');                                                                      
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
      'Content-Type: application/json',                                                                                
      'Content-Length: ' . strlen($data_string))                                                                       
  );                                                                                                                   
                                                                                                                     
  $result = curl_exec($ch);

	$array = json_decode( $result, true );
	$i =0;
	if($array){
		//print_r($array);
    
		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];
	
			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];
	
			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];
			
			insertBLSEarRow($rA);
	
		}
    $i++;
    
  }
}

function deleteOldBLSEarRow($industry_id) {
	global $conn;
	
	$del_bls_api_data_wages = "delete from bls_api_data_wages where industry_id = '$industry_id'";
	$industries = $conn->query($del_bls_api_data_wages);
	//echo "deleted bls_api_data_wages industry_id = ".$industry_id."\n\n";

}

function insertBLSEarRow($rA) {
	global $conn;
	
	$sql = "insert into bls_api_data_wages set";
	$sql .= " `industry_id` = '".$rA['industry_id']."',";
	$sql .= " `source_id` = '".$rA['source_id']."',"; 
 	$sql .= " `year` = '".$rA['year']."',"; 
 	$sql .= " `period` = '".$rA['period']."',"; 
 	$sql .= " `periodName` = '".$rA['periodName']."',"; 
	$sql .= " `latest` = '".$rA['latest']."',"; 
 	$sql .= " `value` = '".$rA['value']."',"; 
 	
	$sql .= " `footnotes_code` = '".$rA['footnotes_code']."',"; 
	$sql .= " `footnotes_text` = '".$rA['footnotes_text']."',"; 

	$sql .= " `cal_nc_1` = '".$rA['cal_nc_1']."',"; 
	$sql .= " `cal_nc_3` = '".$rA['cal_nc_3']."',";
	$sql .= " `cal_nc_6` = '".$rA['cal_nc_6']."',"; 
	$sql .= " `cal_nc_12` = '".$rA['cal_nc_12']."',";
	
	$sql .= " `cal_pct_1` = '".$rA['cal_pct_1']."',"; 
	$sql .= " `cal_pct_3` = '".$rA['cal_pct_3']."',";
	$sql .= " `cal_pct_6` = '".$rA['cal_pct_6']."',"; 
	$sql .= " `cal_pct_12` = '".$rA['cal_pct_12']."'";
	//echo $sql."\n\n";
  $result = $conn->query($sql);
}

function insertBLSWageChartRow($industry_id){
	global $conn;
	
	$bls_source_data = getBLSSourceData($industry_id);
	$bls_mostrecent = getMostRecentBLSEarRow($industry_id);
	$bls_mostrecent_yearback = getMostRecentLastYearBLSEarRow($industry_id, $bls_mostrecent['year'], $bls_mostrecent['periodName']);
	$bls_lasttenannual = get10AnnualBLSEarRow($industry_id);
	
	$wage_title = buildWageTitleString($bls_source_data['bls_wage_name'], $bls_mostrecent['value'], $bls_mostrecent_yearback['value']);
	$wage_text = buildWageTextString($bls_source_data['bls_wage_name'], $bls_mostrecent['value'], $bls_mostrecent['periodName'], $bls_mostrecent_yearback['value']);
	
	$del_sql = "delete from bls_chart_wage_data where industry_id =  '$industry_id'";
	$delete = $conn->query($del_sql);
	
	//print_r($bls_lasttenannual);
	//echo "matt";
	//die();
	
	$sql = "insert into bls_chart_wage_data set";
	$sql .= " `industry_id` 	= '".$industry_id."',";
	$sql .= " `current_label` = '".formatBLSMonth($bls_mostrecent['periodName'])."-".trimBLSYear($bls_mostrecent['year'])."',";
	$sql .= " `current_value` = '".number_format($bls_mostrecent['value'], 2)."',";
	$sql .= " `prev_label` 		= '".formatBLSMonth($bls_mostrecent_yearback['periodName'])."-".trimBLSYear($bls_mostrecent_yearback['year'])."',";
	$sql .= " `prev_value` 		= '".number_format($bls_mostrecent_yearback['value'], 2)."',";
	$sql .= " `val_2008` 			= '".number_format($bls_lasttenannual['2008'], 2)."',";
	$sql .= " `val_2009` 			= '".number_format($bls_lasttenannual['2009'], 2)."',";
	$sql .= " `val_2010` 			= '".number_format($bls_lasttenannual['2010'], 2)."',";
	$sql .= " `val_2011` 			= '".number_format($bls_lasttenannual['2011'], 2)."',";
	$sql .= " `val_2012` 			= '".number_format($bls_lasttenannual['2012'], 2)."',";
	$sql .= " `val_2013` 			= '".number_format($bls_lasttenannual['2013'], 2)."',";
	$sql .= " `val_2014` 			= '".number_format($bls_lasttenannual['2014'], 2)."',";
	$sql .= " `val_2015` 			= '".number_format($bls_lasttenannual['2015'], 2)."',";
	$sql .= " `val_2016` 			= '".number_format($bls_lasttenannual['2016'], 2)."',";
	$sql .= " `val_2017` 			= '".number_format($bls_lasttenannual['2017'], 2)."',";
  $sql .= " `val_2018` 			= '".number_format($bls_lasttenannual['2018'], 2)."',";
  $sql .= " `val_2019` 			= '".number_format($bls_lasttenannual['2019'], 2)."',";
  $sql .= " `val_2020` 			= '".number_format($bls_lasttenannual['2020'], 2)."',";
  $sql .= " `val_2021` 			= '".number_format($bls_lasttenannual['2021'], 2)."',";
	$sql .= " `wage_title` 			= '".$wage_title."',";	
	$sql .= " `wage_text` 			= '".$wage_text."'";
	//echo $sql;
	$result = $conn->query($sql);
	//die();
}

function getMostRecentBLSEarRow($industry_id){
	global $conn;
	/*
  //original
	$mr_bls_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_wages where industry_id = '$industry_id' and periodName != 'Annual' and latest = 'true'";
	//Switching from latest = true to footnotes_text = preliminary
  //$mr_bls_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_wages where industry_id = '$industry_id' and periodName != 'Annual' and footnotes_text = 'preliminary'";
	//most recent row, no prelim or latest flag
  //$mr_bls_emp_sql = "select ROUND(value,2) as value, year, periodName from bls_api_data_wages where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 1";
    
  $mr_bls_emp = $conn->query($mr_bls_emp_sql);
	$mr_bls_emp_row = $mr_bls_emp->fetch_assoc();

	return $mr_bls_emp_row;
  */
  //2020 work 2
  $mr_bls_ear_sql = "select * from bls_api_data_wages where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 4";
  $mr_bls_ear = $conn->query($mr_bls_ear_sql);

  $data_array = array();
  
	if ($mr_bls_ear->num_rows > 0) {
    $prev_periodName = '';
		while($row = $mr_bls_ear->fetch_assoc()) {
      if($prev_periodName == ''){
        //echo "in 1";
        //add first one to db
        //set prev = first one
        $data_array['year'] = $row["year"];
        $data_array['periodName'] = $row["periodName"];
        $data_array['value'] = $row["value"];
        
        $prev_periodName = $row["periodName"];
      }elseif($prev_periodName == $row["periodName"]){
        //echo "in 2";
        //add next one to previous
        $data_array['value'] = $data_array['value'] + $row["value"];
        $prev_periodName = $row["periodName"];
      }else{
        //echo "in 3";
        //do nothing;
      }  
		}
	}
  //echo "\n\n";
  //print_r($data_array);
  return $data_array;
	
}

function getMostRecentLastYearBLSEarRow($industry_id, $year, $periodName){
	global $conn;
	
	$year_back = $year -1;
	
	$mr_bls_ly_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_wages where industry_id = '$industry_id' and periodName != 'Annual' and year = '$year_back' and periodName = '$periodName'";
	$mr_bls_ly_emp = $conn->query($mr_bls_ly_emp_sql);
	$mr_bls_ly_emp_row = $mr_bls_ly_emp->fetch_assoc();

	return $mr_bls_ly_emp_row;
}

function get10AnnualBLSEarRow($industry_id){
		global $conn;

		$annual_bls_emp_sql = "select SUM(value) as value, year, periodName from bls_api_data_wages where `industry_id` = '$industry_id' and periodName = 'Annual' group by year order by year DESC LIMIT 30";
		$annual_bls_emp = $conn->query($annual_bls_emp_sql);
		//$annual_bls_emp_rows = $annual_bls_emp->fetch_assoc();
		//echo $annual_bls_emp_sql;
		//die();
		$data_array = array();
		$i = 0;
		if ($annual_bls_emp->num_rows > 0) {
			while($row = $annual_bls_emp->fetch_assoc()) {
				$data_array[$row["year"]] = $row["value"];
				//$i++;
			}
		}
	//	die();
	//	print_r($data_array);
	//	die();
		return $data_array;
	
}

function getBLSEarForChart($industry_id){
	global $conn;
	
	$bls_emp_sql = "select * from bls_chart_wage_data where industry_id = '$industry_id'";
	//echo $bls_emp_sql;
	//die();
	$bls_emp_data = $conn->query($bls_emp_sql);
	$bls_emp_row = $bls_emp_data->fetch_assoc();

	return $bls_emp_row;
	
}

function buildWageTitleString($title, $current_value, $prev_value){
	$percent_change = number_format(((($current_value / $prev_value) - 1) * 100),2);
	if($percent_change >= 0.50){
		$header_metric = 'rise';
	}else if($percent_change <= -0.50){
		$header_metric = 'fall';
	}else{
		$header_metric = 'stay flat';
	}
	
	return "Wages at ".$title." ".$header_metric;
}

function buildWageTextString($title, $current_value, $current_month, $prev_value){
	$percent_change = ((($current_value / $prev_value) - 1) * 100);

	return "Average wages for nonsupervisory employees at ".$title." were $".number_format($current_value, 2)." per hour in ".$current_month.", a ".number_format($percent_change, 1)."% change compared to a year ago.";
}

//----------------------------------------------------------------------------------------------
/* PPI */

function getBLSPPIData($industry_id, $source_id){
	global $conn;
	global $ba_bls_api_key;
	global $bagmail_bls_api_key_alt;
  global $agilefy_bls_api_key2;
  global $ccrisp_bls_api_key;

	echo $industry_id." -- ".$source_id."\n";

	$url = 'https://api.bls.gov/publicAPI/v2/timeseries/data/';
	$method = 'POST';
	$query = array(
		'seriesid'  => array('PCU'.$source_id.'41'),
		'startyear' => '2007',
		'endyear'   => '2020',
		'annualaverage' => 'true',
		'registrationkey' => $ccrisp_bls_api_key,
		'calculations' => 'true'
		);

	//, 'CEU'.$full_ind.'08'
	$pd = json_encode($query);
	$contentType = 'Content-Type: application/json';
	$contentLength = 'Content-Length: ' . strlen($pd);

	$bls_result = file_get_contents(
		$url, null, stream_context_create(
			array(
				'http' => array(
					'method' => $method,
					'header' => $contentType . "\r\n" . $contentLength . "\r\n",
					'content' => $pd
				),
			)
		)
	);
	//var_dump($http_response_header);
	//print_r($result);

	//die();
	$array = json_decode( $bls_result, true );
	$i =0;
	if($array){
		//print_r($array);

		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];

			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];

			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];

			insertBLSPPIRow($rA);

		}
		$i++;
	}
}

function curlBLSPPIData($industry_id, $source_id, $count){
	global $conn;
	//global $viq_bls_api_key;
	global $bls2021_5;
  global $bls2021_6;

  if($count <= 425){
    $bls_key = $bls2021_5;
  }else{
    $bls_key = $bls2021_6;
  }

  echo $industry_id." -- ".$source_id." -- count: ".$count." -- key:".$bls_key." \n";

	$query = array(
		'seriesid'  => array('PCU'.$source_id),
		'startyear' => '2007',
		'endyear'   => '2021',
		'annualaverage' => 'true',
		'registrationkey' => $bls_key,
		'calculations' => 'true'
		);

	$data_string = json_encode($query);

  $ch = curl_init('https://api.bls.gov/publicAPI/v2/timeseries/data/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
  );

  $result = curl_exec($ch);

	$array = json_decode( $result, true );
	$i =0;
	if($array){
		//print_r($array);

		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];

			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];

			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];

			insertBLSPPIRow($rA);
		}
    $i++;
  }

}

function deleteOldBLSPPIRow($industry_id) {
	global $conn;

	$del_bls_api_data_ppi = "delete from bls_api_data_ppi where industry_id = '$industry_id'";
	$industries = $conn->query($del_bls_api_data_ppi);
	//echo "deleted bls_api_data_ppi industry_id = ".$industry_id."\n\n";

}

function insertBLSPPIRow($rA) {
	global $conn;

	$sql = "insert into bls_api_data_ppi set";
	$sql .= " `industry_id` = '".$rA['industry_id']."',";
	$sql .= " `source_id` = '".$rA['source_id']."',";
 	$sql .= " `year` = '".$rA['year']."',";
 	$sql .= " `period` = '".$rA['period']."',";
 	$sql .= " `periodName` = '".$rA['periodName']."',";
	$sql .= " `latest` = '".$rA['latest']."',";
 	$sql .= " `value` = '".$rA['value']."',";

	$sql .= " `footnotes_code` = '".$rA['footnotes_code']."',";
	$sql .= " `footnotes_text` = '".$rA['footnotes_text']."',";

	$sql .= " `cal_nc_1` = '".$rA['cal_nc_1']."',";
	$sql .= " `cal_nc_3` = '".$rA['cal_nc_3']."',";
	$sql .= " `cal_nc_6` = '".$rA['cal_nc_6']."',";
	$sql .= " `cal_nc_12` = '".$rA['cal_nc_12']."',";

	$sql .= " `cal_pct_1` = '".$rA['cal_pct_1']."',";
	$sql .= " `cal_pct_3` = '".$rA['cal_pct_3']."',";
	$sql .= " `cal_pct_6` = '".$rA['cal_pct_6']."',";
	$sql .= " `cal_pct_12` = '".$rA['cal_pct_12']."'";
	//echo $sql."\n\n";
  $result = $conn->query($sql);
	//print_r($conn);
	//die();
	//echo $result;
	//die();
}

function insertBLSPPIChartRow($industry_id){
	global $conn;
	//print_r($conn);
	//die();

	$bls_source_data = getBLSSourceData($industry_id);
	$bls_mostrecent = getMostRecentBLSPPIRow($industry_id);
	$bls_mostrecent_yearback = getMostRecentLastYearBLSPPIRow($industry_id, $bls_mostrecent['year'], $bls_mostrecent['periodName']);
	$bls_lasttenannual = get10AnnualBLSPPIRow($industry_id);
	//echo $bls_mostrecent_yearback['value']."\n";
	//die();

	$ppi_title = buildPPITitleString($bls_source_data['bls_ppi_name'], $bls_mostrecent['value'], $bls_mostrecent_yearback['value']);
	$ppi_text = buildPPITextString($bls_source_data['bls_ppi_name'], $bls_mostrecent['value'], $bls_mostrecent['periodName'], $bls_mostrecent_yearback['value']);

	$del_sql = "delete from bls_chart_ppi_data where industry_id =  '$industry_id'";
	$delete = $conn->query($del_sql);

	//print_r($bls_lasttenannual);
	//echo "matt";
	//die();

	$sql = "insert into bls_chart_ppi_data set";
	$sql .= " `industry_id` 	= '".$industry_id."',";
	$sql .= " `current_label` = '".formatBLSMonth($bls_mostrecent['periodName'])."-".trimBLSYear($bls_mostrecent['year'])."',";
	$sql .= " `current_value` = '".number_format($bls_mostrecent['value'], 2)."',";
	$sql .= " `prev_label` 		= '".formatBLSMonth($bls_mostrecent_yearback['periodName'])."-".trimBLSYear($bls_mostrecent_yearback['year'])."',";
	$sql .= " `prev_value` 		= '".number_format($bls_mostrecent_yearback['value'], 2)."',";
	$sql .= " `val_2008` 			= '".number_format($bls_lasttenannual['2008'], 2)."',";
	$sql .= " `val_2009` 			= '".number_format($bls_lasttenannual['2009'], 2)."',";
	$sql .= " `val_2010` 			= '".number_format($bls_lasttenannual['2010'], 2)."',";
	$sql .= " `val_2011` 			= '".number_format($bls_lasttenannual['2011'], 2)."',";
	$sql .= " `val_2012` 			= '".number_format($bls_lasttenannual['2012'], 2)."',";
	$sql .= " `val_2013` 			= '".number_format($bls_lasttenannual['2013'], 2)."',";
	$sql .= " `val_2014` 			= '".number_format($bls_lasttenannual['2014'], 2)."',";
	$sql .= " `val_2015` 			= '".number_format($bls_lasttenannual['2015'], 2)."',";
	$sql .= " `val_2016` 			= '".number_format($bls_lasttenannual['2016'], 2)."',";
	$sql .= " `val_2017` 			= '".number_format($bls_lasttenannual['2017'], 2)."',";
  $sql .= " `val_2018` 			= '".number_format($bls_lasttenannual['2018'], 2)."',";
  $sql .= " `val_2019` 			= '".number_format($bls_lasttenannual['2019'], 2)."',";
  $sql .= " `val_2020` 			= '".number_format($bls_lasttenannual['2020'], 2)."',";
  $sql .= " `val_2021` 			= '".number_format($bls_lasttenannual['2021'], 2)."',";
	$sql .= " `ppi_title` 			= '".$ppi_title."',";
	$sql .= " `ppi_text` 			= '".$ppi_text."'";
	//echo $sql;
	$result = $conn->query($sql);
	//echo $result;
	//die();
}

function getMostRecentBLSPPIRow($industry_id){
	global $conn;
	/*
  //original
	$mr_bls_ppi_sql = "select SUM(value) as value, year, periodName from bls_api_data_ppi where industry_id = '$industry_id' and periodName != 'Annual' and latest = 'true'";
	//Switching from latest = true to footnotes_text = preliminary
  //$mr_bls_ppi_sql = "select SUM(value) as value, year, periodName from bls_api_data_ppi where industry_id = '$industry_id' and periodName != 'Annual' and footnotes_text = 'preliminary'";
	//most recent row, no prelim or latest flag
  //$mr_bls_ppi_sql = "select ROUND(value,2) as value, year, periodName from bls_api_data_ppi where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 1";

  $mr_bls_ppi = $conn->query($mr_bls_ppi_sql);
	$mr_bls_ppi_row = $mr_bls_ppi->fetch_assoc();

	return $mr_bls_ppi_row;
  */
  //2020 work 2
  $mr_bls_ppi_sql = "select * from bls_api_data_ppi where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 4";
  $mr_bls_ppi = $conn->query($mr_bls_ppi_sql);

  $data_array = array();

	if ($mr_bls_ppi->num_rows > 0) {
    $prev_periodName = '';
		while($row = $mr_bls_ppi->fetch_assoc()) {
      if($prev_periodName == ''){
        //echo "in 1";
        //add first one to db
        //set prev = first one
        $data_array['year'] = $row["year"];
        $data_array['periodName'] = $row["periodName"];
        $data_array['value'] = $row["value"];

        $prev_periodName = $row["periodName"];
      }elseif($prev_periodName == $row["periodName"]){
        //echo "in 2";
        //add next one to previous
        $data_array['value'] = $data_array['value'] + $row["value"];
        $prev_periodName = $row["periodName"];
      }else{
        //echo "in 3";
        //do nothing;
      }
		}
	}
  //echo "\n\n";
  //print_r($data_array);
	//die();
  return $data_array;

}

function getMostRecentLastYearBLSPPIRow($industry_id, $year, $periodName){
	global $conn;

	$year_back = $year -1;

	$mr_bls_ly_ppi_sql = "select SUM(value) as value, year, periodName from bls_api_data_ppi where industry_id = '$industry_id' and periodName != 'Annual' and year = '$year_back' and periodName = '$periodName'";
	$mr_bls_ly_ppi = $conn->query($mr_bls_ly_ppi_sql);
	$mr_bls_ly_ppi_row = $mr_bls_ly_ppi->fetch_assoc();

	return $mr_bls_ly_ppi_row;
}

function get10AnnualBLSPPIRow($industry_id){
		global $conn;

		$annual_bls_ppi_sql = "select SUM(value) as value, year, periodName from bls_api_data_ppi where `industry_id` = '$industry_id' and periodName = 'Annual' group by year order by year DESC LIMIT 30";
		$annual_bls_ppi = $conn->query($annual_bls_ppi_sql);
		//$annual_bls_ppi_rows = $annual_bls_ppi->fetch_assoc();
		//echo $annual_bls_ppi_sql;
		//die();
		$data_array = array();
		$i = 0;
		if ($annual_bls_ppi->num_rows > 0) {
			while($row = $annual_bls_ppi->fetch_assoc()) {
				$data_array[$row["year"]] = $row["value"];
				//$i++;
			}
		}
	//	die();
		//print_r($data_array);
		//die();
		return $data_array;

}

function getBLSPPIForChart($industry_id){
	global $conn;

	$bls_ppi_sql = "select * from bls_chart_ppi_data where industry_id = '$industry_id'";
	//echo $bls_ppi_sql;
	//die();
	$bls_ppi_data = $conn->query($bls_ppi_sql);
	$bls_ppi_row = $bls_ppi_data->fetch_assoc();

	return $bls_ppi_row;

}

function buildPPITitleString($title, $current_value, $prev_value){
	$percent_change = number_format(((($current_value / $prev_value) - 1) * 100),2);
	if($percent_change >= 0.50){
		$header_metric = 'rise';
	}else if($percent_change <= -0.50){
		$header_metric = 'fall';
	}else{
		$header_metric = 'stay flat';
	}

	return "Producer Prices for ".$title." ".$header_metric;
}

function buildPPITextString($title, $current_value, $current_month, $prev_value){
	$percent_change = number_format(((($current_value / $prev_value) - 1) * 100),2);

	return "The Producer Price Index for ".$title." changed ".$percent_change."% in ".$current_month." compared to a year ago, according to the latest data from the Bureau of Labor Statistics.";
}

//----------------------------------------------------------------------------------------------
/* CPI */
function getBLSCPIData($industry_id, $source_id){
	global $conn;
	global $ba_bls_api_key;
	global $bagmail_bls_api_key_alt;
  global $agilefy_bls_api_key2;
  global $ccrisp_bls_api_key;

	echo $industry_id." -- ".$source_id."\n";

	$url = 'https://api.bls.gov/publicAPI/v2/timeseries/data/';
	$method = 'POST';
	$query = array(
		'seriesid'  => array('CUUR'.$source_id.'SA0L1E'),
		'startyear' => '2007',
		'endyear'   => '2020',
		'annualaverage' => 'true',
		'registrationkey' => $ccrisp_bls_api_key,
		'calculations' => 'true'
		);

	//, 'CEU'.$full_ind.'08'
	$pd = json_encode($query);
	$contentType = 'Content-Type: application/json';
	$contentLength = 'Content-Length: ' . strlen($pd);

	$bls_result = file_get_contents(
		$url, null, stream_context_create(
			array(
				'http' => array(
					'method' => $method,
					'header' => $contentType . "\r\n" . $contentLength . "\r\n",
					'content' => $pd
				),
			)
		)
	);
	//var_dump($http_response_header);
	//print_r($result);

	//die();
	$array = json_decode( $bls_result, true );
	$i =0;
	if($array){
		//print_r($array);

		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];

			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];

			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];

			insertBLSCPIRow($rA);

		}
		$i++;
	}
}

function curlBLSCPIData($industry_id, $source_id){
	global $conn;
	global $ba_bls_api_key;
	global $bagmail_bls_api_key_alt;
  global $agilefy_bls_api_key2;
  global $ccrisp_bls_api_key;

  echo $industry_id." -- ".$source_id."\n";

	$query = array(
		'seriesid'  => array('CUUR'.$source_id.'SA0L1E'),
		'startyear' => '2007',
		'endyear'   => '2020',
		'annualaverage' => 'true',
		'registrationkey' => $ccrisp_bls_api_key,
		'calculations' => 'true'
		);

	$data_string = json_encode($query);

  $ch = curl_init('https://api.bls.gov/publicAPI/v2/timeseries/data/');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
  );


  $result = curl_exec($ch);
	//echo $result;
	//die();

	$array = json_decode( $result, true );
	$i =0;
	if($array){
		//print_r($array);

		foreach($array['Results']['series'][0]['data'] as $d){
			//print_r($d);
			//echo $d['year']."\n";
			$rA['industry_id'] = $industry_id;
			$rA['source_id'] = $source_id;
			$rA['year'] = $d['year'];
			$rA['period'] = $d['period'];
			$rA['periodName'] = $d['periodName'];
			$rA['latest']  = $d['latest'];
			$rA['value'] = $d['value'];
			$rA['footnotes_code'] = $d['footnotes'][0]['code'];
			$rA['footnotes_text'] = $d['footnotes'][0]['text'];

			$rA['cal_nc_1'] = $d['calculations']['net_changes'][1];
			$rA['cal_nc_3'] = $d['calculations']['net_changes'][3];
			$rA['cal_nc_6'] = $d['calculations']['net_changes'][6];
			$rA['cal_nc_12'] = $d['calculations']['net_changes'][12];

			$rA['cal_pct_1'] = $d['calculations']['pct_changes'][1];
			$rA['cal_pct_3'] = $d['calculations']['pct_changes'][3];
			$rA['cal_pct_6'] = $d['calculations']['pct_changes'][6];
			$rA['cal_pct_12'] = $d['calculations']['pct_changes'][12];

			insertBLSCPIRow($rA);
		}
    $i++;
  }

}

function deleteOldBLSCPIRow($industry_id) {
	global $conn;

	$del_bls_api_data_cpi = "delete from bls_api_data_cpi where industry_id = '$industry_id'";
	$industries = $conn->query($del_bls_api_data_cpi);
	//echo "deleted bls_api_data_cpi industry_id = ".$industry_id."\n\n";

}

function insertBLSCPIRow($rA) {
	global $conn;

	$sql = "insert into bls_api_data_cpi set";
	$sql .= " `industry_id` = '".$rA['industry_id']."',";
	$sql .= " `source_id` = '".$rA['source_id']."',";
 	$sql .= " `year` = '".$rA['year']."',";
 	$sql .= " `period` = '".$rA['period']."',";
 	$sql .= " `periodName` = '".$rA['periodName']."',";
	$sql .= " `latest` = '".$rA['latest']."',";
 	$sql .= " `value` = '".$rA['value']."',";

	$sql .= " `footnotes_code` = '".$rA['footnotes_code']."',";
	$sql .= " `footnotes_text` = '".$rA['footnotes_text']."',";

	$sql .= " `cal_nc_1` = '".$rA['cal_nc_1']."',";
	$sql .= " `cal_nc_3` = '".$rA['cal_nc_3']."',";
	$sql .= " `cal_nc_6` = '".$rA['cal_nc_6']."',";
	$sql .= " `cal_nc_12` = '".$rA['cal_nc_12']."',";

	$sql .= " `cal_pct_1` = '".$rA['cal_pct_1']."',";
	$sql .= " `cal_pct_3` = '".$rA['cal_pct_3']."',";
	$sql .= " `cal_pct_6` = '".$rA['cal_pct_6']."',";
	$sql .= " `cal_pct_12` = '".$rA['cal_pct_12']."'";
	//echo $sql."\n\n";
  $result = $conn->query($sql);
	//print_r($conn);
	//die();
	//echo $result;
	//die();
}

function insertBLSCPIChartRow($industry_id){
	global $conn;

	$bls_source_data = getBLSSourceData($industry_id);
	$bls_mostrecent = getMostRecentBLSCPIRow($industry_id);
	$bls_mostrecent_yearback = getMostRecentLastYearBLSCPIRow($industry_id, $bls_mostrecent['year'], $bls_mostrecent['periodName']);
	$bls_lasttenannual = get10AnnualBLSCPIRow($industry_id);

	$cpi_title = buildCPITitleString($bls_source_data['bls_cpi_name'], $bls_mostrecent['value'], $bls_mostrecent_yearback['value']);
	$cpi_text = buildCPITextString($bls_source_data['bls_cpi_name'], $bls_mostrecent['value'], $bls_mostrecent['periodName'], $bls_mostrecent_yearback['value']);

	$del_sql = "delete from bls_chart_cpi_data where industry_id =  '$industry_id'";
	$delete = $conn->query($del_sql);

	//print_r($bls_lasttenannual);
	//echo "matt";
	//die();

	$sql = "insert into bls_chart_cpi_data set";
	$sql .= " `industry_id` 	= '".$industry_id."',";
	$sql .= " `current_label` = '".formatBLSMonth($bls_mostrecent['periodName'])."-".trimBLSYear($bls_mostrecent['year'])."',";
	$sql .= " `current_value` = '".number_format($bls_mostrecent['value'], 2)."',";
	$sql .= " `prev_label` 		= '".formatBLSMonth($bls_mostrecent_yearback['periodName'])."-".trimBLSYear($bls_mostrecent_yearback['year'])."',";
	$sql .= " `prev_value` 		= '".number_format($bls_mostrecent_yearback['value'], 2)."',";
	$sql .= " `val_2008` 			= '".number_format($bls_lasttenannual['2008'], 2)."',";
	$sql .= " `val_2009` 			= '".number_format($bls_lasttenannual['2009'], 2)."',";
	$sql .= " `val_2010` 			= '".number_format($bls_lasttenannual['2010'], 2)."',";
	$sql .= " `val_2011` 			= '".number_format($bls_lasttenannual['2011'], 2)."',";
	$sql .= " `val_2012` 			= '".number_format($bls_lasttenannual['2012'], 2)."',";
	$sql .= " `val_2013` 			= '".number_format($bls_lasttenannual['2013'], 2)."',";
	$sql .= " `val_2014` 			= '".number_format($bls_lasttenannual['2014'], 2)."',";
	$sql .= " `val_2015` 			= '".number_format($bls_lasttenannual['2015'], 2)."',";
	$sql .= " `val_2016` 			= '".number_format($bls_lasttenannual['2016'], 2)."',";
	$sql .= " `val_2017` 			= '".number_format($bls_lasttenannual['2017'], 2)."',";
  $sql .= " `val_2018` 			= '".number_format($bls_lasttenannual['2018'], 2)."',";
  $sql .= " `val_2019` 			= '".number_format($bls_lasttenannual['2019'], 2)."',";
  $sql .= " `val_2020` 			= '".number_format($bls_lasttenannual['2020'], 2)."',";
	$sql .= " `cpi_title` 			= '".$cpi_title."',";
	$sql .= " `cpi_text` 			= '".$cpi_text."'";
	//echo $sql;
	$result = $conn->query($sql);
	//die();
}

function getMostRecentBLSCPIRow($industry_id){
	global $conn;
	/*
  //original
	$mr_bls_cpi_sql = "select SUM(value) as value, year, periodName from bls_api_data_cpi where industry_id = '$industry_id' and periodName != 'Annual' and latest = 'true'";
	//Switching from latest = true to footnotes_text = preliminary
  //$mr_bls_cpi_sql = "select SUM(value) as value, year, periodName from bls_api_data_cpi where industry_id = '$industry_id' and periodName != 'Annual' and footnotes_text = 'preliminary'";
	//most recent row, no prelim or latest flag
  //$mr_bls_cpi_sql = "select ROUND(value,2) as value, year, periodName from bls_api_data_cpi where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 1";

  $mr_bls_cpi = $conn->query($mr_bls_cpi_sql);
	$mr_bls_cpi_row = $mr_bls_cpi->fetch_assoc();

	return $mr_bls_cpi_row;
  */
  //2020 work 2
  $mr_bls_cpi_sql = "select * from bls_api_data_cpi where industry_id = '$industry_id' and periodName != 'Annual' order by year DESC, period DESC LIMIT 4";
  $mr_bls_cpi = $conn->query($mr_bls_cpi_sql);

  $data_array = array();

	if ($mr_bls_cpi->num_rows > 0) {
    $prev_periodName = '';
		while($row = $mr_bls_cpi->fetch_assoc()) {
      if($prev_periodName == ''){
        //echo "in 1";
        //add first one to db
        //set prev = first one
        $data_array['year'] = $row["year"];
        $data_array['periodName'] = $row["periodName"];
        $data_array['value'] = $row["value"];

        $prev_periodName = $row["periodName"];
      }elseif($prev_periodName == $row["periodName"]){
        //echo "in 2";
        //add next one to previous
        $data_array['value'] = $data_array['value'] + $row["value"];
        $prev_periodName = $row["periodName"];
      }else{
        //echo "in 3";
        //do nothing;
      }
		}
	}
  //echo "\n\n";
  //print_r($data_array);
  return $data_array;

}

function getMostRecentLastYearBLSCPIRow($industry_id, $year, $periodName){
	global $conn;

	$year_back = $year -1;

	$mr_bls_ly_cpi_sql = "select SUM(value) as value, year, periodName from bls_api_data_cpi where industry_id = '$industry_id' and periodName != 'Annual' and year = '$year_back' and periodName = '$periodName'";
	$mr_bls_ly_cpi = $conn->query($mr_bls_ly_cpi_sql);
	$mr_bls_ly_cpi_row = $mr_bls_ly_cpi->fetch_assoc();

	return $mr_bls_ly_cpi_row;
}

function get10AnnualBLSCPIRow($industry_id){
		global $conn;

		$annual_bls_cpi_sql = "select SUM(value) as value, year, periodName from bls_api_data_cpi where `industry_id` = '$industry_id' and periodName = 'Annual' group by year order by year DESC LIMIT 30";
		$annual_bls_cpi = $conn->query($annual_bls_cpi_sql);
		//$annual_bls_cpi_rows = $annual_bls_cpi->fetch_assoc();
		//echo $annual_bls_cpi_sql;
		//die();
		$data_array = array();
		$i = 0;
		if ($annual_bls_cpi->num_rows > 0) {
			while($row = $annual_bls_cpi->fetch_assoc()) {
				$data_array[$row["year"]] = $row["value"];
				//$i++;
			}
		}
	//	die();
	//	print_r($data_array);
	//	die();
		return $data_array;

}

function getBLSCPIForChart($industry_id){
	global $conn;

	$bls_cpi_sql = "select * from bls_chart_cpi_data where industry_id = '$industry_id'";
	//echo $bls_cpi_sql;
	//die();
	$bls_cpi_data = $conn->query($bls_cpi_sql);
	$bls_cpi_row = $bls_cpi_data->fetch_assoc();

	return $bls_cpi_row;

}

function buildCPITitleString($title, $current_value, $prev_value){
	$percent_change = number_format(((($current_value / $prev_value) - 1) * 100),2);
	if($percent_change >= 0.50){
		$header_metric = 'rise';
	}else if($percent_change <= -0.50){
		$header_metric = 'fall';
	}else{
		$header_metric = 'stay flat';
	}

	return "CPI by ".$title." ".$header_metric;
}

function buildCPITextString($title, $current_value, $current_month, $prev_value){
	$percent_change = ((($current_value / $prev_value) - 1) * 100);

	return "Average CPI for nonsupervisory employees at ".$title." were $".number_format($current_value, 2)." per hour in ".$current_month.", a ".number_format($percent_change, 1)."% change compared to a year ago.";
}

//----------------------------------------------------------------------------------------------


/*various */
function formatBLSMonth($month){
	switch ($month) {
		case 'Annual':
	  	return "Annual";
	    break;
	  case 'January':
	    return "Jan";
	    break;
	  case 'February':
	    return "Feb";
	    break;	
	  case 'March':
	    return "Mar";
	    break;
	  case 'April':
	    return "Apr";
	    break;
	  case 'May':
	    return "May";
	    break;	        
	  case 'June':
	    return "Jun";
	    break;	        
	  case 'July':
	    return "Jul";
	    break;	        
	  case 'August':
	    return "Aug";
	    break;	        
	  case 'September':
	    return "Sep";
	   	break;	        
	  case 'October':
	    return "Oct";
	    break;
	  case 'November':
	    return "Nov";
	    break;	        
	  case 'December':
	    return "Dec";
	    break;	        
	}
	
}

function trimBLSYear($year){
	
	$trimmed_year = substr($year, -2);
	
	return $trimmed_year;
}

/* End BLS Functions */

/* Functions to check if file exists */
function checkFile($file){
	/*if (file_exists($filename)) {
	    echo "The file $filename exists";
	} else {
	    echo "The file $filename does not exist";
	}*/
	$ch = curl_init($file);

	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_exec($ch);
	$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// $retcode >= 400 -> not found, $retcode = 200, found.
	if($retcode == 200){
		return "1";
	}else{
		return "0";
	}
	curl_close($ch);
}

function showFileCheckResult($id, $name, $fullfileloc, $filename, $file_status){
	if($file_status == 0){
		echo "<div class=\"col-md-3\">".$id.". ".$name." -- ".$filename."</div>";	
	}else{
		echo "<div class=\"col-md-3\">".$id.". <a href=\"".$fullfileloc."\">".$name."</a> OK</div>";	
	}
}

function showImageCheckResult($industry_id, $industry_name, $image_title, $fullfileloc, $filename, $file_status){
	if($file_status == 0){
		echo "<div class=\"col-md-12\">ind: ".$industry_id." -- ".$industry_name."--image: ".$image_title." -- ".$fullfileloc."</div>";	
	}else{
		//echo "<div class=\"col-md-12\">".$industry_name.". <a href=\"".$fullfileloc."\" target=\"_blank\">".$image_title."</a> OK</div>";	
	}
}

function checkLoopCount($i, $loop_count, $sleeptime){
	if($i == $loop_count){
		sleep($sleeptime);
		$i=0;
	}
}


/* how firms operate charts */
function getHFORevPieChart($indid){
	global $conn;

	$hforevsql = "select * from industry_revs where industry_id = $indid LIMIT 1";

	$hforev = $conn->query($hforevsql);
	$hforevrow = $hforev->fetch_assoc();

	return $hforevrow;

}

function getHFORevPerEmpChart($indid){
	global $conn;

	$hforevperempsql = "select * from industry_revperemps where industry_id = $indid LIMIT 1";

	$hforevperemp = $conn->query($hforevperempsql);
	$hforevperemprow = $hforevperemp->fetch_assoc();

	return $hforevperemprow;

}

function getWCCashFlowGraph($indid){
	global $conn;

	$wccashflowgraphsql = "select * from industry_cash_flow_graphs where industry_id = $indid order by value DESC";
  $wccashflowgraphdata = $conn->query($wccashflowgraphsql);
	//$wccashflowgraphdata = $wccashflowgraph->fetch_assoc();

  $dataString = '';
  $i = 0;
  
  if ($wccashflowgraphdata->num_rows > 0) {
		while($row = $wccashflowgraphdata->fetch_assoc()) {
      $dataString .= "['".$row["label"]."',".$row["value"].", chartColors[0]]";
			$i++;
			if($i != $wccashflowgraphdata->num_rows){
				$dataString .= ",";
			}
		}
	}

	return $dataString;

}

function getPorterChartData($indid){
	
	$ind_data = getIndustryTableData($indid);

	$pc_title = porter_chart_titles($ind_data['name']);
	
	$pc_datarow = porter_chart_data_row($indid);
	
	$pc_ie_row = getIndustryElementData($indid);
	
	$chart_data = '<svg class="quarter-chart" width="579px" height="320px" viewBox="0 0 579 320" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="background: #FFFFFF; max-width: 100%;">
		  <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		    <path d="M514.666667,54.872549 L514.666667,70.0098039 L289.973039,100.284314 L65.2794118,70.0098039 L65.2794118,54.872549 L0,54.872549 L0,0 L579,0 L579,54.872549 L514.666667,54.872549 L514.666667,54.872549 Z M65.2794118,265.848039 L0,265.848039 L0,320.720588 L579,320.720588 L579,265.848039 L514.666667,265.848039 L514.666667,250.710784 L289.973039,220.436275 L65.2794118,250.710784 L65.2794118,265.848039 Z" id="Combined-Shape" fill="#F3F2F2"></path>
		    <path d="M0,53.9264706 L170.294118,53.9264706 L200.568627,159.887255 L170.294118,265.848039 L0,265.848039 L0,53.9264706 Z M579,53.9264706 L579,265.848039 L408.705882,265.848039 L378.431373,159.887255 L408.705882,53.9264706 L579,53.9264706 Z" id="Combined-Shape" fill="#0A77B2"></path>
  
		    <text class="svg-text-bold svg-text-title" font-size="17" line-spacing="24" fill="#415565" style="text-anchor: middle">
		      <tspan x="289.5" y="142">'.$pc_title['0'].'</tspan>
		      <tspan x="289.5" dy="18">'.$pc_title['1'].'</tspan>
		      <tspan x="289.5" dy="18">'.$pc_title['2'].'</tspan>
		      <tspan x="289.5" dy="24" class="svg-text">'.$pc_ie_row['size'].' '.$pc_ie_row['entity_type'].'</tspan>
		    </text>

		    <text class="svg-text-bold svg-text-title" fill="#FFFFFF" x="22" y="85">
		      SUPPLIERS
		    </text>
		    <text class="svg-text" font-size="12" line-spacing="24" fill="#c7f9ff">
		      <tspan x="22" y="113">'.$pc_datarow['suppliers_1'].'</tspan>
		      <tspan x="22" dy="24">'.$pc_datarow['suppliers_2'].'</tspan>
		      <tspan x="22" dy="24">'.$pc_datarow['suppliers_3'].'</tspan>
		      <tspan x="22" dy="24">'.$pc_datarow['suppliers_4'].'</tspan>
		      <tspan x="22" dy="24">'.$pc_datarow['suppliers_5'].'</tspan>
					<tspan x="22" dy="24">'.$pc_datarow['suppliers_6'].'</tspan>
		    </text>

		    <text class="svg-text-bold svg-text-title" fill="#0A77B2" x="289.5" y="24" style="text-anchor: middle">
		      NEW ENTRANTS
		    </text>
		    <text class="svg-text" font-size="12" line-spacing="24" fill="#696969" style="text-anchor: middle">
		      <tspan x="289.5" y="47">'.$pc_datarow['new_entrants_1'].'</tspan>
		      <tspan x="289.5" dy="24">'.$pc_datarow['new_entrants_2'].'</tspan>
		    </text>

		    <text class="svg-text-bold svg-text-title" fill="#0A77B2" x="289.5" y="252" style="text-anchor: middle">
		      SUBSTITUTES
		    </text>
		    <text class="svg-text" font-size="12" line-spacing="24" fill="#696969" style="text-anchor: middle">
		      <tspan x="289.5" y="275">'.$pc_datarow['substitutes_1'].'</tspan>
		      <tspan x="289.5" dy="24">'.$pc_datarow['substitutes_2'].'</tspan>
		    </text>

		    <text class="svg-text-bold svg-text-title" fill="#FFFFFF" x="425" y="85">
		      BUYERS
		    </text>
		    <text class="svg-text" font-size="12" line-spacing="24" fill="#c7f9ff">
		      <tspan x="425" y="113">'.$pc_datarow['buyers_1'].'</tspan>
		      <tspan x="425" dy="24">'.$pc_datarow['buyers_2'].'</tspan>
		      <tspan x="425" dy="24">'.$pc_datarow['buyers_3'].'</tspan>
		      <tspan x="425" dy="24">'.$pc_datarow['buyers_4'].'</tspan>
					<tspan x="425" dy="24">'.$pc_datarow['buyers_5'].'</tspan>
					<tspan x="425" dy="24">'.$pc_datarow['buyers_6'].'</tspan>
		    </text>
		  </g>
		</svg>
		';
    
    if($pc_datarow['suppliers_1'] != ''){
		  return $chart_data;
    }
}

function save_porter_chart($indid){
	
	global $conn;
	
	$pcdata = getPorterChartData($indid);
	
	//del old chart
	$del_old_pc = "delete from chartgen where industry_id = '$indid' and chart = 'porter_chart'";
	$deloldpc = $conn->query($del_old_pc);
	
	$insertSQL = "insert into chartgen set industry_id = $indid, chart = 'porter_chart', chartdata = '$pcdata' ";
	$insert = $conn->query($insertSQL);
}

function porter_chart_titles($industry_name){
	
	$title_lines = explode("\n", wordwrap($industry_name, 19, "\n"));
	return $title_lines;
	
}

function porter_chart_data_row($indid){
	
	global $conn;

	$porterchartsql = "select * from industry_portercharts where industry_id = $indid LIMIT 1";

	$porterchart = $conn->query($porterchartsql);
	$porterchartrow = $porterchart->fetch_assoc();

	return $porterchartrow;
	
}

function getIndustryElementData($industry_id){
	global $conn;
	
	$ie_sql = "select * from industry_elements where industry_id = '$industry_id'";
	$iedata = $conn->query($ie_sql);
	$iedata_row = $iedata->fetch_assoc();

	return $iedata_row;
}

function getImportExport($indid){
	global $conn;
	
	$iesql = "select * from industry_import_exports where industry_id = $indid LIMIT 1";

	$importexport = $conn->query($iesql);
	$importexportrow = $importexport->fetch_assoc();
	
	return $importexportrow;

}

function cleanForJS($value){
  if($value == 'N/A'){
    echo "'N/A'";
  }else{
    echo $value;
  }
}

function getCapFinExamples($limit){
  global $conn;
  
  $cfe_sql = "select i.id as industry_id, i.name as industry_name, icfe.id as image_id, icfe.title as image_title, icfe.image_name from industries i, industry_cap_fin_examples icfe
  where i.id = icfe.industry_id order by i.id LIMIT ".$limit;
  //and i.id = 143
  //echo $cfe_sql;
  //die();
  $cfes = $conn->query($cfe_sql);
  return $cfes;	
}

function getLowChartCountProfiles(){
  global $conn;
  
	$lowcountprofilesql = "select cg.industry_id as industry_id, count(cg.id) as count, i.industry_type as type, i.name 
    from chartgen cg, industries i where cg.industry_id = i.id and i.industry_type != 'niche' and i.published = 1
  group by cg.industry_id
  order by count ASC";
	$lowcountprofile = $conn->query($lowcountprofilesql);
	
	return $lowcountprofile;
  
}

function getLowChartCountNiche(){
  global $conn;
  
	$lowcountprofilesql = "select cg.industry_id as industry_id, count(cg.id) as count, i.industry_type as type, i.name 
    from chartgen cg, industries i where cg.industry_id = i.id and i.industry_type = 'niche' and i.published = 1
  group by cg.industry_id";
	$lowcountprofile = $conn->query($lowcountprofilesql);
	
	return $lowcountprofile;
  
}

function getLEs(){

	global $conn;
  $lesql = "select * from local_economies where id != 1 order by id";
  //$lesql = "select * from local_economies where state = 'NC' order by id";
  //$lesql = "select * from local_economies where id = 20500 or id = 37063 order by id";
	//$indsql = "select * from industries where published = 1 order by id ";
	$local_economies = $conn->query($lesql);
	
	return $local_economies;	

}

function getLEeData($leid){
	global $conn;
	
	$lesql = "select * from local_economies where id = '$leid' LIMIT 1";
	$ledata = $conn->query($lesql);
	$ledatarow = $ledata->fetch_assoc();
	
	return $ledatarow;
}

function insertLEChartData($leid, $chart, $imgdata){
	global $conn;
	
	//del old chart
	$del_old_chart = "delete from le_chartgen where le_id = '$leid' and chart = '$chart'";
	$deloldchart = $conn->query($del_old_chart);

	$insertSQL = "insert into le_chartgen set le_id = $leid, chart = '$chart', chartdata = '$imgdata' ";
	$insert = $conn->query($insertSQL);

}

function getLE_population($leid){
  global $conn;
  
  $lep_sql = "select * from local_economy_populations where local_economy_id = '$leid' order by year ASC";
  $le_population = $conn->query($lep_sql);
  
  return $le_population;	

}

function getLE_population_growth($leid){
  global $conn;
  
  $lepg_sql = "select * from local_economy_population_growths where local_economy_id = '$leid' order by year ASC";
  $le_population_growth = $conn->query($lepg_sql);
  
  $lepgus_sql = "select * from local_economy_population_growths where local_economy_id = '1000' order by year ASC";
  $le_population_growth_us = $conn->query($lepgus_sql);
  
  $pg_us_array = array();
  while($rowus = $le_population_growth_us->fetch_assoc()) {
    $pg_us_array[$rowus["year"]] = $rowus["population_growth_value"];
  }
  //print_r($pg_us_array);
  //echo "<br />";
  //echo $pg_us_array['2014'];
  //die();
  
  $le_pg_string = '';

  if ($le_population_growth->num_rows > 0) {
    while($row = $le_population_growth->fetch_assoc()) {
      $le_pg_string .= "['".$row["year"]."', ".$row["population_growth_value"].", ".$pg_us_array[$row["year"]]."],";
    }
  }
  return $le_pg_string;	

}

function getLE_per_capita_income($leid){
  global $conn;
  
  $lepci_sql = "select * from local_economy_per_capita_incomes where local_economy_id = '$leid' order by year ASC";
  $le_per_capita_income = $conn->query($lepci_sql);
  
  $lepcius_sql = "select * from local_economy_per_capita_incomes where local_economy_id = '1000' order by year ASC";
  $le_per_capita_income_us = $conn->query($lepcius_sql);
  
  $pci_us_array = array();
  while($rowus = $le_per_capita_income_us->fetch_assoc()) {
    $pci_us_array[$rowus["year"]] = $rowus["per_capita_income_value"];
  }
  
  $le_pci_string = '';

  if ($le_per_capita_income->num_rows > 0) {
    while($row = $le_per_capita_income->fetch_assoc()) {
      $le_pci_string .= "['".$row["year"]."', ".$row["per_capita_income_value"].", ".$pci_us_array[$row["year"]]."],";
    }
  }
  return $le_pci_string;	

}

function getLE_job_growth($leid){
  global $conn;
  
  $lejg_sql = "select * from local_economy_job_growths where local_economy_id = '$leid' order by year ASC, month ASC";
  $le_job_growth = $conn->query($lejg_sql);
  
  $lejgus_sql = "select * from local_economy_job_growths where local_economy_id = '1000' order by year ASC, month ASC";
  $le_job_growth_us = $conn->query($lejgus_sql);
  
  $jg_us_array = array();
  while($rowus = $le_job_growth_us->fetch_assoc()) {
    $jg_us_array[$rowus["year"]."-".$rowus["month"]] = $rowus["job_growth_value"];
  }

  $le_jg_string = '';

  if ($le_job_growth->num_rows > 0) {
    while($row = $le_job_growth->fetch_assoc()) {
      $le_jg_string .= "['".$row["year"]."-".$row["month"]."', ".$row["job_growth_value"].", ".$jg_us_array[$row["year"]."-".$row["month"]]."],";
    }
  }
  return $le_jg_string;	

}

function getLE_unemployment($leid){
  global $conn;
  
  $leue_sql = "select * from local_economy_unemployments where local_economy_id = '$leid' order by year ASC, month ASC";
  $le_unemployment = $conn->query($leue_sql);
  
  $leueus_sql = "select * from local_economy_unemployments where local_economy_id = '1000' order by year ASC, month ASC";
  $le_unemployment_us = $conn->query($leueus_sql);
  
  $ue_us_array = array();
  while($rowus = $le_unemployment_us->fetch_assoc()) {
    $ue_us_array[$rowus["year"]."-".$rowus["month"]] = $rowus["unemployment_value"];
  }

  $le_ue_string = '';

  if ($le_unemployment->num_rows > 0) {
    while($row = $le_unemployment->fetch_assoc()) {
      $le_ue_string .= "['".$row["year"]."-".$row["month"]."', ".$row["unemployment_value"].", ".$ue_us_array[$row["year"]."-".$row["month"]]."],";
    }
  }
  return $le_ue_string;	

}

function getLE_home_price($leid){
  global $conn;
  
  $lehp_sql = "select * from local_economy_home_prices where local_economy_id = '$leid' order by year ASC";
  $le_home_price = $conn->query($lehp_sql);
  
  $lehpus_sql = "select * from local_economy_home_prices where local_economy_id = '1000' order by year ASC";
  $le_home_price_us = $conn->query($lehpus_sql);
  
  $hp_us_array = array();
  while($rowus = $le_home_price_us->fetch_assoc()) {
    $hp_us_array[$rowus["year"]] = $rowus["home_price_value"];
  }
  
  $le_hp_string = '';

  if ($le_home_price->num_rows > 0) {
    while($row = $le_home_price->fetch_assoc()) {
      $le_hp_string .= "['".$row["year"]."', ".$row["home_price_value"].", ".$hp_us_array[$row["year"]]."],";
    }
  }
  return $le_hp_string;	

}

function getLE_permits_single($leid){
  global $conn;
  
  $leps_sql = "select * from local_economy_single_permits where local_economy_id = '$leid' order by year ASC, month ASC";
  $le_permits_single = $conn->query($leps_sql);
  
  $le_ps_string = '';
  
  if ($le_permits_single->num_rows > 0) {
    while($row = $le_permits_single->fetch_assoc()) {
      $le_ps_string .= "['".$row["year"]."-".$row["month"]."', ".$row["single_family_permit_value"]."],";
    }
  }
  
  return $le_ps_string;	

}

function getLE_permits_multi($leid){
  global $conn;
  
  $lepm_sql = "select * from local_economy_multi_permits where local_economy_id = '$leid' order by year ASC, month ASC";
  $le_permits_multi = $conn->query($lepm_sql);
  
  $le_pm_string = '';
  
  if ($le_permits_multi->num_rows > 0) {
    while($row = $le_permits_multi->fetch_assoc()) {
      $le_pm_string .= "['".$row["year"]."-".$row["month"]."', ".$row["multi_family_permit_value"]."],";
    }
  }
  
  return $le_pm_string;	


}

function getLE_owneroccupied($leid){
  global $conn;
  
  $leoo_sql = "select * from local_economy_occupied_housings where local_economy_id = '$leid' LIMIT 1";
  $le_owner_occupied = $conn->query($leoo_sql);
  $leoorow = $le_owner_occupied->fetch_assoc();
  
  $le_oo_string = '';
  
  $le_oo_string .= "['Renters', ".$leoorow["renter_percent"]."],";
  $le_oo_string .= "['Owners', ".$leoorow["owner_percent"]."],";
  
  return $le_oo_string;	

}

function getLE_home_price_change($leid,$first_forecast){
  global $conn;
  
  $lehpc_sql = "select * from local_economy_home_price_changes where local_economy_id = '$leid' order by year ASC";
  $le_home_price_change = $conn->query($lehpc_sql);
  
  $lehpcus_sql = "select * from local_economy_home_price_changes where local_economy_id = '1000' order by year ASC";
  $le_home_price_change_us = $conn->query($lehpcus_sql);
  
  $hpc_us_array = array();
  while($rowus = $le_home_price_change_us->fetch_assoc()) {
    $hpc_us_array[$rowus["year"]] = $rowus["home_price_change_value"];
  }
  
  $le_hpc_string = '';

  if ($le_home_price_change->num_rows > 0) {
    while($row = $le_home_price_change->fetch_assoc()) {
      
      if($row["year"] < $first_forecast){
        $le_hpc_string .= "['".$row["year"]."', ".$row["home_price_change_value"].", '', ".$hpc_us_array[$row["year"]].", ''],";
      }elseif($row["year"] >= $first_forecast){
        $le_hpc_string .= "['".$row["year"]."', ".$row["home_price_change_value"].", chartProjectionStyle1, ".$hpc_us_array[$row["year"]].", chartProjectionStyle2],";
      }
    }
  }
  
  return $le_hpc_string;	


}

function getLE_gross_monthly_rent($leid,$first_forecast){
  global $conn;
  
  $legmr_sql = "select * from local_economy_monthly_rents where local_economy_id = '$leid' order by year ASC";
  $le_gross_monthly_rent = $conn->query($legmr_sql);
  
  $legmrus_sql = "select * from local_economy_monthly_rents where local_economy_id = '1000' order by year ASC";
  $le_gross_monthly_rent_us = $conn->query($legmrus_sql);
  
  $gmr_us_array = array();
  while($rowus = $le_gross_monthly_rent_us->fetch_assoc()) {
    $gmr_us_array[$rowus["year"]] = $rowus["monthly_rent_value"];
  }
  
  $le_gmr_string = '';

  if ($le_gross_monthly_rent->num_rows > 0) {
    while($row = $le_gross_monthly_rent->fetch_assoc()) {
      
      if($row["year"] < $first_forecast){
        $le_gmr_string .= "['".$row["year"]."', ".$row["monthly_rent_value"].", '', ".$gmr_us_array[$row["year"]].", ''],";
      
      }elseif($row["year"] >= $first_forecast){
        $le_gmr_string .= "['".$row["year"]."', ".$row["monthly_rent_value"].", chartProjectionStyle1, ".$gmr_us_array[$row["year"]].", chartProjectionStyle2],";
      
      }
    }
  }
  
  return $le_gmr_string;	

}


function determineOutputFileNameLE($shortname, $charttype){
	// $root_dir = "/Users/reddingm/Code/VerticalIQ_generators/charts/output";
	$root_dir = getcwd() . '/output';
  return $root_dir."/".$shortname."_".$charttype.".png";
}


/*
function getImportsExports($industry_id){
		global $conn;

		$impexpdata_sql = "select * from industry_trade_import_exports where `industry_id` = '$industry_id' order by year ASC";
		$impexpdata = $conn->query($impexpdata_sql);

  	$dataString = '';
  	$i=0;
  	if ($impexpdata->num_rows > 0) {
  		while($row = $impexpdata->fetch_assoc()) {
  			//$dataString .= "['".$row["sale_date"]."',".$row[$col]."]";
  			$dataString .= "['2010', 187576, 179502]";
        $i++;
  			if($i != $impexpdata->num_rows){
  				$dataString .= ",";
  			}
  		}
  	}

  	return $dataString;  

}
*/






















?>