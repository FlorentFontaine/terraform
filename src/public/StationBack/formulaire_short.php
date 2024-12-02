<?php 

use Classes\DB\Database;
use Helpers\StringHelper;

header("Content-type: text/html; charset=iso-8859-1");

require_once '../ctrl/ctrl.php';

$MyPost = $_SESSION["formulaire_short"];
$_SESSION["formulaire_short"] = NULL;

?>
<form action="?" method="post">
<table>
<?php 


if($MyPost["ErrorCreateDos"])
{

?>

<tr><td colspan="2"><a style="color: red;font-weight: bolder"><?php  echo $MyPost["ErrorCreateDos"];?></a></td></tr>
<tr><td>&nbsp;</td></tr>

<?php 

}

?>
<tr><td >Nom soci&eacute;t&eacute; :</td><td><input type="text" name="STA_SARL" value="<?php echo $MyPost["STA_SARL"]; ?>"></td></tr>

<tr><td>&nbsp;</td></tr>
<tr>
    <td valign="top"  <?php echo StringHelper::InputInError("CC_NUM",$TabError); ?> >Cabinet</td>
    <td  valign="top">


      <?php


	  $joinR = $User->JoinRequired("comptable");
	  
	  $WhereR = $User->WhereRequired("comptable");
          
          $WhereR .= $User->WhereRequired("cabinet");

          
	  if($_SESSION["agip_AG_NUM"])
	  	$WhereR .= " and (CC_IS_ADMIN = 1)";


          $ValDef =  $MyPost["CC_NUM"];

          $sql = "select CC_NUM,CC_NOM,CAB_NOM from comptable  $joinR where 1 $WhereR order by cabinet.CAB_NOM,comptable.CC_NOM";


	  ?>

	  <select name="CC_NUM[]" type="text" class="gapiarea" multiple="multiple" style="width:100%;height:150px;" >

	  <?php

	   
	  $res = Database::query($sql);

		while($ln = Database::fetchArray($res))
		{

			$select = "";

			if(in_array($ln["CC_NUM"],$ValDef))
			{
				$select = " selected='selected' ";

			}

			echo "<option value='".$ln["CC_NUM"]."' $select>".$ln["CC_NOM"]."</option>";

		}


	  ?>
    </select>




        <!--/gapi_champ_code_comptable-->
        <!--gapi_champ_nom_station-->    </td>
  </tr>

<tr><td>&nbsp;</td></tr>

<tr><td>Date d&eacute;but exercice :</td><td><input type="text" name="DOS_DEBEX" value="<?php echo StringHelper::MySql2DateFr($MyPost["DOS_DEBEX"]); ?>"></td></tr>
<tr><td>Date fin exercice :</td><td><input type="text" name="DOS_FINEX" value="<?php echo StringHelper::MySql2DateFr($MyPost["DOS_FINEX"]); ?>"></td></tr>

<tr><td>&nbsp;</td></tr>
<tr><td align="center" colspan="2"><input type="submit" name="validShortForm" value="Cr&eacute;er le dossier"> </td></tr>

</table>
</form>