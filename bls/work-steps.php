##truncate 3 bls tables
truncate industry_bls_emps;
truncate industry_bls_ppis;
truncate industry_bls_wages;


truncate bls_api_data_cpi;
truncate bls_api_data_emp;
truncate bls_api_data_ppi;
truncate bls_api_data_wages;
truncate bls_chart_cpi_data;
truncate bls_chart_emp_data;
truncate bls_chart_ppi_data;
truncate bls_chart_wage_data;


php step1_get_bls_emp_data.php

php step2_get_bls_earning_data.php

php step3_get_bls_ppi_data.php

//php step4_get_bls_cpi_data.php

export queries:

select * from bls_chart_emp_data where current_label != '-'
	save csv as industry_bls_emps

select * from bls_chart_wage_data where current_label != '-'
	save csv as industry_bls_wages
  
select * from bls_chart_ppi_data where current_label != '-'
	save csv as industry_ppi_prices