--  Generate SQL 
--  Version:                   	V5R3M0 040528 
--  Generated on:              	01/09/06 09:22:37 
--  Relational Database:       	S105HR8M 
--  Standards Option:          	DB2 UDB iSeries 
  
drop table greg.oedtwk3;  
CREATE TABLE GREG.OEDTWK3 ( 
--  SQL150B   10   REUSEDLT(*NO) in table OEDTWK in GREG ignored. 
--  SQL1509   10   Format name OEDTWKR for OEDTWK in GREG ignored. 
	O1OCTL NUMERIC(8, 0) NOT NULL DEFAULT 0 , 
	O1ORL# DECIMAL(3, 0) NOT NULL DEFAULT 0 , 
	PRIMARY KEY( O1OCTL , O1ORL#  ) ) ; 

