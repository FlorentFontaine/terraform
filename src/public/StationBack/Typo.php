<?php
session_start();

require_once("../dbClasses/User.php");
require_once("../dbClasses/station.php");

if($Section == "Garde")
{
	$ligneStations = station::GetStation($_SESSION["station_STA_NUM"]);

if($ligneStations['STA_TYPO1'])
			  echo $ligneStations['STA_TYPO1']." - ";
			  if($ligneStations['STA_TYPO2'])
			  echo $ligneStations['STA_TYPO2']." - ";
			  if($ligneStations['STA_BARBUF'] == "oui")
			  echo "Bar Buffet - ";
			  if($ligneStations['STA_LAVP'] == "oui")
			  echo "Lavage Portique - "; 
			  if($ligneStations['STA_LAVHP'] == "oui")
			  echo "Lavage HP - "; 
			   if($ligneStations['STA_BAIE'] == "oui")
			  echo "Baie active"; 
			  
	
}else{
?>

<table  border="1" class="" style="margin-left:10px;text-align:left;width:600px">
  <tr class="EnteteTab">
    <td colspan="6">Cat&eacute;gorie de la station </td>
    </tr>
  <tr class="EnteteTab">
    <td width="100" >Typologie 1 </td>
    <td width="100" >Typologie 2 </td>
    <td width="100" >BAR<br/>
    Buffet</td>
    <td width="100" >Lavage<br/>
    Portique</td>
    <td width="100" >Lavage<br/>
    HP</td>
    <td width="100" >Baie<br/>
    active</td>
  </tr>
  <tr>
    <td>
	<label><input name="STA_TYPO1" type="radio" value="ATR" 
	
	<?php 
	
	if($ligneStations['STA_TYPO1'] == "ATR")
		echo " checked='checked' ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>
	
	/>
      &nbsp;ATR</label></td>
    <td><label><input name="STA_TYPO2" type="radio" value="ATR1" 
	<?php 
	
	if($ligneStations['STA_TYPO2'] == "ATR1")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;ATR1 </label></td>
    <td><label><input name="STA_BARBUF" type="radio" value="oui" <?php 
	
	if($ligneStations['STA_BARBUF'] == "oui")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>      
      &nbsp;oui </label></td>
    <td><label><input name="STA_LAVP" type="radio" value="oui" <?php 
	
	if($ligneStations['STA_LAVP'] == "oui")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;oui </label></td>
    <td><label><input name="STA_LAVHP" type="radio" value="oui" <?php 
	
	if($ligneStations['STA_LAVHP'] == "oui")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;oui </label></td>
    <td><label><input name="STA_BAIE" type="radio" value="oui" <?php 
	
	if($ligneStations['STA_BAIE'] == "oui")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;oui </label></td>
  </tr>
  <tr>
    <td><label><input name="STA_TYPO1" type="radio" value="CODO"  <?php 
	
	if($ligneStations['STA_TYPO1'] == "CODO")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;CODO </label></td>
    <td><label><input name="STA_TYPO2" type="radio" value="ATR2" <?php 
	
	if($ligneStations['STA_TYPO2'] == "ATR2")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;ATR2 </label></td>
    <td><label><input name="STA_BARBUF" type="radio" value="non" <?php 
	
	if($ligneStations['STA_BARBUF'] == "non")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;non </label></td>
    <td><label><input name="STA_LAVP" type="radio" value="non" <?php 
	
	if($ligneStations['STA_LAVP'] == "non")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;non </label></td>
    <td><label><input name="STA_LAVHP" type="radio" value="non" <?php 
	
	if($ligneStations['STA_LAVHP'] == "non")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;non </label></td>
    <td><label><input name="STA_BAIE" type="radio" value="non" <?php 
	
	if($ligneStations['STA_BAIE'] == "non")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;non </label></td>
  </tr>
  <tr>
    <td><label><input name="STA_TYPO1" type="radio" value="Autre" <?php 
	
	if($ligneStations['STA_TYPO1'] == "Autre")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;Autre </label></td>
    <td><label><input name="STA_TYPO2" type="radio" value="Voie Exp." <?php 
	
	if($ligneStations['STA_TYPO2'] == "Voie Exp.")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;Voie Exp. </label></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><label><input name="STA_TYPO2" type="radio" value="Ville" <?php 
	
	if($ligneStations['STA_TYPO2'] == "Ville")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;Ville </label></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><label><input name="STA_TYPO2" type="radio" value="C'STRORE" <?php 
	
	if($ligneStations['STA_TYPO2'] == "C'STRORE")
		echo " checked='checked'  ";
		
	echo $_SESSION["User"]->getAut($Section,false,"radio");

	?>/>
  &nbsp;C'STRORE </label></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<?php } ?>