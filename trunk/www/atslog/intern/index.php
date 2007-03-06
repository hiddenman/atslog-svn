<?php


 /*                              
                                  
    ���������                     
                                     
 */                              
                                      
include("../include/config.inc.php");

 /* 

    �������

 */
 
include('../include/set/functions.php');

// ���������� ������������ ������� ���������� �� ���� ������������ � ������������� �
// ������� � ������ register_globals
//
import_request_variables("gPc", "rvar_");
if (!empty($rvar_debug))		$debug     = translateHtml($rvar_debug);
if (!empty($rvar_lang))                 $lang     = translateHtml($rvar_lang);
if (!empty($rvar_color)) $color=translateHtml($rvar_color);
if (!empty($rvar_msg)) $msg=translateHtml($rvar_msg);
if (!empty($rvar_cacheflush)) $cacheflush=translateHtml($rvar_cacheflush);

	// Load language
	LanguageSetup($lang);
	
	// Colors scheme   
	ColorSetup($color);

	connect_to_db();

	if (!checkpass()) {
	    nopass();
	}
	if (!hasprivilege("access", false)) {
    	    nopass();
	}

	hasprivilege("parameters", true);

	if (isset($_POST["add"]) && $_POST["add"])
	{
		if ($_POST["internally"] == "")
		{
			header("Location: ?msg=3");
			die;
		}
		if ($_POST["name"] == "")
		{
			header("Location: ?msg=2");
			die;
		}
		if(!$demoMode){
		    $conn->Execute("insert into intphones (intnumber, name) values ('".$_POST["internally"]."', '".$_POST["name"]."')");
		}
# ����� ���� �������� ������ �� ����� ����� �� ����.
		header("Location: ?cacheflush=1");
		die;
	}
	else if (isset($_POST["edit"]) && $_POST["edit"])
	{
		if ($_POST["internally"] == "")
		{
	    	    header("Location: ?msg=3");
		    die;
		}
		if ($_POST["name"] == "")
		{
		    header("Location: ?msg=2");
		    die;
		}
		$q4="SELECT 0 FROM intphones WHERE intnumber = '".$_POST["edit"]."'";
		if($debug) echo $q4."<br>";
				
		$res = $conn->Execute($q4);
		if (isset($res->fields[0])){
		    if(!$demoMode){
			$conn->Execute("update intphones set intnumber='".$_POST["internally"]."', name = '".$_POST["name"]."' where intnumber = '".$_POST["edit"]."'");
		    }
		}else{
		    if(!$demoMode){
			$conn->Execute("insert into intphones (intnumber, name) values ('".$_POST["internally"]."', '".$_POST["name"]."')");
		    }
		}
# ����� ���� �������� ������ �� ����� ����� �� ����.
		header("Location: ?cacheflush=1");
		die;
	}
	else if (isset($_GET["delete"]) && $_GET["delete"])
	{
		$q5="delete from intphones where intnumber = '".$_GET["delete"]."'";
		if($debug) echo $q5."<br>";
		if(!$demoMode){
		    $conn->Execute($q5);
		}
		
# ����� ���� �������� ������ �� ����� ����� �� ����.
		header("Location: ?cacheflush=1");
		die;
	}
$title=strtr($GUI_LANG['ParametersOfInternalPhones'],$GUI_LANG['UpperCase'],$GUI_LANG['LowerCase']);
if(empty($export)) include("../include/set/header.html");
?>
<div>
<table cellpadding=0 cellspacing=0 border=0 width="100%">
    <tr><td colspan=2><?php
$user="";
if(IsDefaultPass($_SERVER['PHP_AUTH_USER'])) echo "<font color=red><b>".$GUI_LANG['ChangeDefaultAdminPassword']."</b></font><br>&nbsp;";
?></td>
    </tr>
    <tr>
<?php
    menucomplit("intern");
?>
	<td width=50%>&nbsp;</td>
    </tr>
</table>
	    </div>
	</td>
    </tr>
    <tr>
	<TD>
<?php
    echo $GUI_LANG['Abonent'].": ".$authrow["login"]." (".$authrow["lastname"]." ".$authrow["firstname"]." ".$authrow["secondname"].")";
?>
	</td>
    </tr>
    <tr>
	<td>
<?php
	echo "<H1>".$GUI_LANG['Phones'].":</H1>";
	if(isset($_GET["msg"])){
		switch ($_GET["msg"])
		{
			case 2:
				echo "<div><font color=red><b>".$GUI_LANG['EnterTheDescriptionOfInternalPhone']."</b></font></div><br>";
				break;
				case 3:
				echo "<div><font color=red><b>".$GUI_LANG['EnterAnInternalPhoneNumber']."</b></font></div><br>";
				break;
		}
	}
	$q1="SELECT calls.internally,users.lastname, users.firstname, users.secondname
	FROM calls
	LEFT JOIN users ON users.internally = calls.internally
	group by calls.internally,users.lastname,users.firstname,users.secondname
	order by internally ASC
	";
	if($debug) echo $q1."<br>";

	$q2="SELECT intnumber, name from intphones order by intnumber ASC";
	if($debug) echo $q2."<br>";

	$conn->setFetchMode(ADODB_FETCH_NUM);
	if(isset($cacheflush) && $cacheflush) $res1 = $conn->CacheFlush($q1);
	$res1 = $conn->CacheExecute($q1);
	if ($res1 && $res1->RecordCount() > 0) { 
	    while (!$res1->EOF) {
		$mamba=$res1->fields;
		$allIntern[]=array(0 => $mamba[0],2 => $mamba[1],3 => $mamba[2],4 => $mamba[3]);
		$res1->MoveNext();
	    }
	}

	$conn->setFetchMode(ADODB_FETCH_NUM);
	if(isset($cacheflush) && $cacheflush) $res = $conn->CacheFlush($q2);
	$res = $conn->CacheExecute($q2);
	if ($res && $res->RecordCount() > 0) { 
	    while (!$res->EOF) {
		$allRows[]=$res->fields;
		$res->MoveNext();
	    }
	}
        if(!empty($allIntern)){
	    while (list($k, $v) = each($allIntern)) {
		if(!empty($allRows)){
		    reset($allRows);
		    while (list($ke, $va) = each($allRows)) {
			if ($v[0]==$va[0]){
			    $dobavit=FALSE;
			    break;
			}else{
			    $dobavit=TRUE;
			}
		    }
		}else{
		    $dobavit=TRUE;
		}
		if($dobavit){
		    $allRows[]=$v;
		}
	    }
	}
	if(!empty($allRows)){
	    reset($allRows);
	    ksort($allRows);
	}
	echo "
	<table cellspacing=0 cellpadding=1 border=0>
	    <TR ".$COLORS['BaseBorderTableBgcolor'].">
		<td>
	<table cellspacing=1 cellpadding=4 border=0><TR ".$COLORS['BaseTablesBgcolor']."><TH>&nbsp;</TH><TH>".$GUI_LANG['InternalPhones']."</TH><TH>".$GUI_LANG['Description']."</TH><TH>&nbsp;</TH>";
	if(!empty($allRows)){
	    while (list($key, $row) = each($allRows))
	    {
		echo "\n<TR ".$COLORS['BaseTrBgcolor']."><TD>";

		$comment='';
		unset($postComment);
		if(isset($row[1])){
		    $comment=$row[1];
		    $shmayster=FALSE;
		}else{
		    $preComment=array($row[2],$row[3],$row[4]);
			if(!isset($postComment)) $postComment=array();
		    array($postComment);
		    while (list($key, $r) = each($preComment)){
			if(!empty($r)){
			    $postComment[]=$r;
			}
		    }
		    if(!empty($postComment)){
			$comment=implode(" ",$postComment);
		    }
		    $shmayster=TRUE;
		}

		if (isset($_GET["edit"]) && $_GET["edit"] == $row[0])
		{
			echo "<IMG alt=\"\" SRC=\"../include/img/rowselected.gif\" WidTH=19 HEIGHT=18></TD>\n";
			echo "<TD><FORM METHOD=POST STYLE=\"margin:0\"><INPUT TYPE=\"text\" NAME=\"internally\" VALUE=\"".htmlspecialchars($row[0])."\" SIZE=5></TD>\n";
			echo "<TD><INPUT TYPE=\"text\" NAME=\"name\" VALUE=\"".htmlspecialchars($comment)."\" SIZE=30></TD>\n";
			echo "<TD><INPUT TYPE=\"hidden\" NAME=\"edit\" VALUE=\"".$row[0]."\"><INPUT TYPE=IMAGE WidTH=16 HEIGHT=16 SRC=\"../include/img/save.gif\" ALT=\"".$GUI_LANG['Save']."\" onclick=\"submit();\" style=\"cursor:hand;\"><IMG WidTH=16 HEIGHT=16 src=\"../include/img/undo.gif\" alt=\"".$GUI_LANG['Cancel']."\" onclick=\"window.location = '?';\" style=\"cursor:hand;\"></FORM></TD>\n";
		}
		else
		{
			echo "<IMG alt=\"\" WidTH=19 HEIGHT=18 SRC=\"../include/img/row.gif\"></TD>";
			echo "<TD>".htmlspecialchars($row[0])."</TD>\n";

			if($shmayster){
			    echo "<TD><font ".$COLORS['HiddenFont'].">".htmlspecialchars($comment)."</font></TD>";
			}else{
			    echo "<TD>".htmlspecialchars($comment)."</TD>";
			}
			echo "<TD><IMG WidTH=16 HEIGHT=16 HSPACE=2 VSPACE=2 SRC=\"../include/img/button_edit.png\" ALT=\"".$GUI_LANG['Edit']."\" onclick=\"window.location = '?edit=".$row[0]."';\" style=\"cursor:hand;\">";
			echo "<IMG WidTH=16 HEIGHT=16 HSPACE=2 VSPACE=2 SRC=\"../include/img/button_drop.png\" ALT=\"".$GUI_LANG['Delete']."\" onclick=\"if (window.confirm('".$GUI_LANG['DeleteDescriptionOfInternalPhone']." ".$row[0]."?')) window.location = '?delete=".$row[0]."';\" style=\"cursor:hand;\"></TD>";
		}
		echo "\n</TR>";
	    }
	}
	echo "<TR ".$COLORS['BaseTrBgcolor']."><TD><IMG alt=\"\" WidTH=19 HEIGHT=18 SRC=\"../include/img/rownew.gif\"><FORM METHOD=POST STYLE=\"margin:0\"></TD>";
	echo "<TD><INPUT TYPE=\"text\" NAME=\"internally\" SIZE=5></TD>";
	echo "<TD><INPUT TYPE=\"text\" NAME=\"name\" SIZE=30></TD>";
	echo "<TD><INPUT TYPE=\"hidden\" NAME=\"add\" VALUE=\"1\"><INPUT TYPE=IMAGE WidTH=16 HEIGHT=16 SRC=\"../include/img/new.gif\" ALT=\"".$GUI_LANG['Add']."\" onclick=\"submit();\" style=\"cursor:hand;\"></FORM></TD></TR>\n";
	echo "</TABLE>
		</td>
	    </tr>
	</table>
	";

    include("../include/set/footer.html");
?>
