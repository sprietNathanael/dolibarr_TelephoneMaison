<?php

	require 'config.php';
	
	dol_include_once('/societe/class/societe.class.php');
	dol_include_once('/kiwi/class/kiwi.class.php');
	dol_include_once('/core/lib/company.lib.php');
	
	
	$fk_soc = GETPOST('id');
	
	$object=new Societe($db);
	$object->fetch($fk_soc);
	
	$action = GETPOST('action');
	
	$PDOdb = new TPDOdb;

	switch ($action) {
		case 'add':
			
			$fk_kiwi = GETPOST('fk_kiwi');
			if($fk_kiwi>0) {
				
				$kiwi=new TKiwi;
				$kiwi->fk_product = $fk_kiwi;
				$kiwi->fk_soc = $object->id;
				$kiwi->save($PDOdb);
			
				setEventMessage($langs->trans('KiwiAdded'));	
			}
			
			_card($object);
			
			break;
		
		case 'del':
			
			$rowid = GETPOST('rowid');
			if($rowid>0) {
				$kiwi=new TKiwi;
				if($kiwi->load($PDOdb, $rowid)) {
					$kiwi->delete($PDOdb);
					setEventMessage($langs->trans('KiwiDeleted'));	
				}
			
					
			}
			
			_card($object);
			
			break;
		
		default:
		
			_card($object);
			
			break;
	}


function _card(&$object) {
	global $conf,$db,$user,$langs,$form;	

	// défintion entête
	$head = societe_prepare_head($object);
	llxHeader();
	dol_fiche_head($head, 'kiwi', $langs->trans("ThirdParty"),0,'company');
	
	
	// sélection d'un produit dans une catégorie défini dans l'admin
	// on utilise un sélecteur custom car pas de filtre natif sur catégorie pour ce combo dans dolibarr
	
	$PDOdb = new TPDOdb;
	
	$TProduct = TKiwi::getProduct($PDOdb);
	
	$formCore=new TFormCore('auto','form1');
	echo $formCore->hidden('action', 'add');
	echo $formCore->hidden('id', $object->id);
	echo $formCore->combo($langs->trans('KiwiToAdd'), 'fk_kiwi', $TProduct, 0);
	echo $formCore->btsubmit($langs->trans('Add'), 'btadd');
	$formCore->end();
	// affichage de la liste des kiwi associés
	
	$l=new TListviewTBS('lKiwi');
	$sql = "SELECT rowid, fk_product,date_cre, '' as 'Action' FROM ".MAIN_DB_PREFIX."kiwi WHERE fk_soc = ".$object->id;
	
	
	
	echo $l->render($PDOdb, $sql,array(
	
		'title'=>array(
			'fk_product'=>$langs->trans('Product')
			,'date_cre'=>$langs->trans('Date')
		)
		,'link'=>array(
			'Action'=>'<a href="?id='.$object->id.'&action=del&rowid=@rowid@">'.img_delete().'</a>'
		)
		,'hide'=>array('rowid')
		,'type'=>array(
			'date_cre'=>'date'
		)
		,'eval'=>array(
			
			'fk_product'=>'getProductLink(@val@)'
		
		)
		,'search'=>array(
			'date_cre'=>'calendar'
		)
	));
	
	
	/*$kiwi = new TKiwi;
	$kiwi->fk_soc = $object->id;
	$kiwi->fk_product = 1;
	$kiwi->save($PDOdb);
	*/
	// pied de page 
	dol_fiche_end();
	llxFooter();
	
}

function getProductLink($fk_product) {
	global $conf,$user,$db,$langs;
	dol_include_once('/product/class/product.class.php');
				
	$product_static = new Product($db);
	$product_static->fetch($fk_product);
	
	
	return $product_static->getNomUrl(1).' '.$product_static->label;
}
