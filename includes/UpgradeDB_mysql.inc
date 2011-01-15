<?php

/* mysql specific functions for the database upgrade script
 */

function CharacterSet($table, $db) {
	$sql="SELECT TABLE_COLLATION
		FROM information_schema.tables
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$table."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	return $myrow['TABLE_COLLATION'];
}

function AddColumn($Column, $Table, $Type, $Null, $Default, $After, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Column;
		$result = DB_query($sql, $db);
	}
	if (isset($SQLFile) or DB_num_rows($result)==0) {
		if ($Type=='text') {
			$response=executeSQL("ALTER TABLE `".$Table."` ADD COLUMN `".$Column."` ".$Type." ".$Null.
				" AFTER `".$After."`", $db, False);
		} else {
			$response=executeSQL("ALTER TABLE `".$Table."` ADD COLUMN `".$Column."` ".$Type." ".$Null." DEFAULT '".$Default.
				"' AFTER `".$After."`", $db, False);
		}
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been inserted') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be inserted') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('already exists') , 'info');
	}
}

function DropColumn($Column, $Table, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Column;
		$result = DB_query($sql, $db);
	}
	if (isset($SQLFile) or DB_num_rows($result)!=0) {
		$response=executeSQL("ALTER TABLE `".$Table."` DROP `".$Column, $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already removed') , 'info');
	}
}

function ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size, $db) {
	$sql="SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Size) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function ChangeColumnName($OldName, $Table, $Type, $Null, $Default, $NewName, $db, $AutoIncrement='') {
	$sql="SELECT CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$OldName."'";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		if ($AutoIncrement=='') {
			$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$OldName." ".$NewName." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		} else {
			$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$OldName." ".$NewName." ".$Type." ".$Null." ".$AutoIncrement, $db, False);
		}
		if ($response==0) {
			OutputResult( _('The column').' '.$OldName.' '._('has been renamed').' '.$NewName , 'success');
		} else {
			OutputResult( _('The column').' '.$OldName.' '._('could not be renamed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$OldName.' '._('is already changed') , 'info');
	}
}

function ChangeColumnType($Column, $Table, $Type, $Null, $Default, $db) {
	$sql="SELECT DATA_TYPE
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Type) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function ChangeColumnDefault($Column, $Table, $Type, $Null, $Default, $db) {
	$sql="SELECT COLUMN_DEFAULT
		FROM information_schema.columns
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND COLUMN_NAME='".$Column."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]<>$Default) {
		$response=executeSQL("ALTER TABLE ".$Table." CHANGE COLUMN ".$Column." ".$Column." ".$Type." ".$Null." DEFAULT '".$Default."'", $db, False);
		if ($response==0) {
			OutputResult( _('The column').' '.$Column.' '._('has been changed') , 'success');
		} else {
			OutputResult( _('The column').' '.$Column.' '._('could not be changed') , 'error');
		}
	} else {
		OutputResult( _('The column').' '.$Column.' '._('is already changed') , 'info');
	}
}

function NewConfigValue($ConfName, $ConfValue, $db) {
	$sql="SELECT confvalue
		FROM config
		WHERE confname='".$ConfName."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("INSERT INTO `config` (`confname`, `confvalue`) VALUES ('".$ConfName."', '".$ConfValue."')", $db, False);
		if ($response==0) {
			OutputResult( _('The config value').' '.$ConfName.' '._('has been inserted') , 'success');
		} else {
			OutputResult( _('The config value').' '.$ConfName.' '._('could not be inserted') , 'error');
		}
	} else {
		OutputResult( _('The config value').' '.$ConfName.' '._('is in') , 'info');
	}
}

function CreateTable($Table, $sql, $db) {
	$ShowSQL="SHOW TABLES WHERE Tables_in_".$_SESSION['DatabaseName']."='".$Table."'";
	$result=DB_Query($ShowSQL, $db);

	if (DB_num_rows($result)==0) {
		$response=executeSQL($sql, $db, False);
		if ($response==0) {
			OutputResult( _('The table').' '.$Table.' '._('has been created') , 'success');
		} else {
			OutputResult( _('The table').' '.$Table.' '._('could not be created') , 'error');
		}
	} else {
		OutputResult( _('The table').' '.$Table.' '._('already exists') , 'info');
	}
}

function ConstraintExists($Table, $Constraint, $db) {
	$sql="SELECT CONSTRAINT_NAME
		FROM information_schema.TABLE_CONSTRAINTS
		WHERE TABLE_SCHEMA='".$_SESSION['DatabaseName']."'
			AND TABLE_NAME='".$Table."'
			AND CONSTRAINT_NAME='".$Constraint."'";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		return false;
	} else {
		return true;
	}
}

function DropConstraint($Table, $Constraint, $db) {
	if (ConstraintExists($Table, $Constraint, $db)) {
		$response=executeSQL("ALTER TABLE `".$Table."` DROP FOREIGN KEY `".$Constraint."`", $db, False);
		if ($response==0) {
			OutputResult( _('The constraint').' '.$Constraint.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The constraint').' '.$Constraint.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The constraint').' '.$Constraint.' '._('does not exist') , 'info');
	}
}

function AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField, $db) {
	if (!ConstraintExists($Table, $Constraint, $db)) {
		$response=executeSQL("ALTER TABLE ".$Table. " ADD CONSTRAINT ".$Constraint." FOREIGN KEY (".$Field.
			") REFERENCES ".$ReferenceTable." (".$ReferenceField.")", $db, False);
		if ($response==0) {
			OutputResult( _('The constraint').' '.$Constraint.' '._('has been added') , 'success');
		} else {
			OutputResult( _('The constraint').' '.$Constraint.' '._('could not be added') , 'error');
		}
	} else {
		OutputResult( _('The constraint').' '.$Constraint.' '._('already exists') , 'info');
	}
}

function UpdateField($Table, $Field, $NewValue, $Criteria, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="desc ".$Table." ".$Field;
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_row($result);
	} else {
		$myrow[0]=0;
	}
	if ($myrow[0]!=0 or DB_num_rows($result)>0) {
		$sql="SELECT ".$Field." FROM ".$Table." WHERE ".$Criteria;
		$result=DB_query($sql, $db);
		$myrow=DB_fetch_row($result);
		if ($myrow[0]!=$NewValue) {
			$response=executeSQL("UPDATE ".$Table." SET ".$Field."='".$NewValue."' WHERE ".$Criteria, $db, False);
			if ($response==0) {
				OutputResult( _('The field').' '.$Field.' '._('has been updated') , 'success');
			} else {
				OutputResult( _('The field').' '.$Field.' '._('could not be updated') , 'error');
			}
		} else {
			OutputResult( _('The field').' '.$Field.' '._('is already correct') , 'info');
		}
	} else if (isset($SQLFile)) {
		$response=executeSQL("UPDATE ".$Table." SET ".$Field."='".$NewValue."' WHERE ".$Criteria, $db, False);
	}
}

function DeleteRecords($Table, $Criteria, $db) {
	$sql="SELECT * FROM ".$Table." WHERE ".$Criteria;
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		$response=executeSQL("DELETE FROM ".$Table." WHERE ".$Criteria, $db, False);
		if ($response==0) {
			OutputResult( _('Rows have been deleted from').' '.$Table , 'success');
		} else {
			OutputResult( _('Rows could not be deleted from').' '.$Table , 'error');
		}
	} else {
		OutputResult( _('There was nothing to delete from').' '.$Table , 'info');
	}
}

function DropTable($Table, $Field, $db) {
	$sql="SHOW tables WHERE Tables_in_".$_SESSION['DatabaseName']." ='".$Table."'";
	$result=DB_query($sql, $db);
	$CanDrop=False;
	if (DB_num_rows($result)>0) {
		$CanDrop=True;
		$sql="desc ".$Table." ".$Field;
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)>0) {
			$CanDrop=True;
		} else {
			$CanDrop=False;
		}
	}
	if ($CanDrop) {
		$response=executeSQL("DROP TABLE IF EXISTS `".$Table."`", $db);
		if ($response==0) {
			OutputResult( _('The old table').' '.$Table.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The old table').' '.$Table.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The old table').' '.$Table.' '._('has already been removed') , 'info');
	}
}

function InsertRecord($Table, $CheckFields, $CheckValues, $Fields, $Values, $db) {
	global $SQLFile;
	if (DB_table_exists($Table, $db)) {
		$sql="SELECT * FROM ".$Table." WHERE ";
		for ($i=0;$i<sizeOf($CheckFields);$i++) {
			$sql = $sql.$CheckFields[$i]."='".$CheckValues[$i]."' AND ";
		}
		$sql=substr($sql, 0, strlen($sql)-5);
		$result=DB_query($sql, $db);
	}
	if (DB_num_rows($result)==0 or isset($SQLFile)) {
		$sql="INSERT INTO ".$Table." (";
		for ($i=0;$i<sizeOf($Fields);$i++) {
			$sql = $sql.$Fields[$i].",";
		}
		$sql=substr($sql, 0, strlen($sql)-1).") VALUES (";
		for ($i=0;$i<sizeOf($Values);$i++) {
			$sql = $sql."'".$Values[$i]."',";
		}
		$sql=substr($sql, 0, strlen($sql)-1).")";
		$response=executeSQL($sql, $db);
		if ($response==0) {
			OutputResult( _('The record has been inserted') , 'success');
		} else {
			OutputResult( _('The record could not be inserted') , 'error');
		}
	} else {
		OutputResult( _('The record is already in the table') , 'info');
	}
}

function DropPrimaryKey($Table, $OldKey, $db) {
	$Total=0;
	foreach ($OldKey as $Field) {
		$sql="select * from information_schema.key_column_usage where table_name='".$Table."' and constraint_name='primary' and
		table_schema='".$_SESSION['DatabaseName']."' AND COLUMN_NAME='".$Field."'";
		$result = DB_query($sql, $db);
		$Total = $Total + DB_num_rows($result);
	}
	if ($Total==sizeOf($OldKey)) {
		$response=executeSQL("ALTER TABLE ".$Table." DROP PRIMARY KEY", $db);
		if ($response==0) {
			OutputResult( _('The primary key in').' '.$Table.' '._('has been removed') , 'success');
		} else {
			OutputResult( _('The primary key in').' '.$Table.' '._('could not be removed') , 'error');
		}
	} else {
		OutputResult( _('The primary key in').' '.$Table.' '._('has already been removed') , 'info');
	}
}

function AddPrimaryKey($Table, $Fields, $db) {
	$sql="select * from information_schema.key_column_usage where table_name='".$Table."' and constraint_name='primary' and
		table_schema='".$_SESSION['DatabaseName']."'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)!=sizeOf($Fields)) {
		$KeyString=implode(",", $Fields);
		$response=executeSQL("ALTER TABLE ".$Table." ADD PRIMARY KEY ( ".$KeyString." )", $db);
		if ($response==0) {
			OutputResult( _('The primary key in').' '.$Table.' '._('has been added') , 'success');
		} else {
			OutputResult( _('The primary key in').' '.$Table.' '._('could not be added') , 'error');
		}
	} else {
		OutputResult( _('The primary key in').' '.$Table.' '._('has already been added') , 'info');
	}
}

function RenameTable($OldName, $NewName, $db) {
	$sql="SHOW TABLES WHERE Tables_in_".$_SESSION['DatabaseName']."='".$OldName."'";
	$result=DB_Query($sql, $db);

	if (DB_num_rows($result)!=0) {
		$response=executeSQL("RENAME TABLE ".$OldName." to ".$NewName, $db, False);
		if ($response==0) {
			OutputResult( _('The table').' '.$OldName.' '._('has been renamed to').' '.$NewName , 'success');
		} else {
			OutputResult( _('The table').' '.$OldName.' '._('could not be renamed to').' '.$NewName , 'error');
		}
	} else {
		OutputResult( _('The table').' '.$NewName.' '._('already exists') , 'info');
	}
}

function OutputResult($msg, $status) {
	if ($status=='error') {
		$_SESSION['Updates']['Errors']++;
		$_SESSION['Updates']['Messages'][]=$msg;
//		echo '<td style="background-color: #fddbdb;color: red;">';
	} else if ($status=='success') {
		$_SESSION['Updates']['Successes']++;
//		echo '<td style="background-color: #b9ecb4;color: #006400;">';
	} else {
		$_SESSION['Updates']['Warnings']++;
//		echo '<td style="background-color: #c7ccf6;color: navy;">';
	}
//	echo $msg;
//	echo '</td>';
}


?>
