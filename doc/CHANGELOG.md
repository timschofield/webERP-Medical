# Changelog
All notable changes to the webERP project will be documented in this file.  
The format is based on [Keep a Changelog], and this project adheres to [Semantic Versioning].  
For changelogs earlier than v4.14.1, please refer to [CHANGELOG_ARCHIVE.md].

## Guidelines
- Keep descriptions as short and concise as possible.  
- If including html tags in your description, please surround them with back ticks.  
- The commit Type, will typically be one of the following: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`  
- Enter dates in format yyyy-mm-dd.  
- Add links to the associated GitHub commit in Details column.  
- Add links to supporting info in Ref column, such as Forum posts, Mailing List archives or GitHub issues.  

## [Unreleased]

| Description | Type | Author | Date | Details | Ref |
|:-----------:|:----:|:------:|:----:|:-------:|:---:|
| SystemParameters.php: Fix typo in a LogSeverity option, refactored these options (less code), and other element updates. | Fixed | PaulT | 2022-05-15 | [View](https://github.com/webERP-team/webERP/commit/c6b2d5ee128f8a3b2051a12be87d7346f95ff6f2) |  |
| footer.php: Restore missing LogFile handling causing error. (reported in the forums by Dale Scott) | Fixed | PaulT | 2022-05-15 | [View](https://github.com/webERP-team/webERP/commit/5fb8b2c3589f6504324a8a82ee327084ec517923) | [Forum](https://www.weberp.org/forum/showthread.php?tid=9255) |
| header.php: Fix hyperlink to the manual. (reported in the forums by Dale Scott) | Fixed | Tim Schofield | 2021-12-16 | [View](https://github.com/webERP-team/webERP/commit/c474bd10d104595d63afb340a967763b88a25b00) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8990&pid=17130) |

## [v4.15.2] - 2021-11-27

| Description | Type | Author | Date | Details | Ref |
|:-----------:|:----:|:------:|:----:|:-------:|:---:|
| MiscFunctions.js: Fixes the case where a negative payment is being made, without any allocation. Previously the payment was being blocked when the payment was less than the allocated amount, even when the payment was negative. | Fixed | Tim Schofield | 2021-10-28 | [View](https://github.com/webERP-team/webERP/commit/a7d97baf8bcdb10cb66801292af0d29bf9611c5b) | [Forum](https://www.weberp.org/forum/showthread.php?tid=8860) |
| New themes and related improvements. | Added | Juergen Mueller | 2021-05-11 | [View](https://github.com/webERP-team/webERP/commit/bd6c006bdd33932144f8e25a81583cc7703a3061) | |
| Z_importDebtors.php: Bugs in customer import script. | Fixed | Tim Schofield | 2021-05-04 | [View](https://github.com/webERP-team/webERP/commit/054f56ee642de5749bd3ff2f2b7e9d8868289366) | |
| Use https protocol with geocode urls. | Changed | JanB | 2021-03-07 | [View](https://github.com/webERP-team/webERP/commit/9ce12156ffc3b7e0b690f6aa23d8692bd1bbc578) | [Sourceforge](https://sourceforge.net/p/web-erp/discussion/general/thread/939c342135/#1c75) |
| Suppliers.php: fix typo in previous commit. | Fixed | PaulT | 2021-03-05 | [View](https://github.com/webERP-team/webERP/commit/a56b3a59a055ad111012b09f686c47d89a47c126) | |
| Add missing 'key' parameter for geocoding. | Changed | JanB | 2021-02-28 | [View](https://github.com/webERP-team/webERP/commit/5c9019d79f301015e797f14ed71eafd908044c7c) | [SourceForge](https://sourceforge.net/p/web-erp/discussion/general/thread/939c342135/#088e) |
| GLCashFlowsindirect.php: Fix syntax error with cast. | Fixed | PaulT | 2021-02-14 | [View](https://github.com/webERP-team/webERP/commit/100b450e163fea359e36a987d7f38d25aa084112) | |
| StockUsageGraph.php: Set graph back to bars. (original value, assigned incorrectly) | Changed | PaulT | 2020-11-16 | [View](https://github.com/webERP-team/webERP/commit/282cc93055c4d25e95bd256ceaf1f0cec78cad36) | |
| StockUsageGraph.php: Show zero counts within period. | Added | HDeriauFF (PaulT assist and commit) | 2020-11-15 | [View](https://github.com/webERP-team/webERP/commit/17afce45cc6b64a6327996721b9728fe39a3cafc) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8418) |
| Finnish Translation | Added | Pekka Viiliainen (Phil commit) | 2020-10-24 | [View](https://github.com/webERP-team/webERP/commit/e5816a63a9f1be2fc253558ff30f0195910b1378) | |
| GetPrice.inc: Correctly show price when one is expired and not replaced. | Fixed | Tim Schofield | 2020-10-06 | [View](https://github.com/webERP-team/webERP/commit/491f9c088e22c66208309be7beb2f6bbcfcbc4eb) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8407) |
| Update SQL script path references. (reported by Tom Glare) | Changed | PaulT | 2020-09-12 | [View](https://github.com/webERP-team/webERP/commit/aa71a7b5e4fa7a0f1a13e0116233bd599123636a) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8409&pid=16137#pid16137) |
| Form tags: Apply htmlspecialchars() or urlencode() to attribute values as needed. | Changed/Security | PaulT | 2020-08-15 | [View](https://github.com/webERP-team/webERP/commit/b28f0cf0a24e82b8db920b4b6b7d4e1c6d836d95) | |
| Fix several reported vulnerabilities. | Security | Mario Riederer (Phil commit) | 2020-08-08 | [View](https://github.com/webERP-team/webERP/commit/c1569480a830f706e4e27447cad94238b254dced) | |
| Silverwolf CSS: Remove the background image and use css to create the same affect. | Changed | Tim Schofield | 2020-06-11 | [View](https://github.com/webERP-team/webERP/commit/0f8bd3228aea7eb81aed267408e919c49739fc50) | |
| session.php: Bug in variable sanitising routine. | Fixed | Tim Schofield | 2020-06-08 | [View](https://github.com/webERP-team/webERP/commit/3f0f0b5962b04fbdfece352af259a90f89e1ace8) | |
| install/index.php: Improve the installer language handling so that the detected language is set as expected. | Changed | PaulT | 2020-06-06 |  [View](https://github.com/webERP-team/webERP/commit/7a43bc23b244e7637b0fb9b661efd32034c81739) | |
| Fix LFI issue. (Reported by: Simon@lyhinslab.org, https://lyhinslab.org) | Security | Simon / PaulT | 2020-06-06 | [View](https://github.com/webERP-team/webERP/commit/baf43971019da0dbacf094e94d16fec2720722a3) | |
|PDFPriceList.php: Apply alternating row shading to pricing rows. (forum request) | Added | PaulT | 2020-04-24 | [View](https://github.com/webERP-team/webERP/commit/a21c76905025518ee9bd3936a865177770a2dcc0) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8389&pid=16060#pid16060) |
| <ol><li>default.sql, demo.sql: Correct DB structure missing AUTO_INCREMENT fields.</li><li>Login.php, session.php: Remove references to get_magic_quotes_gpc() which is removed in PHP7.4 and above.</li></ol> | <ol><li>Fixed</li><li>Changed</li></ol> | <ol><li>Tim Schofield</li><li>Confucius / Tim Schofield</li></ol> | 2020-04-23 | [View](https://github.com/webERP-team/webERP/commit/9aaeae890716c0a70123a800b865d0e820710557) | <ol><li>[Forum](http://www.weberp.org/forum/showthread.php?tid=8386&pid=16029#pid16029)</li><li>[Forum](http://www.weberp.org/forum/showthread.php?tid=8393)</li></ol>
| Update Espanol locale | Update | Rafael Chacón | 2020-03-18 | [View](https://github.com/webERP-team/webERP/commit/86e5f1443c345925b645682b6b8f0483b9c5078d) | |
| default.css: Minor style tweak. | Changed | Rafael Chacón | 2020-03-18 | [View](https://github.com/webERP-team/webERP/commit/0d78f93b57fbca8e030a145ec43d01769cc0538f) | |
| demo.sql: Revised demo data. | Update | Tim Schofield | 2020-01-28 | [View](https://github.com/webERP-team/webERP/commit/f37c4687e38d70115ec6a5dc845bd8acb2f0e149) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8386&pid=16021#pid16021) |
| Credit_Invoice.php: Round the freight cost, and the previous allocated amount to the required decimal places. | Fixed | Tim Schofield | 2020-01-28 | [View](https://github.com/webERP-team/webERP/commit/e1082b75832cde3b65ef4327d50db1fd77047e8d) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8385&pid=16020#pid16020) |
| Z_DeleteCreditNote.php: Update feedback message in previous commit. | Fixed | PaulT | 2020-01-19 | [View](https://github.com/webERP-team/webERP/commit/e0c94a0de04d1437a729a29963ce05684df9d07f) | |
| Z_DeleteCreditNote.php: Fix SQL error, add DELETE for stockserialmoves causing FK error. | Fixed | PaulT | 2020-01-18 | [View](https://github.com/webERP-team/webERP/commit/9bdf62ecdb1fea5f95ffb6a36cae751b8ea4316f) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8370) |
| Update GetRptLinks function. | Fixed | PaulT | 2020-01-18 | [View](https://github.com/webERP-team/webERP/commit/afbbb76d1f071f228f17b792b880f72fc7c55a25) | [Forum](http://www.weberp.org/forum/showthread.php?tid=7996&pid=15926#pid15926) |
| ConfirmDispatch_Invoice.php: Fix error where tax amount wasn't getting rounded to the required decimal places leading to potential rounding errors. | Fixed | Tim Schofield | 2020-01-18 | [View](https://github.com/webERP-team/webERP/commit/e3b693808e5e9e0fb56336684b62a87ff16120c5) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8385&pid=16011#pid16011) |
| Dashboard.php: Fix the bug whereby sales person invoices were divulged to other sales people when a customer has more than one branch with different sales people attached. | Fixed | Tim Schofield | 2019-12-14 | [View](https://github.com/webERP-team/webERP/commit/f22ee88d2df54fbcb25ecdd6e9a8f41a9267b373) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8382) |
| api/api_locations.php: Return API data format to include integer code as described in the manual. | Fixed | Express Achiever | 2019-11-20 | [View](https://github.com/webERP-team/webERP/commit/5bc70eb3c95dea3639641ffee922016a1d168a2c) | |
| SupplierTenderCreate.php: Fix ambiguous SQL statement. | Fixed | Tim Schofield | 2019-11-18 | [View](https://github.com/webERP-team/webERP/commit/bc1c7968660fda46686b30a60dad3a9060d9356b) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8381&pid=15968#pid15968) |
| PDFStockCheckComparison.php: Insert missing standard cost field. | Fixed | Express Achiever | 2019-11-15 | [View](https://github.com/webERP-team/webERP/commit/7d6c175feda7c139ee6887a6ebdaca5ea66ebbe7) | |
| StockCounts.php: Add alternative method for user to enter stock counts. | Changed | Express Achiever| 2019-11-07 | [View](https://github.com/webERP-team/webERP/commit/6f29993e579810e33c7f2016fe329f88798bca46) | |
| SelectSalesOrder.php Fix SQL error. Also apply whitespace and variable name formatting. | Fixed | PaulT | 2019-11-02 | [View](https://github.com/webERP-team/webERP/commit/912a3424fa58317f5c00911730d9036350d6b258) | |
| Fix a few issues related to GL Posting for Stock Transfers. | Fixed | Express Achiever | 2019-10-12 | [View](https://github.com/webERP-team/webERP/commit/47e0a731883f1cbc1057afac9187ae262016a90e) | |
| Demo mode tidy. | Changed | Phil Daintree | 2019-10-05 | [View](https://github.com/webERP-team/webERP/commit/501e26ede1c679842efcc12f605337563845ce9d) | |
| Demo mode modifications. | Changed | Phil Daintree | 2019-10-05 | [View](https://github.com/webERP-team/webERP/commit/8a7cab0b7d104d6550424f618ee36d894d63eca8) | |
| SalesCategories.php: Rework the script to conform with the project standards, and to make the use of it easier and more intuitive. | Changed | Tim Schofield | 2019-09-26 | [View](https://github.com/webERP-team/webERP/commit/6937e7887938d825f46152d408517172f8bda7ab) | |
| InternalStockRequest.php: Fix inconsistent casing in variable names. | Fixed | Tim Schofield | 2019-09-23 | [View](https://github.com/webERP-team/webERP/commit/473203cc78f484236ab377b7407a07893700bd7d) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8366) |
| PageSecurity.php: Fix typo in variable name. | Fixed | Tim Schofield | 2019-09-16 | [View](https://github.com/webERP-team/webERP/commit/c04ffce829b32b601860a59aec90b914f2995235) | |
| Z_DeleteSalesTransActions.php: Fix utility script for new tables. | Fixed | Tim Schofield | 2019-08-16 | [View](https://github.com/webERP-team/webERP/commit/f8cfb455f696dadd29d90f53664eefaeb7ca2ce1) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8360) |
| Check that the server has the correct permissions to upload part image. | Fixed | Tim Schofield | 2019-09-08 | [View](https://github.com/webERP-team/webERP/commit/d6223d61859475fcc791226de1861dfad9b64536) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8357) |
| Fix purchase order item to flag as completed. | Fixed | Express Achiever | 2019-09-06 | [View](https://github.com/webERP-team/webERP/commit/638a7a739c867905e22d4c4e8ce1af7d2290cc60) | |
| Fix stock movement records missing in report when there is no link to table custbranch. | Fixed | Express Achiever | 2019-09-06 | [View](https://github.com/webERP-team/webERP/commit/83f863a6628680b3a2ea6c62eba7a77b1dcff2c0) | |
| Counter sales: UX improvements with to CounterSalesFunctions.js. | Changed | Express Achiever | 2019-09-06 | [View](https://github.com/webERP-team/webERP/commit/c7e033dbf90423d316b4c57f5e0dba735d8a1ae5) | |
| MRP shortages incorrectly includes "Service/Labour" items. | Fixed | Alan Miller | 2019-09-01 | [View](https://github.com/webERP-team/webERP/commit/c1bce8f8f17fb3a1234c4d11d100916bdaa69dac) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8347&highlight=MRPShortages) |
| Restrict demo mode from alterations to security model. | Fixed | Phil Daintree | 2019-08-31 | [View](https://github.com/webERP-team/webERP/commit/de6466f35c5fc899b796e605e479eef0205d8009) | |
| Counter sales: improve quick entry flow by pre-fetching stockmaster list and auto fill to the quick entry table. | Changed | Express Achiever | 2019-08-30 | [View](https://github.com/webERP-team/webERP/commit/3733fc8d73da932c9a7b063a44089d2b824b113f) | |
| Missing parameter for Add_GRN_To_Trans | Fixed | Express Achiever | 2019-08-30 | [View](https://github.com/webERP-team/webERP/commit/96c8eb9566e983f5a51ef8b7fc64fb211cc60c26) | |
| Fix language files | Fixed | Rafael Chacón | 2019-08-28 | [View](https://github.com/webERP-team/webERP/commit/2b2f591c1e06a68c30eeab8a121a0ef83b109565) | |

## [v4.15.1] - 2019-06-16

| Description | Type | Author | Date | Details | Ref |
|:-----------:|:----:|:------:|:----:|:-------:|:---:|
| index.php: Properly check return from database before showing report links | Fixed | Tim Schofield | 2019-06-09 | [View](https://github.com/webERP-team/webERP/commit/44f680c34d76e2ae794e678335e5310ebad37aad) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8345) |
| GLTransInquiry.php: Fix to previous commit - ovdiscount is not a field in supptrans table, only debtotrans | Fixed | Tim Schofield | 2019-05-25 | [View](https://github.com/webERP-team/webERP/commit/71db91d6f4fd67b65025a713c3d5c30ab99e59a3) |   |
| Credit_Invoice.php: Enable the shipping to be reduced to zero in case where the original invoice included a value for shipping costs. (reported in the forums by HDeriauFF) | Changed | Tim Schofield | 2019-05-04 | [View](https://github.com/webERP-team/webERP/commit/20f24e86d43d90bf584e6c205e548bffab287711) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8339&pid=15697#pid15697) |
| CustomerAllocations.php: Allow user to perform allocation on receipts with amount zero but discount is non zero | Changed | express achiever | 2019-04-18 | [View](https://github.com/webERP-team/webERP/commit/c3498d37f2da76238cc9113f61ff9992c6be9956) |   |
| BOMs.php: Incorporate selection box to either show all levels of a BOM or just the top level | Changed | Tim Schofield | 2019-04-14 | [View](https://github.com/webERP-team/webERP/commit/faa70d7c68bfb5b761e504e2a2c2efeae5f3014c) |   |
| Include the branch name in the inquiry (with standardized code updates) | Added/Changed | Tim Schofield/HDeriauFF | 2019-04-14 | [View](https://github.com/webERP-team/webERP/commit/14ccc08f8cdd4063f0971c891af6b877adb4101a) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8325) |
| New GL journal templating system | Added | Tim Schofield | 2019-04-05 | [View](https://github.com/webERP-team/webERP/commit/621416105ff9ffe92f7e30a33687f81c224f5b82) |   |
| Fix gettext calls and standardize code | Fixed | Paul Becker | 2019-04-05 | [View](https://github.com/webERP-team/webERP/commit/b902c3eba66cb265e81dcedc8fc9574e0915e6a2) |   |
| SupplierTransInquiry.php: Fix sql search by supplier id condition | Fixed | express achiever | 2019-03-26 | [View](https://github.com/webERP-team/webERP/commit/1074421d4aa01dfea24462b83b1ae735d3165a70) |   |
| GLTransInquiry.php: Fix where discount is entered at payment but not included in the report line item calculation | Fixed | express achiever | 2019-03-24 | [View](https://github.com/webERP-team/webERP/commit/dd1895f74dab21ac30222bc11ee6379d6316a48e) |   |
| StockStatus.php: Table formatting issue | Fixed | express achiever | 2019-03-24 | [View](https://github.com/webERP-team/webERP/commit/0e982f34da72806e8d556126c6be7eeeaee364cc) |   |
| French translation: Minor translation update | Changed | Rafael Chacón | 2019-03-21 | [View](https://github.com/webERP-team/webERP/commit/6b2fb4da40620147048502192de144e8d7e198af) |   |
| Standardize code: To various files | Changed | Rafael Chacón | 2019-03-18 | [View](https://github.com/webERP-team/webERP/commit/1119ec935d2cf0783ea52f8514f8275e5ed2b1bd) |   |
| Code updates: To various files | Changed | Rafael Chacón | 2019-03-18 | [View](https://github.com/webERP-team/webERP/commit/a632a84b6bf483f06fcb2e50f434bde1b4ab8c67) |   |
| GLStatements.php: Script updates before upload | Changed | Rafael Chacón | 2019-03-14 | [View](https://github.com/webERP-team/webERP/commit/b872bec3de867e2227315f4346a5e8c5972ad99a) |   |
| Rebuild language files and a few minor code changes | Changed | Rafael Chacón | 2019-02-24 | [View](https://github.com/webERP-team/webERP/commit/df22cd372c468f5e27646bde37d601e17e1f4240) |   |
| Replace texts with blanks with trimmed text | Changed | Rafael Chacón | 2019-02-21 | [View](https://github.com/webERP-team/webERP/commit/972c7c145649af2b141d0b17756a5fb1a0a7ed7e) |   |
| Fix country name in Currencies.php | Fixed | Rafael Chacón | 2019-02-20 | [View](https://github.com/webERP-team/webERP/commit/c6f4d97d496d3ee5289e7ef577b536e50782195a) |   |
| Fix height in #QuickMenuDiv li | Fixed | Rafael Chacón | 2019-02-20 | [View](https://github.com/webERP-team/webERP/commit/c8cb323b16105866c9d576b0d69c2969cee94962) |   |
| Use inline-block instead of inline | Changed | Rafael Chacón | 2019-02-20 | [View](https://github.com/webERP-team/webERP/commit/23a580b16f05c6015e03aa64321d57af6878853d) |   |
| Fix concat errors, add urlencode() | Fixed | PaulT | 2019-02-12 | [View](https://github.com/webERP-team/webERP/commit/d6b2ddb7353fcfdfa0b952a117feca83f330eaaa) |   |
| Deletes the translation of numbers | Changed | Rafael Chacón | 2019-01-21 | [View](https://github.com/webERP-team/webERP/commit/55bc2e3275c8d6a8284f9fa9118782aa0b8a5908) |   |
| Standardise buttons in Horizontal Analysis reports | Changed | Rafael Chacón | 2019-01-19 | [View](https://github.com/webERP-team/webERP/commit/22c62fc4129364fa7ce5efa2f1b92adeb9cc7c7f) |   |
| Clean up purchases and sales reports code | Changed | Rafael Chacón | 2019-01-09 | [View](https://github.com/webERP-team/webERP/commit/f0c064c6abfca9d39f58abb4aac18af39a51de38) |   |
| Standardise GL reports code | Changed | Rafael Chacón | 2019-01-09 | [View](https://github.com/webERP-team/webERP/commit/e7ceae5931c020f98ce8b89b12d4397d277c4f77) |   |
| Clean up and standardise sheet style view | Changed | Rafael Chacón | 2019-01-04 | [View](https://github.com/webERP-team/webERP/commit/81f710f1c85a49dc703db8d968de3c45904bba7c) |   |
| Clean up the CSS | Changed | Rafael Chacón | 2019-01-04 | [View](https://github.com/webERP-team/webERP/commit/57ce6c30a61cec38281b37f21324699ffa64e624) |   |
| Hides help texts for extra small devices | Changed | Rafael Chacón | 2019-01-04 | [View](https://github.com/webERP-team/webERP/commit/8e468644ff6982cc7682480a5710a25647b38214) |   |
| Show a set of financial statements | Added | Rafael Chacón | 2019-01-02 | [View](https://github.com/webERP-team/webERP/commit/02beefcd31ef4a848760c7ba93fe0bf36a78393e) |   |
| Fix variable used in function fShowPageHelp | Fixed | Rafael Chacón | 2018-12-30 | [View](https://github.com/webERP-team/webERP/commit/b48c70708c5ae96c46f5162076ab03e9170ca791) |   |
| Fix SQL in Timesheets per Tim | Fixed | Phil Daintree | 2018-12-29 | [View](https://github.com/webERP-team/webERP/commit/b0d816d88fa55c2e56e7d7a6f46987e44472a30b) |   |
| Improve the readability of a script | Changed | Rafael Chacón | 2018-12-27 | [View](https://github.com/webERP-team/webERP/commit/9dd0c6ca06fbbb3bd879395ec7721e79d7b5af54) |   |
| Improve the readability of a script | Changed | Rafael Chacón | 2018-12-26 | [View](https://github.com/webERP-team/webERP/commit/9afe079de81088e1aa9b30f2bd6eed2886068a34) |   |
| Sort the bank accounts | Changed | Rafael Chacón | 2018-12-18 | [View](https://github.com/webERP-team/webERP/commit/79d88db4ea04099473edfc7f1ddd84deb4ad500f) |   |
| Order by bankaccountname | Changed | Rafael Chacón | 2018-12-14 | [View](https://github.com/webERP-team/webERP/commit/eb5bd54f55c4398aa9781ea9b392fd304b845f92) |   |
| Add option to print the BOM from the entry screen | Changed | Tim Schofield | 2018-12-08 | [View](https://github.com/webERP-team/webERP/commit/cb5754b1228b93a3ae34d993b2f9e3d484aeaf0f) |   |
| Add option to import po items | Changed | express achiever | 2018-12-06 | [View](https://github.com/webERP-team/webERP/commit/c24c5217369d9c2299b16c680d06d9bed0354a3d) |   |
| Change field_help_text code (part 1) | Changed | Rafael Chacón | 2018-12-06 | [View](https://github.com/webERP-team/webERP/commit/5b660abb2f2360cfea500ea9cbb5dcfdd4e217be) |   |
| Add page_info_text class | Added | Rafael Chacón | 2018-12-05 | [View](https://github.com/webERP-team/webERP/commit/06151046399d29d04e37f37c88aa63cc7b235d3f) |   |
| Delete unneeded images | Removed | Rafael Chacón | 2018-12-05 | [View](https://github.com/webERP-team/webERP/commit/f40435a7891c72e46b77af6fa2b5441a29ce16a3) |   |
| Fix images call | Fixed | Rafael Chacón | 2018-12-05 | [View](https://github.com/webERP-team/webERP/commit/af967fa3e6c9555693142717f6792dd4044ac516) |   |
| Fix RTL images | Fixed | Rafael Chacón | 2018-12-05 | [View](https://github.com/webERP-team/webERP/commit/e7c875a7feb35aa5bf0a574a363a7d84b959c063) |   |
| Fix the size of some images | Fixed | Rafael Chacón | 2018-12-05 | [View](https://github.com/webERP-team/webERP/commit/fbbd45c81dbf9708dd4bcd4e7de5c7076a9faeaa) |   |
| Per David Shaw Alter all date and datetime fields to have valid defaults '1000-01-01' as '0000-00-00' no longer acceptable | Changed | Phil | 2018-12-01 | [View](https://github.com/webERP-team/webERP/commit/8d94b89dc70b206b82281e702b89063b78189639) |  |
| Standardise PurchasesReport.php | Changed | Rafael Chacón | 2018-11-30 | [View](https://github.com/webERP-team/webERP/commit/34841abdeb195052935ae4d1d1efce708eef4ddc) |   |
| Add SalesReport.php | Added | Rafael Chacón | 2018-11-30 | [View](https://github.com/webERP-team/webERP/commit/9dc2c2cf14a4ec2adca40a7923313833a4b62f1a) |   |
| Add units column to PDFWOPrint.php | Changed | Paul Becker | 2018-11-17 | [View](https://github.com/webERP-team/webERP/commit/f9ee93b083d34e7d21e2ee3bbb861ac0f0d649cd) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8231) |
| Fix rounding of shrink factor in Stocks.php | Fixed | Paul Becker | 2018-11-10 | [View](https://github.com/webERP-team/webERP/commit/) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8240) |
| PDFWOPrint.php: Fix notices, reported in the forums by William Hunter | Fixed | PaulT | 2018-11-06 | [View](https://github.com/webERP-team/webERP/commit/9786f3a5c05b3be1c26827b35ca92f62485d77ee) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8233) |
| New script to enter timeshees with the new table timesheets| Added | Phil | 2018-10-28 |  |  |
| Updates to the manual to describe labour functionality| Added | Phil | 2018-10-20 | [View](https://github.com/webERP-team/webERP/commit/b466bb45d20d1510947bd64048fcaec3c55e6f78) |  |
| Fix error in prnMsg display - DB errors were not reported - had to define $Messages as global inside DB_query function| Fixed | Phil | 2018-10-20 |  |  |
| New script to add employees Employees.php with new table employees for the purposes of time-sheet entry| Added | Phil | 2018-10-20 | [View](https://github.com/webERP-team/webERP/commit/c0bea42348118135c9f19230d8048c1d46d4e2e9) |  |
| Add new config option to allow shortcut menus to be disabled as they can confuse some folks| Changed | Phil | 2018-10-20 | [View](https://github.com/webERP-team/webERP/commit/c0bea42348118135c9f19230d8048c1d46d4e2e9) |  |
| Add manual links for MRP scripts | Changed | Phil | 2018-10-20 | [View](https://github.com/webERP-team/webERP/commit/c0bea42348118135c9f19230d8048c1d46d4e2e9) |  |
| Eliminate pdf_append/ and stockmaster appendfile column | Removed | Paul Becker / Tim | 2018-09-09 | [View](https://github.com/webERP-team/webERP/commit/e0daaed8a9859f41f50821a3339de627b277d954) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8206) |
| Fix filename output in PDFQALabel | Fixed | Paul Becker | 2018-09-02 | [View](https://github.com/webERP-team/webERP/commit/d54dae8c60530b80591081778810e19709e47dbe) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8198) |
| Fix calculation descrepancy in Z_CheckDebtorsControl | Fixed | Paul Becker | 2018-09-02 | [View](https://github.com/webERP-team/webERP/commit/bc14d0d28e4356139d4a05a56d97761c47df1eaf) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8188) |
| Add order number into file name | Change | Paul Becker | 2018-08-28 | [View](https://github.com/webERP-team/webERP/commit/e5fd39ed26b0768ccfbe7dba70e0d633de8635c9) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8194) |
| Log output not working (reported by Michelle) | Fixed | Tim Schofield | 2018-08-28 | [View](https://github.com/webERP-team/webERP/commit/18b96699565897a169e47f588577fda5c50edd47) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8196) |
| Update DB version info (missing with last release) | Changed | Rafael | 2018-08-25 | [View](https://github.com/webERP-team/webERP/commit/fd0cc7a9633ced890a0b15d7184a94feaa5fc42f) |  |
| Add a input check for the sortby input | Fixed | Exson | 2018-07-29 | [View](https://github.com/webERP-team/webERP/commit/593252c64c0b38cc03d661ca6847a1e5096a5222) |  |
| Add script to set period of GL transactions where the period has not been set correctly | Added | Phil | 2018-07-01 | [View](https://github.com/webERP-team/webERP/commit/6136b5a96b564b1add42d2dd3bbe22936f00e476) |  |
| Relocate tcpdf barcodes directory | Fixed | PaulT | 2018-06-28 | [View](https://github.com/webERP-team/webERP/commit/23fd7ff52) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8173&pid=14904#pid14904) |
| Fix $Prefix use in footer.php | Fixed | alanmi3383 | 2018-06-28 | [View](https://github.com/webERP-team/webERP/commit/c1093765f) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8173&pid=14897#pid14897) |
| Add Tim's default shipper for suppliers feature | Added | Tim Schofield | 2018-06-23 | [View](https://github.com/timschofield/webERP-svn/commit/8f8ea3d9c) | [Forum](http://www.weberp.org/forum/showthread.php?tid=2696) |
| Add Tim's defaultgl column feature | Added | Tim Schofield | 2018-06-18 | [View](https://github.com/webERP-team/webERP/pull/67/files) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8169&pid=14931#pid14931) |
| DailyBankTransactions.php (updated script) | Changed | Tim Schofield | 2018-06-18 | [View](https://github.com/webERP-team/webERP/commit/ed1e5e0cb) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8179) |
| MRP.php fix reorder qty insert calculation | Fixed | BrianTMG | 2018-06-17 | [View](https://github.com/webERP-team/webERP/commit/ab6308dcf) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8175) |
| DailyBankTransactions.php calculation fix | Fixed | Paul Becker | 2018-06-16 | [View](https://github.com/webERP-team/webERP/commit/7d39e0bfd) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8178) |

## [v4.15] - 2018-05-20

| Description | Type | Author | Date | Details | Ref |
|:-----------:|:----:|:------:|:----:|:-------:|:---:|
| Upgrade weberpchina.sql file and modify htmlspecialcharts parameter in footer.php | Changed | Exson Qu | 2018-05-15 | [View](http://github.com/webERP-team/webERP/commit/62a4571fb) |  |
| Change module alias from "orders" to "Sales" to standardise in MainMenuLinksArray.php | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/30115ebda) |  |
| Groups the INSERT INTO sentences in SQL upgrade script | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/7ad7340cc) |  |
| Various improvements to manual | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/cfce65413) |  |
| Add script description and ViewTopic to SelectPickingLists.php | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/62c7a5fb0) |  |
| Add ViewTopic and BookMark to GeneratePickingList.php | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/88a43575f) |  |
| Add "id" for $BookMark = 'CounterReturns' in ManualSalesOrders.html | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/a12be5b00) |  |
| Add script description, ViewTopic and BookMark in CounterReturns.php | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/80243e2d1) |  |
| Add info to General Ledger manual | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/a1c8fac82) |  |
| Add "id" for $BookMark = 'SelectContract' in SelectContract.php | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/fa43031fb) |  |
| Add "id" for $BookMark = 'SelectContract' in ManualContracts.html | Changed | Rafael Chacón | 2018-05-08 | [View](http://github.com/webERP-team/webERP/commit/4d75b86c5) |  |
| Add 'Bank Account Balances' and 'Graph of Account Transactions' info to manual. Reorganise 'Inquiries and Reports' and 'Maintenance' of 'General Ledger" chapter of manual. | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/84aa988b3) |  |
| Add script description and BookMark in BankAccountBalances.php | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/21ea3ac27) |  |
| Add section for the "Graph of Account Transactions" script in ManualGeneralLedger.html | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/078c13a60) |  |
| Groups "INSERT INTO `scripts`" sentences and completes empty `description` fields in SQL upgrade script | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/c649a57fb) |  |
| Add script description, also fix $ViewTopic in GLAccountGraph.php| Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/fdfda2148) |  |
| Update Spanish translation | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/41380a6c3) |  |
| Add script description in PDFShipLabel.php | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/8e6513bb8) |  |
| Add script description in PDFAck.php | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/4cd593557) |  |
| Add script description in GeneratePickingList.php | Changed | Rafael Chacón | 2018-05-07 | [View](http://github.com/webERP-team/webERP/commit/4857c4a7e) |  |
| Add script description, ViewTopic and BookMark in PickingLists.php| Changed | Rafael Chacón | 2018-05-06 | [View](http://github.com/webERP-team/webERP/commit/aff58aa7d) |  |
| Rebuild languages files | Changed | Rafael Chacón | 2018-05-06 | [View](http://github.com/webERP-team/webERP/commit/ca7ee2360) |  |
| Add script description, ViewTopic and BookMark in SelectPickingLists.php | Changed | Rafael Chacón | 2018-05-06 | [View](http://github.com/webERP-team/webERP/commit/1c184e09c) |  |
| Update Spanish translation |  Changed| Rafael Chacón | 2018-05-04 | [View](http://github.com/webERP-team/webERP/commit/511eb2e29) |  |
| Fixed bank account and related data visible to an unauthorised user in Dashboard.php | Fixed | Paul Becker | 2018-05-02 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8161) |
| Fix the bug that accountgroup validation used the wrong table and field reported by Laura | Fixed | Exson Qu | 2018-04-30 | [View](https://github.com/webERP-team/webERP/commit/56b66d466e3fdd4d5d55c8e1827a3ab74d69a4b8) |  |
| Added a utility script to fix 1c allocations in AR - a GL journal will be required for the total of debtor balances changed as a result for the control account to remain in balance Z_Fix1cAllocations.php | Added | Phil Daintree | 2018-04-28 | [View](https://github.com/webERP-team/webERP/pull/45/commits/756546887bac32f3ce5d2c357b7e79f7366c0391) |  |
| Added the latest phpxmlrpc code version 4.3.1 - using the compatibility layer though - see https://github.com/gggeek/phpxmlrpc | Changed | Phil Daintree | 2018-04-28 | [View](https://github.com/webERP-team/webERP/pull/45/commits/756546887bac32f3ce5d2c357b7e79f7366c0391) |  |
| Fixed failure to issue invoice for customer reference is more than 20 characters | Fixed | Xiaobotian | 2018-04-27 | [View](https://github.com/webERP-team/webERP/commit/9a7f83ac16e858ac50362cb1e90a74adbdb9f419) |  |
| Added latest SQL update script to UpgradeDatabase.php | Changed | Deibei | 2018-04-27 | [View](https://github.com/webERP-team/webERP/commit/d02430f20e6c044131828f5e27a82471e79f1723) |  |
| Change log updated and formatted in tabular markdown | Changed | Andrew Couling | 2018-04-27 | [View](https://github.com/webERP-team/webERP/commit/bcb543774885bad573081c7798140193665b9bd1) |  |
| Fix the bug of wrong affected scope of bom changing in BOMs.php | Fixed | Exson Qu | 2018-04-26 | [View](http://github.com/webERP-team/webERP/commit/9e8585e91) |  |
| Rebuild languages files | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/1cdd72b5f) |  |
| Minor changes to GeneratePickingList.php | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/ccaf2c404) |  |
| Rebuild languages files Part 3 | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/417971114) |  |
| Rebuild languages files Part 2 | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/bee02030a) |  |
| Rebuild languages files Part 1 | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/04ab5753c) |  |
| Rebuild languages files | Changed | Rafael Chacon | 2018-04-24 | [View](http://github.com/webERP-team/webERP/commit/9b2b54858) |  |
| Correct menu link caption | Fixed | PaulT | 2018-04-20 | [View](http://github.com/webERP-team/webERP/commit/c9fb416a7) |  |
| Fixed the cost calculation bug in Work Order Costing for different standcost of the same stock and use Total Cost Variance to calculate cost variance instead of Total Cost variance for WAC' | Fixed | Exson Qu | 2018-04-18 | [View](http://github.com/webERP-team/webERP/commit/74ac6b4a5) |  |
| New script to graph GL account | Added | Paul Becker | 2018-04-17 | [View](http://github.com/webERP-team/webERP/commit/a930ba0b4) |  |
| Redo changes lost in the last commit | Added | Tim Schofield | 2018-04-11 | [View](http://github.com/webERP-team/webERP/commit/3548fa1b1) |  |
| Add in checkbox for showing zero stocks | Added | Tim Schofield | 2018-04-10 | [View](http://github.com/webERP-team/webERP/commit/74592f0e1) |  |
| Show only non-zero balances, and whether controlled item. | Added | Paul Becker | 2018-04-10 | [View](http://github.com/webERP-team/webERP/commit/a14d3d7b7) |  |
| Fixes incorrect counter numbering of form elements | Fixed | Tim Schofield | 2018-04-10 | [View](http://github.com/webERP-team/webERP/commit/3fcaa6b92) |  |
| Remove tabindex attributes | Changed | Tim Schofield/Jeff Harr | 2018-04-06 | [View](http://github.com/webERP-team/webERP/commit/7d6cec6a2) |  |
| Add XML files to new company copy | Added | PaulT | 2018-04-03 | [View](http://github.com/webERP-team/webERP/commit/5072343f7) |  |
| Fixes to the database files | Fixed | Ap Muthu | 2018-04-03 | [View](http://github.com/webERP-team/webERP/commit/f4d6ff0ac) |  |
| Fix messages not being shown | Fixed | Ap Muthu | 2018-04-03 | [View](http://github.com/webERP-team/webERP/commit/0fcf1c414) |  |
| Synch sqls with upgrade sqls #28 | Fixed | Ap Muthu | 2018-04-03 | [View](http://github.com/webERP-team/webERP/commit/83d8e9ba4) |  |
| Wrong $MysqlExt value in installer | Fixed | Ap Muthu | 2018-04-03 | [View](http://github.com/webERP-team/webERP/commit/eda1245c3) |  |
| Add session name to avoid conflicts | Fixed | PaulT | 2018-04-02 | [View](http://github.com/webERP-team/webERP/commit/2bf01bf9e) |  |
| Remove more unused $db parameters | Deprecated | PaulT | 2018-04-01 | [View](http://github.com/webERP-team/webERP/commit/517f3ed2f) |  |
| Use new period selection functions | Added | Tim Schofield/Paul Becker | 2018-03-31 | [View](http://github.com/webERP-team/webERP/commit/4d3e7d35c) |  |
| If config.php is already in place the error message will never be displayed, so print direct to screen | Fixed | Ap Muthu | 2018-03-31 | [View](http://github.com/webERP-team/webERP/commit/e6e56ac8c) |  |
| Correct invalid function name and clean up code Files Changed: api/api_debtortransactions.ph api/api_stock.php api/api_workorders.php Correct function call from GetNextTransactionNo() to GetNextTransNo() Change variable names to conform to coding standards Change code layout to conform to coding standards Fixes issue no #24 | Fixed | Tim Schofield | 2018-03-29 | [View](http://github.com/webERP-team/webERP/commit/cec3387b2) | [Issue](https://github.com/timschofield/webERP-svn/issues/24) |
| Meet coding standards and more commentary. | Changed | PaulT | 2018-03-27 | [View](http://github.com/webERP-team/webERP/commit/e04d4580b) |  |
| Files to ignore | Changed | Tim Schofield | 2018-03-22 | [View](http://github.com/webERP-team/webERP/commit/22045a5b2) |  |
| Improvements to the period selection functions: 1 Changed variable names to conform to coding standards 2 Added  options to show financial years as well as calendar years 3 Added extra parameter to allow only some options to appear in drop down list 4 Added default case to prevent dropping through without anything being selected | Added | Tim Schofield | 2018-03-20 | [View](http://github.com/webERP-team/webERP/commit/1fd7a0d8e) |  |
| Misspelling of Aged Suppliers Report | Fixed | PaulT/Paul Becker | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/c8dc092fc) |  |
| DeliveryDetails.php: Sales Order required date not reflected in Work Order. | Fixed | PaulT/Jeff Harr | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/d58fb9632) |  |
| GLAccountInquiry: Fix that the script does not automatically show data | Fixed | PaulT/Paul Becker | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/02b212294) |  |
| Add from and to period in links to GLAccountInquiry.php | Added | Tim Schofield | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/f492056dc) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8138&pid=14667#pid14667) |
| Consider discount with SalesGraph | Added | PaulT/Paul Becker | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/acfacde4b) |  |
| Remove unused $db parameter | Deprecated | PaulT | 2018-03-16 | [View](http://github.com/webERP-team/webERP/commit/ff9c4106e) |  |
| Dismissible notification functionality added. New icons and styling added to all themes. Messages now stored in an array before being printed. Assistance from @timschofield and @TurboPT. | Added | Andrew Couling | 2018-03-15 | [View](http://github.com/webERP-team/webERP/commit/488a32560) |  |
| Remove .gitignore from branch | Changed | PaulT | 2018-03-13 | [View](http://github.com/webERP-team/webERP/commit/8294000b6) |  |
| Move hidden input | Changed | PaulT | 2018-03-13 | [View](http://github.com/webERP-team/webERP/commit/2c915bd82) |  |
| "Periods in GL Reports" mod | Added | PaulT/Paul Becker | 2018-03-12 | [View](http://github.com/webERP-team/webERP/commit/1d5e2e28d) |  |
| code review GLPosting.php and Payments.php | Changed | Phil Daintree | 2018-03-12 | [View](http://github.com/webERP-team/webERP/commit/e4d5e9cb7) |  |
| Removed unused variable | Changed | PaulT | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/1411c48fb) |  |
| Link updates to GLAccountInquiry | Changed | PaulT/Paul Becker | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/ee91eb111) |  |
| Add duedate to ORDERY BY clause | Fixed | PaulT/Paul Becker | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/60d85e070) |  |
| Reportwriter mods, part 3 | Changed | PaulT/Paul Becker | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/d94e197ab) |  |
| Fixes error that allowed a transaction to be authorised and posted multiple times by hitting page refresh | Fixed | Tim Schofield | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/683a630e1) |  |
| Logout cookie handling | Changed | PaulT | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/b163d75c1) |  |
| Reportwrite mods, part 2 | Changed | PaulT/Paul Becker | 2018-03-08 | [View](http://github.com/webERP-team/webERP/commit/f3aeea5dc) |  |
| Refreshed README file in markdown format | Added | Andrew Couling | 2018-03-09 | [View](http://github.com/webERP-team/webERP/commit/c075e5765) |  |
| Reportwriter mods. | Changed | PaulT/Paul Becker | 2018-03-06 | [View](http://github.com/webERP-team/webERP/commit/21756696c) |  |
| Show stock adjustments and internal stock requests | Changed | Tim Schofield | 2018-03-06 | [View](http://github.com/webERP-team/webERP/commit/d07055458) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8111) |
| Remove the 'alt' attribute from date inputs | Changed | PaulT | 2018-03-04 | [View](http://github.com/webERP-team/webERP/commit/f7bac6fe8) |  |
| Update MiscFunctions.js | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/63f205820) |  |
| Update DatabaseTranslations.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/a2947cc93) |  |
| Update UPGRADING.txt | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/bafed3526) |  |
| Update manual.css | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/98b5678b9) |  |
| Update INSTALL.txt | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/2bbda6c36) |  |
| Update print.css | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/40b3162f9) |  |
| Update login.css for each theme | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/26df5c58c) |  |
| Update default.css for each theme | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/5f6e798f6) |  |
| Update PDFQuotationPortraitPageHeader.inc | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/36d7f47c7) |  |
| Update PDFQuotationPageHeader.inc | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/086beaaee) |  |
| Update FailedLogin.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/46b9612ed) |  |
| Update CurrenciesArray.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/da99d31b5) |  |
| Update Z_poRebuildDefault.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/01284f7c4) |  |
| Update Z_ChangeStockCode.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/113ab5fcc) |  |
| Update SupplierAllocations.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/5f6c046fc) |  |
| Update PDFQuotationPortrait.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/5d6df7dd3) |  |
| Update PDFQuotation.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/c71a8fc6d) |  |
| Update CustomerAllocations.php | Changed | PaulT | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/a6c962c7c) |  |
| Remove ID svn lines | Changed | Phil Daintree | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/b1f012819) |  |
| Move dashboard link | Changed | PaulT | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/529be6951) |  |
| Remove sourceforge image link - broken | Deprecated | Phil Daintree | 2018-03-03 | [View](http://github.com/webERP-team/webERP/commit/020c9edb0) |  |
| Replace a $Bundle reference with $SItem. | Changed | PaulT | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/d798a999f) |  |
| Petty Cash - Receipt upload filenames now hashed for improved security & new expenses claim field 'Business Purpose' | Added | Andrew Couling | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/d63ecaeca) |  |
| Updates to README.txt | Changed | Phil Daintree | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/24e1e8e40) |  |
| Moved README.txt to root directory | Changed | Phil Daintree | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/60ce8504f) |  |
| Delete README.md | Changed | PhilDaintree | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/bb500beb8) |  |
| Rename README.txt to README.md | Changed | Phil Daintree | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/7fa8c263d) |  |
| Add readme | Added | Phil Daintree | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/33ee42363) |  |
| Update PrintCustTrans.php | Changed | PaulT | 2018-03-02 | [View](http://github.com/webERP-team/webERP/commit/0235627f6) |  |
| Update StockLocMovements.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/b2080a61a) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/8f71a30ce) |  |
| Revert "Revert "Update StockLocMovements.php"" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/18fb22add) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/d8e040941) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/c38a2877c) |  |
| Update ConnectDB_postgres.inc | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/76704de09) |  |
| Update ConnectDB_mysqli.inc | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/60a112ca6) |  |
| Update ConnectDB_mysql.inc | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/92f72c7b1) |  |
| Update header.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/909af543c) |  |
| Update GetConfig.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/ee32394d8) |  |
| Update GetConfig.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/d04c256c0) |  |
| Update TestPlanResults.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/adeddb122) |  |
| Update StockMovements.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/cac9b6a5d) |  |
| Make the email address a mailto: link as in SelectSupplier.php. | Changed | Tim Schofield | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/f15783e07) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8109) |
| Update StockLocMovements.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/f8c1bb5a1) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/d716178a5) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/034b5a2e4) |  |
| Revert "Revert "Update StockLocMovements.php"" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/671837690) |  |
| Revert "Update StockLocMovements.php" | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/bb38776d6) |  |
| Update StockLocMovements.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/011887797) |  |
| Update StockLocMovements.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/d496ef8ef) |  |
| Update SelectPickingLists.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/2f99a1ee1) |  |
| Update PickingLists.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/02bfcd469) |  |
| Update MRPPlannedPurchaseOrders.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/c8282670a) |  |
| Update BOMs_SingleLevel.php | Changed | PaulT | 2018-03-01 | [View](http://github.com/webERP-team/webERP/commit/c296fa1e5) |  |
| Update WorkOrderCosting.php | Changed | PaulT | 2018-02-28 | [View](http://github.com/webERP-team/webERP/commit/191833317) |  |
| Update EDIMessageFormat.php | Changed | PaulT | 2018-02-28 | [View](http://github.com/webERP-team/webERP/commit/a370b1119) |  |
| Update PO_SelectPurchOrder.php | Changed | PaulT | 2018-02-28 | [View](http://github.com/webERP-team/webERP/commit/352151ecd) |  |
| Changes to tables to work with the improved table sorting routine | Changed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/8df9ea96b) |  |
| Add Tim's improved SortSelect() js function replacement. Change also requires `<thead>`, `<tbody>`, and (if needed) `<tfoot>` tags applied to tables that have sorting. Also, change removes the 'alt' attribute from date inputs (handling replaced by commit 7974) within these modified files. | Added | Tim Schofield/PaulT | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/cda963727) | [Forum](http://www.weberp.org/forum/showthread.php?tid=7918) |
| InventoryPlanning.php, InventoryValuation.php, StockCheck.php: Fix view page source message "No space between attributes" reported in Firefox | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/07452b09c) |  |
| Sanitize scripts name in footer.inc and forbidden the use of InputSerialItemsSequential.php without login. | Changed | Exson Qu | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/bc05bb017) |  |
| Stocks.php: Fix navigation bar handling to avoid stockid loss and also disable navigation submit when at the first (or last) item. Change also adds a closing table tag, removes an extra double quote from two attributes, and a minor message layout improvement. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/e012d6364) |  |
| PO_SelectOSPurchOrder.php: Derived from Tim's code: add default current dates. (there may not yet be any purchorders records) / PaulT: do not show the order list table when there are no records to show. (avoids a table heading output without any associated row data) | Changed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/7b3107223) |  |
| MiscFunctions.js: Set the calendar click and change handlers to reference the localStorage DateFormat instead of the element's "alt" attribute value. (Know that this update requires the localStorage change applied with commit 7973) | Changed | Tim Schofield | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/5a136ea9c) |  |
| header.php: Set the DOCTYPE to html5 declaration format, update the meta tag with Content-Type info, and add localStorage with DateFormat and Theme for upcoming changes to table column sorting and calendar handling improvements. | Changed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/1f2dd1dbf) |  |
| CustomerAllocations.php: Minor code shuffle to fix view page source message "Start tag 'div' seen in 'table'" reported in Firefox | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/2216b0d0b) |  |
| Customers.php, ShopParameter.php: Fix view page source message "No space between attributes" reported in Firefox. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/8f51f42b0) |  |
| Labels.php: Remove extra closing `</td>` `</tr>` tag pair. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/5daf2c79f) |  |
| FixedAssetLocations.php: Move closing condition brace to cover entire table output to avoid a stray closing table tag output if the condition is not met. Also, replace some style attributes with equivalent CSS. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/4167599b1) |  |
| MaintenanceUserSchedule.php: Fix closing tag mismatch. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/5fc33c882) |  |
| GLJournalInquiry.php: Add missing = to value attribute. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/63d0e7ee4) |  |
| Fixed the DB_escape_string bug for Array in session.inc and destroy cookie while users log out in Logout.php | Fixed | Exson Qu | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/631d6aebc) |  |
| header.php: Add link to the Dashboard in the AppInfoUserDiv. | Added | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/ee8e7eb3e) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8100) |
| Remove unused $db parameter from many functions within the /api area. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/634c3f93b) |  |
| upgrade4.14.1-4.14.2.sql: Add SQL update to support commit 7961. | Changed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/5935b1e07) |  |
| AgedControlledInventory.php: Add UOM to output. | Added | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/905e650ba) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8091&pid=14286#pid14286) |
| Z_ChangeSalesmanCode.php: New script to change a salesman code. | Added | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/e840b5301) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8094) |
| Minor change to remove number from input field committed with 7946, and add attributes on two input fields. | Changed | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/3e220cf14) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8089&pid=14266#pid14266) |
| Remove $db parameter from PeriodExists(), CreatePeriod(), CalcFreightCost(), CheckForRecursiveBOM(), DisplayBOMItems() and a few other functions. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/69f9dc4a5) |  |
| InternalStockRequest.php: Address a few issues reported by Paul B: Fix Previous/Next handling, table sorting, wrong on-order quantities, and apply the user's display records max. Change also removes unused code and other minor improvements. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/cfc8cfe8b) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8089) |
| StockMovements.php, StockLocMovements.php: Correct stock movements that have more than one serial number as part of it, then the item will appear multiple times in the movements script with the total quantity in each line. For example, if I enter a quantity adjustment for a controlled item, and assign 3 serial numbers to this movement and then run the inquiries, there will be 3 separate lines with a quantity of 3 against each one. | Fixed | Tim Schofield | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/45f169be1) |  |
| SellThroughSupport.php: Remove (another) redundant hidden FormID input. (there were two, overlooked the 2nd one earlier) | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/bd8ae37df) |  |
| SellThroughSupport.php: Remove redundant hidden FormID input. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/bd3e0ec77) |  |
| Contracts.php: Move closing form tag outside of condition. Fixes view page source message: "Saw a form start tag, but there was already an active form element. Nested forms are not allowed. Ignoring the tag." reported in Firefox. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/d2a5d9d7e) |  |
| Remove $db parameter from WoRealRequirements(), EnsureGLEntriesBalance(), and CreateQASample() functions. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/e6aa43214) |  |
| Remove $db parameter from BomMaterialCost(), GetTaxRate(), GetTaxes(), GetCreditAvailable(), ItemCostUpdateGL(), and UpdateCost() functions. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/2ebcfda45) |  |
| Remove $db parameter from all GetStockGLCode() functions. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/7a44c37ef) |  |
| Remove a few lingering $k and $j variables left behind from 7944 commit. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/4bca85d25) |  |
| MRPReschedules.php, MRPShortages.php: Use DB_table_exists() from commit 7943 to replace table check query. | Fixed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/c036edadf) |  |
| Remove the last of the remaining URL 'SID' references. | Deprecated | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/79cd5b540) |  |
| StockLocMovements.php, StockMovements.php: Add serial number column to output. | Added | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/9d5aaa1d1) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8088) |
| InternalStockRequestFulfill.php: Add controlled stock handling within this script. | Added | Paul Becker | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/a88d57e9e) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8086) |
| MRPPlannedPurchaseOrders.php, MRPPlannedWorkOrders.php: Fix conversion factor matter noted by Tim, use DB_table_exists() from commit 7943 to replace table check query, and minor rework to 'missing cell' handling from commit 7939. | Fixed | Tim/Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/21b6010d5) |  |
| Replace old method of table row alternating color handing with improved CSS. Also, this change removes some empty/unused properties from a few css file and removes old URL 'SID' references in files already modified for this commit. Due to SVN issues with TestPlanResults.php, this one file will be committed later. | Changed | Paul Thursby | 2018-02-27 | [View](http://github.com/webERP-team/webERP/commit/a35e49759) |  |
| ConnectDB_xxxx.inc files: Add function DB_table_exists() function to all DB support files, by Tim suggestion. Note that this function will be used in other files in a future commit. Signed-off-by: Paul Thursby | Added | Tim Schofield | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/d502bb706) |  |
| Z_SalesIntegrityCheck.php: Fix that the does not take into account discountpercent so it shows an issue where non exists. | Fixed | Paul Becker | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/b44969048) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8084) |
| UserSettings.php: Fix the 'Maximum Number of Records to Display' from populating with the session default at page load instead of the user's setting. Applied Tim's improved handling. | Fixed | Tim Schofield | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/223abbb93) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8081) |
| geo_displaymap_customers.php, geo_displaymap_suppliers.php: Fix a few PHP short-tags, and move some javascript from PHP output to fix 'missing tag' validation complaints. | Fixed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/2713e4123) |  |
| MRPPlannedPurchasekOrders.php, MRPPlannedWorkOrders.php: PaulT: Add missing table cell to work orders to match recent change to planned purchase orders and replace 'where clause joins' with table join in both files. Apply consistent code formatting between both files. | Fixed | Paul Thursby/Paul Becker | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/889896d2b) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8061) |
| SalesGraph.php: Rework previous 7908 implementation that caused graphing to break. | Fixed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/83ee834cd) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8071) |
| InternalStockRequestInquiry.php: Restore ONE space to previous 7936 commit. | Changed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/49c76ebe2) |  |
| Remove unused $db and $conn parameters from DB_Last_Insert_ID() and (where present) from DB_show_tables(), and DB_show_fields(). Also, remove any unused 'global $db' references across the code base. | Deprecated | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/7f3464c40) |  |
| MRPPlannedPurchaseOrders.php: Add capability to review planned purchase orders and add a new link to convert to a new PO. | Added | Paul Becker | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/ffa090fa8) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8061) |
| PrintCustOrder.php, PrintCustOrder_generic.php, PDFOrderPageHeader_generic.inc: Add units, volume, and weight info, date/signature lines, sales order details narrative, plus other minor PDF formatting. | Added | Paul Becker | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/c7e99651b) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8048) |
| Remove unused $db parameter from DB_fetch_array() and DB_Query() functions. Also, rename several DB_Query names to match function definition name: DB_query. | Deprecated | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/5252106df) |  |
| Dashboard.php: Replace due date handling with existing function. | Changed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/5e94cef20) |  |
| PrintCustTrans.php, PDFTransPageHeader.inc, PrintCustTransPortrait.php, PDFTransPageHeaderPortrait.inc: Add missing stock lot/serial info to landscape output to be consistent with portrait output (reported by HDeriauFF), add Due Date info to invoices (reported by Paul Becker), and (PaulT) add security checks to portrait file, layout improvements, change PDF initialization handling, and more. | Fixed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/079b5e908) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8065&pid=14115#pid14115)) |
| Add a 'warning' case to getMsg(), as there is mixed use of 'warn' and 'warning' usage with prnMsg() calls. The 'warning' (before this change) defaults to an 'info' style message. | Changed | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/e5147580f) |  |
| Remove unused $db parameter from DB_query(), DB_error_no() and DB_error_msg() other DB-related function calls. | Deprecated | Paul Thursby | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/e506e545b) |  |
| Remove stray ; appearing after if, else, and foreach blocks. | Fixed | Paul Becker | 2018-02-26 | [View](http://github.com/webERP-team/webERP/commit/439e34699) | [Forum](http://www.weberp.org/forum/showthread.php?tid=8064) |
| MiscFunctions.php, Z_ChangeStockCode.php, Z_ChangeGLAccountCode.php: Remove unused $db parameter from function ChangeFieldInTable(). | Deprecated | PaulT | 2018-01-27 |  |  |
| New picking list feature for regular and controlled/serialized stock. This feature improves (and replaces) the current pick list handling. | Added | Andrew Galuski/Tim | 2018-01-26 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7988) |
| Add single quotation escape and charset in htmlspecialchars() in session.inc | Changed | Exson | 2018-01-26 |  |  |
| Use htmlspecialchars() to encode html special characters to html entity and set the cookie available only httponly in session.inc | Changed | Exson | 2018-01-26 |  |  |
| ReorderLevel.php: Exclude completed orders from on order counts. | Changed | Briantmg | 2018-01-25 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8060) |
| SelectOrderItems.php: Paul B. Fix stock table columns NOT sorting on this page / PaulT. Use existing CSS to replace two style attributes. | Fixed | PaulT/Paul Becker | 2018-01-24 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8057) |
| SupplierInvoice.php, CounterSales.php: Replace two other hard-coded styles with existing CSS class. | Changed | Andy Couling | 2018-01-24 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8057) |
| SelectOrderItems.php: Paul B. Remove stray value output / PaulT. Replace hard-coded style with existing CSS class. | Fixed | PaulT/Paul Becker | 2018-01-24 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8057) |
| CustomerPurchases.php: Adds Units and Discount columns and other minor changes so that it more closely matches output from OrderDetails.php. | Added | Paul Becker | 2018-01-15 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8040) |
| CustomerPurchases.php: Fix script to show actual Price and actual Amount of Sale based upon discount. | Fixed | Paul Becker | 2018-01-12 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8040) |
| Payments.php: Remove my debug/test echo line from the previous commit. | Fixed | PaulT | 2018-01-09 |  |  |
| Payments.php: Show bank balance at payments. Know that balance display/output is protected by a similar security check manner as protected information at the dashboard. | Added | Paul Becker | 2018-01-09 |  | [Forum](http://weberp.org/forum/showthread.php?tid=8017) |
| Z_MakeNewCompany.php, default.sql, demo.sql: Remove doubled underscore in EDI_Sent reference. | Fixed | Paul Becker | 2018-01-09 |  | [Forum](http://weberp.org/forum/showthread.php?tid=7920) |
| PDFTransPageHeader.inc, PDFTransPageHeaderPortrait.inc: Add additional address fields and/or adds an extra space between some address fields. | Added | Paul Becker | 2018-01-08 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7942) |
| PO_Items.php: Fix/improve Supplier checkbox handling, and fix a PHP7 compatibility issue. | Fixed | Tim | 2018-01-08 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7958) |
| SalesGraph.php: Replace period numbers in graph title with month and year. | Changed | Paul Becker/Tim | 2018-01-08 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7946) |
| WriteReport.inc: Fix broken page number handling. | Fixed | Paul Becker | 2018-01-07 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7955) |
| Change.log: Update remaining past commit entries (during the past few weeks) to give credit to the right person(s) involved with the change, and when applicable, add the related forum URL for historical reference. | Changed | PaulT | 2018-01-07 |  |  |
| Update phpxmlrpc to latest from https://github.com/gggeek/phpxmlrpc | Changed | Phil | 2018-01-07 |  |  |
| Change.log: Update some past commit entries to give credit to the right person(s) involved with the change, and when applicable, add the related forum URL for historical reference. | Changed | PaulT | 2018-01-06 |  |  |
| SelectSalesOrder.php: Fix handling to correct table heading value. | Fixed | Paul Becker/Tim | 2018-01-06 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8000) |
| Attempt to avoid XSS attacks by logged in users by parsing out "script>" from all $_POST and $_GET variables - subsequentely changed to strip_tags from all $_POST and $_GETs per Tim's recommendation | Security | Phil | 2018-01-06 |  |  |
| SelectSalesOrder.php: Fix search to retain quote option and set StockLocation to the UserStockLocation to auto-load current Sales Orders. | Fixed | Paul Becker | 2018-01-03 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8000) |
| SelectSalesOrder.php: Move handling for URL Quotations parameter to top of file to avoid potential page error(s). Handling move reduces code within some conditional checks. This change also includes minor whitespace improvements and removes an unused global reference. | Changed | Paul Becker/Tim | 2018-01-02 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=8000) |
| css/default/default.css: Add text alignment in a couple of styles to match the same use in other CSS to avoid formatting issues when the default theme is used. Also, set several property names to lowercase. | Changed | PaulT | 2018-01-02 |  |  |
| FormMaker.php, ReportMaker.php, WriteForm.inc: A few more PHP 7.1 array compatibility changes. | Changed | PaulT | 2017-12-20 |  |  |
| RCFunctions.inc, FormMaker.php: PHP 7.1 array compatibility change. | Changed | PaulT | 2017-12-20 |  |  |
| PDFOrderStatus.php: Remove redundant ConnectDB.inc include reference. (already included by session.php at the top of the file) | Deprecated | PaulT | 2017-12-19 |  |  |
| Change.log: Correct my Day/Month entry references over the last few days. | Changed | PaulT | 2017-12-19 |  |  |
| Contracts.php: Move work center handling causing a partial form to appear after the footer when no work centers exist. | Fixed | PaulT | 2017-12-19 |  |  |
| Contract_Readin.php: Add customerref field to query to appear in the form when a contract is modified. | Added | Paul Becker | 2017-12-19 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7998) |
| ReportCreator.php: PHP 7.1 array compatibility change. | Changed | rjonesbsink | 2017-12-18 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7969) |
| BOMIndented.php, BOMIndentedReverse.php: Adjust PDF position values, and add UoM, remove stray 0-9 string output. | Added | Paul Becker | 2017-12-18 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7994) |
| PDFBOMListingPageHeader.inc, BOMListing.php: Adjust PDF position values, and add UoM. | Added | Paul Becker | 2017-12-18 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7993) |
| MRPPlannedPurchaseOrders.php, MRPPlannedWorkOrders.php: Fix PDF highlighting, PDF position value adjustments, and other minor tweaks. | Fixed | Paul Becker | 2017-12-15 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7991) |
| CustomerReceipt.php: Wrap delete link parameter values with urlencode(). | Security | Tim | 2017-12-14 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7980) |
| PDFCOA.php: Add column prodspeckey to queries which is used as a description alternative. | Added | Paul Becker | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7989) |
| PDFCOA.php, PDFProdSpec: Minor value adjust to correct inconsistent footer wrap. | Fixed | Paul Becker | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7987) |
| HistoricalTestResults.php, SelectQASamples.php, TestPlanResults.php: Fix date inputs to work with the date picker. | Fixed | PaulT | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7984) |
| PDFQALabel.php: Overlapping in the PDF when printing non-controlled items. | Fixed | Paul Becker | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7976) |
| CustomerReceipt.php: Add identifier to URL for delete link. | Fixed | Paul Becker | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7980) |
| QATests.php: Correct wrong attribute name in two option tags. | Fixed | Paul Becker | 2017-12-13 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7983) |
| PHP 7 constructor compatibility change to phplot.php. | Changed | rjonesbsink | 2017-12-11 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7977) |
| SelectSalesOrder.php: Consistent delivery address and correct a unit conversion issue. | Changed | Paul Becker | 2017-12-11 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7967) |
| PHP 7 constructor compatibility change to htmlMimeMail.php and mimePart.php. | Changed | rjonesbsink | 2017-12-11 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7971) |
| Order by transaction date and add link to debtors in Dashboard.php script. | Changed | RChacon | 2017-12-06 |  |  |
| Phil commited Tim's BankAccountBalances.php script | Added | Tim | 2017-12-03 |  |  |
| Fixed the outstanding quantity is not right in PO_SelectOSPurchOrder.php. | Fixed | Exson | 2017-12-02 |  |  |
| Fix for javascript date picker for US date formats | Fixed | Tim | 2017-12-02 |  |  |
| Purchases report - also deleted id non-exsitent in css committed changes suggested by VortecCPI | Changed | Phil/Paul Becker | 2017-12-02 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=7943) |
| Added Petty Cash receipt file upload to directory functionality. | Added | Andy Couling | 2017-11-23 |  |  |
| Remove cost updating for WAC method in BOMs.php. | Changed | Exson | 2017-11-07 |  |  |
| Fixed the salesman authority problem in PrintCustTrans.php. | Fixed | Exson | 2017-10-25 |  |  |
| Prevent sales man from viewing other sales' sales orders in PrintCustTrans.php. | Fixed | Exson | 2017-10-23 |  |  |
| Prevent customer from modifying or viewing other customer's order in SelectOrderItems.php and PrintCustTrans.php. | Fixed | Exson | 2017-10-23 |  |  |
| Change header to meta data to avoid header do not work in some server environment in SelectCompletedOrder.php and SelectGLAccount.php | Changed | Exson | 2018-10-21 |  |  |
| Removed reference to css class 'toplink' in CustomerInquiry.php and CustomerAccount.php. | Fixed | Andy Couling | 2017-10-17 |  |  |
| Fix InventoryPlanning.php and includes/PDFInventoryPlanPageHeader.inc to display categories selected and fix month headings displayed | Fixed | Phil | 2017-10-17 |  |  |
| New Expenses/Update Expense table header in PcClaimExpensesFromTab.php | Added | Andy Couling | 2017-10-15 |  |  |
| Fixed the edit/delete cash assignment functionality in PcAssignCashToTab.php | Fixed | Andy Couling | 2017-10-15 |  |  |
| Table header labels corrected in PcClaimExpensesFromTab.php and PcAuthorizeExpenses.php. | Fixed | Andy Couling | 2017-10-15 |  |  |
| Fixed expense deletion dialogue box in PcClaimExpensesFromTab.php. | Fixed | Andy Couling | 2017-10-15 |  |  |
| Missing $Id comments added to Petty Cash scripts. | Changed | Andy Couling | 2017-10-15 |  |  |
| Fixed the bug that Narrative information will loss when add or remove controlled items lot no in StockAdjustments.php. | Fixed | Exson | 2017-10-10 |  |  |
| If it is set the $_SESSION['ShowPageHelp'] parameter AND it is FALSE, hides the page help text (simplifies code using css). | Changed | RChacon | 2017-10-10 |  |  |
| Set decimals variable for exchange rate in Currencies.php. | Changed | RChacon | 2017-10-10 |  |  |
| Improve currency showing and set decimals variable for exchange rate in Payments.php. | Changed | RChacon | 2017-10-10 |  |  |
| Fix the indian_number_format bug in MiscFunctions.php. | Fixed | Exson | 2017-10-10 |  |  |
| Fixed the non-balance bug in CustomerReceipt.php. And fixed the non rollback problem when there is a non-balance existed. Fixed error noises. | Fixed | Exson | 2017-10-09 |  |  |
| Add html view to SuppPriceList.php. | Added | Exson | 2017-10-03 |  |  |
| Standardise and add icons for usability. | Added | RChacon | 2017-09-20 |  |  |
| Increases accuracy in coordinates. | Changed | RChacon | 2017-09-20 |  |  |
| Fixed the wrong price retrieved bug in PO_Header.php. | Fixed | Exson | 2017-09-20 |  |  |
| Fixed the vendor price bug to ensure only the effective price showed by suppliers in SelectProduct.php. | Fixed | Exson | 2017-09-20 |  |  |
| Fixed the bug to make GRN reverse workable. | Fixed | Exson | 2017-09-20 |  |  |
| Customer information missing in CustomerReceipt.php. Reported by Steven Fu. | Fixed | Exson | 2017-09-19 |  |  |
| Geocode bug fixes | Fixed | Tim | 2017-09-18 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=4380) |
| SelectProduct.php made image dispay code match that used in WorkOrderEntry.php | Fixed | Paul Becker | 2017-09-17 |  |  |
| Fixed the onclick delete confirmation box call in ContractBOM.php, was 'MakeConfirm'. | Fixed | Andy Couling | 2017-09-11 |  |  |
| Code consistency in PO_Items.php. | Changed | Andy Couling | 2017-09-11 |  | [Forum](http://www.weberp.org/forum/showthread.php?tid=4355) |
| Z_ChangeLocationCode.php: Add missing locationusers table update, reported by Paul Becker in forums. | Fixed | PaulT | 2017-09-08 |  |  |
| Fix portrait invoice email now has narrative of correct invoice number! | Fixed | Phil | 2017-09-08 |  |  |
| Petty cash improvements to tax taken from Tim's work | Added | Andy Couling | 2017-09-08 |  |  |
| Fix currency translation in PO_AuthorisationLevels.php. | Fixed | RChacon | 2017-09-06 |  |  |
| Fixed the bug that invoice cannot be issued by same transaction multiple times in SuppTrans.php. | Fixed | Exson | 2017-09-06 |  |  |
| Fixed the bug that can not display correctly while the same debtors has more than one transaction and make GL account which is not AR account or bank account transaction showing on too. | Fixed | Exson | 2017-08-30 |  |  |
| Fixed the default shipper does not work in CustomerBranches.php reported by Steven. | Fixed | Exson | 2017-08-30 |  |  |
| CounterSales.php and StockAdjustments.php: Apply fixes posted by Tim in weberp forums. | Fixed | PaulT | 2017-08-10 |  |  |
| Fixed the search failure problem due to stock id code in SelectWorkOrder.php. | Fixed | Exson | 2017-07-27 |  |  |
| Add QR code for item issue and fg collection for WO in PDFWOPrint.php | Fixed | Exson | 2017-07-18 |  |  |
| Fix call to image tick.svg. | Fixed | RChacon | 2017-07-17 |  |  |
| Utility script to remove all purchase back orders | Added | Phil | 2017-07-15 |  |  |
| Fixed the wrong price bug and GP not updated correctly in SelectOrderItems.php. Report by Robert from MHHK forum. | Fixed | Exson | 2017-07-10 |  |  |
| reportwriter/admin/forms area, fix tag name in four files: `<image>` to `<img>` | Fixed | PaulT | 2017-07-08 |  |  |
| DefineImportBankTransClass.php - Remove extra ( | Fixed | PaulT | 2017-07-04 |  |  |
| Fixed the argument count error in SupplierInvoice.php. | Fixed | Exson | 2017-06-30 |  |  |

## [v4.14.1] - 2017-06-26

| Description | Type | Author | Date | Details | Ref |
|:------------|:----:|:------:|:----:|:-------:|:---:|
| Merge css/WEBootstrap/css/custom.css into css/WEBootstrap/default.css to preserve bootstrap as original. |  | RChacon | 2022-06-25 |  |  |
| Add style sections for device rendering width ranges for no responsive themes. |  | RChacon | 2022-06-24 |  |  |
| Fix class for TransactionsDiv, InquiriesDiv and MaintenanceDiv. Fix bootstrap copy. |  | RChacon | 2022-06-23 |  |  |
| Fixed the Over Receive Portion bug in WorkOrderReceive.php. |  | Exson | 2022-06-22 |  |  |
| Add meta viewport for initial-scale=1 for working css in small devices. |  | RChacon | 2017-06-21 |  |  |


[Unreleased]: https://github.com/webERP-team/webERP/compare/v4.15.2...HEAD
[v4.15.2]: https://github.com/webERP-team/webERP/compare/v4.15.1...v4.15.2
[v4.15.1]: https://github.com/webERP-team/webERP/compare/v4.15...v4.15.1
[v4.15]: https://github.com/webERP-team/webERP/compare/v4.14.1...v4.15
[Semantic Versioning]: http://semver.org/spec/v2.0.0.html
[Keep a Changelog]: http://keepachangelog.com/en/1.0.0/
[CHANGELOG_ARCHIVE.md]: CHANGELOG_ARCHIVE.md
