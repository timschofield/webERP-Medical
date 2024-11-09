INSERT INTO `accountsection` VALUES (1,'EQUITY, PROVISIONS AND FINANCIAL LIABILITIES ACCOUNTS ');
INSERT INTO `accountsection` VALUES (2,'FORMATION EXPENSES AND FIXED ASSETS ACCOUNTS ');
INSERT INTO `accountsection` VALUES (5,'FINANCIAL ACCOUNTS ');
INSERT INTO `accountsection` VALUES (10,'Capital or branches’ assigned capital and owner’s accounts');
INSERT INTO `accountsection` VALUES (20,'Formation expenses and similar expenses ');
INSERT INTO `accountsection` VALUES (30,'Raw materials and consumables ');
INSERT INTO `accountsection` VALUES (50,'Transferable securities');

INSERT INTO `accountgroups` VALUES ('Cost of Goods Sold',2,1,5000,'');
INSERT INTO `accountgroups` VALUES ('Promotions',5,1,6000,'');
INSERT INTO `accountgroups` VALUES ('Revenue',1,1,4000,'');
INSERT INTO `accountgroups` VALUES ('Sales',1,1,10,'');
INSERT INTO `accountgroups` VALUES ('Outward Freight',2,1,5000,'Cost of Goods Sold');
INSERT INTO `accountgroups` VALUES ('BBQs',5,1,6000,'Promotions');
INSERT INTO `accountgroups` VALUES ('Giveaways',5,1,6000,'Promotions');
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,1000,'');
INSERT INTO `accountgroups` VALUES ('Equity',50,0,3000,'');
INSERT INTO `accountgroups` VALUES ('Fixed Assets',10,0,500,'');
INSERT INTO `accountgroups` VALUES ('Income Tax',5,1,9000,'');
INSERT INTO `accountgroups` VALUES ('Liabilities',30,0,2000,'');
INSERT INTO `accountgroups` VALUES ('Marketing Expenses',5,1,6000,'');
INSERT INTO `accountgroups` VALUES ('Operating Expenses',5,1,7000,'');
INSERT INTO `accountgroups` VALUES ('Other Revenue and Expenses',5,1,8000,'');

INSERT INTO `chartmaster` VALUES ('1','Default Sales/Discounts','Sales',-1);
INSERT INTO `chartmaster` VALUES ('1010','Petty Cash','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1020','Cash on Hand','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1030','Cheque Accounts','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1040','Savings Accounts','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1050','Payroll Accounts','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1060','Special Accounts','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1070','Money Market Investments','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1080','Short-Term Investments (< 90 days)','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1090','Interest Receivable','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1100','Accounts Receivable','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1150','Allowance for Doubtful Accounts','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1200','Notes Receivable','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1250','Income Tax Receivable','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1300','Prepaid Expenses','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1350','Advances','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1400','Supplies Inventory','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1420','Raw Material Inventory','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1440','Work in Progress Inventory','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1460','Finished Goods Inventory','Current Assets',-1);
INSERT INTO `chartmaster` VALUES ('1500','Land','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1550','Bonds','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1600','Buildings','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1620','Accumulated Depreciation of Buildings','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1650','Equipment','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1670','Accumulated Depreciation of Equipment','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1700','Furniture & Fixtures','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1710','Accumulated Depreciation of Furniture & Fixtures','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1720','Office Equipment','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1730','Accumulated Depreciation of Office Equipment','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1740','Software','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1750','Accumulated Depreciation of Software','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1760','Vehicles','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1770','Accumulated Depreciation Vehicles','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1780','Other Depreciable Property','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1790','Accumulated Depreciation of Other Depreciable Prop','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1800','Patents','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('1850','Goodwill','Fixed Assets',-1);
INSERT INTO `chartmaster` VALUES ('2','test','Sales',-1);
INSERT INTO `chartmaster` VALUES ('2010','Bank Indedebtedness (overdraft)','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2020','Retainers or Advances on Work','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2050','Interest Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2100','Accounts Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2150','Goods Received Suspense','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2200','Short-Term Loan Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2230','Current Portion of Long-Term Debt Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2250','Income Tax Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2300','GST Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2310','GST Recoverable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2320','PST Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2330','PST Recoverable (commission)','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2340','Payroll Tax Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2350','Withholding Income Tax Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2360','Other Taxes Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2400','Employee Salaries Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2410','Management Salaries Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2420','Director / Partner Fees Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2450','Health Benefits Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2460','Pension Benefits Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2470','Canada Pension Plan Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2480','Employment Insurance Premiums Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2500','Land Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2550','Long-Term Bank Loan','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2560','Notes Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2600','Building & Equipment Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2700','Furnishing & Fixture Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2720','Office Equipment Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2740','Vehicle Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2760','Other Property Payable','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2800','Shareholder Loans','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('2900','Suspense','Liabilities',-1);
INSERT INTO `chartmaster` VALUES ('3100','Capital Stock','Equity',-1);
INSERT INTO `chartmaster` VALUES ('3200','Capital Surplus / Dividends','Equity',-1);
INSERT INTO `chartmaster` VALUES ('3300','Dividend Taxes Payable','Equity',-1);
INSERT INTO `chartmaster` VALUES ('3400','Dividend Taxes Refundable','Equity',-1);
INSERT INTO `chartmaster` VALUES ('3500','Retained Earnings','Equity',-1);
INSERT INTO `chartmaster` VALUES ('4100','Product / Service Sales','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4200','Sales Exchange Gains/Losses','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4500','Consulting Services','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4600','Rentals','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4700','Finance Charge Income','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4800','Sales Returns & Allowances','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('4900','Sales Discounts','Revenue',-1);
INSERT INTO `chartmaster` VALUES ('5000','Cost of Sales','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5100','Production Expenses','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5200','Purchases Exchange Gains/Losses','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5500','Direct Labour Costs','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5600','Freight Charges','Outward Freight',-1);
INSERT INTO `chartmaster` VALUES ('5700','Inventory Adjustment','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5800','Purchase Returns & Allowances','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('5900','Purchase Discounts','Cost of Goods Sold',-1);
INSERT INTO `chartmaster` VALUES ('6100','Advertising','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6150','Promotion','Promotions',-1);
INSERT INTO `chartmaster` VALUES ('6200','Communications','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6250','Meeting Expenses','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6300','Travelling Expenses','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6400','Delivery Expenses','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6500','Sales Salaries & Commission','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6550','Sales Salaries & Commission Deductions','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6590','Benefits','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6600','Other Selling Expenses','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6700','Permits, Licenses & License Fees','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6800','Research & Development','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('6900','Professional Services','Marketing Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7020','Support Salaries & Wages','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7030','Support Salary & Wage Deductions','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7040','Management Salaries','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7050','Management Salary deductions','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7060','Director / Partner Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7070','Director / Partner Deductions','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7080','Payroll Tax','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7090','Benefits','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7100','Training & Education Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7150','Dues & Subscriptions','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7200','Accounting Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7210','Audit Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7220','Banking Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7230','Credit Card Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7240','Consulting Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7260','Legal Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7280','Other Professional Fees','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7300','Business Tax','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7350','Property Tax','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7390','Corporation Capital Tax','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7400','Office Rent','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7450','Equipment Rental','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7500','Office Supplies','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7550','Office Repair & Maintenance','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7600','Automotive Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7610','Communication Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7620','Insurance Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7630','Postage & Courier Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7640','Miscellaneous Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7650','Travel Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7660','Utilities','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7700','Ammortization Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7750','Depreciation Expenses','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7800','Interest Expense','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('7900','Bad Debt Expense','Operating Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8100','Gain on Sale of Assets','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8200','Interest Income','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8300','Recovery on Bad Debt','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8400','Other Revenue','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8500','Loss on Sale of Assets','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8600','Charitable Contributions','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('8900','Other Expenses','Other Revenue and Expenses',-1);
INSERT INTO `chartmaster` VALUES ('9100','Income Tax Provision','Income Tax',-1);