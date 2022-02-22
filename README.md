# verticaliq_gen_aws

bls:
1. Truncate tables in generators DB for bls data:

truncate bls_api_data_cpi;
truncate bls_api_data_emp;
truncate bls_api_data_ppi;
truncate bls_api_data_wages;
truncate bls_chart_cpi_data;
truncate bls_chart_emp_data;
truncate bls_chart_ppi_data;
truncate bls_chart_wage_data;

2. from inside bls directory run

php step1_get_bls_emp_data.php
php step2_get_bls_earning_data.php
php step3_get_bls_ppi_data.php