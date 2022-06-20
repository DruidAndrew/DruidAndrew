<?php
if(!defined("ENGINE")){
	die("404");
}


error_reporting(E_ALL | E_STRICT);
require(ENGINE_DIR . "/files_worker/jQ/server/php/UploadHandler.php");
$my_query_string='';
if(isset($_GET['download_type'])){//
	$my_query_string.='?download_type='.$_GET['download_type'];
}
if(isset($_GET['is_edit'])){
	$my_query_string.='&is_edit='.$_GET['is_edit'];
}
if(isset($_GET['edit_id'])){
	$my_query_string.='&edit_id='.$_GET['edit_id'];
}
if(isset($_GET['folder'])){//photos;plan;docs;etap
	$my_query_string.='&folder='.$_GET['folder'];
}
if(isset($_GET['is_document'])){
	$my_query_string.='&is_document='.$_GET['is_document'];
}

//псевдарандомное имя папки
$user_folder_name='tmp_'.$user['uid'];
$upload_url='/tmp_files/';
if(isset($_GET['is_edit'])&&$_GET['is_edit']=='add'){//Создаем новый
	$upload_url='/tmp_files/'.$user_folder_name."/".$_GET['folder']."/";
}
//генерация спец-параметров для работы с базой данных
$special_data_arr=array('user_id'=>$user['uid']);
if(isset($_FILES['files'])){
	$special_data_arr=array_merge($special_data_arr,array('filename'=>$_FILES['files']['name'][0]));
}
if(isset($_GET['edit_id'])){
	$special_data_arr=array_merge($special_data_arr, array('download_item_id'=>$_GET['edit_id'],'is_edit'=>'yes'));
}else{
	$special_data_arr=array_merge($special_data_arr, array('is_edit'=>'no'));
}
if(isset($_GET['folder'])){
	$special_data_arr=array_merge($special_data_arr, array('img_direction'=>$_GET['folder']));
}
if(isset($_GET['download_type'])){
	$special_data_arr=array_merge($special_data_arr, array('download_type'=>$_GET['download_type']));
}


if(isset($_GET['is_edit'])&&$_GET['is_edit']=='edit'){//Редактируем
	switch($_GET['download_type']){
		case 'builders':
			$alias=$db->query("SELECT `alias` FROM `builders` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/uploads/builders/'.$alias."/".$_GET['folder']."/";
			break;
		case 'actions':
			$alias=$db->query("SELECT `alias` FROM `actions` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/uploads/actions/'.$alias."/".$_GET['folder']."/";
			break;
		case 'new_builds':
			$url=Get_Level_Of_Deep($db, $_GET['edit_id']);
			$upload_url='/files/'.$url."/".$_GET['folder']."/";
			break;
		case 'liter':
			$url=Get_Level_Of_Deep($db, $_GET['edit_id']);
			$upload_url='/files/'.$url."/".$_GET['folder']."/";
			break;
		case 'poselki':
			$url=Get_Level_Of_Deep($db, $_GET['edit_id']);
			$upload_url='/files/'.$url."/".$_GET['folder']."/";
			break;
		case 'vtorichka':
			$alias=$db->query("SELECT `alias` FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/files/vtorichka/'.$alias."/".$_GET['folder']."/";
			break;
		case 'land':
			$alias=$db->query("SELECT `alias` FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/files/zemelniy-uchastok/'.$alias."/".$_GET['folder']."/";
			break;
		case 'kommercia':
			$alias=$db->query("SELECT `alias` FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/files/kommercia/'.$alias."/".$_GET['folder']."/";
			break;
		case 'podriadchik':
			$alias=$db->query("SELECT `alias` FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/files/podriadchik/'.$alias."/".$_GET['folder']."/";
			break;
		case 'home_solo':
			$alias=$db->query("SELECT `alias` FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0]['alias'];
			$upload_url='/files/dom/'.$alias."/".$_GET['folder']."/";
			break;
		case 'home_kp':
			$item=$db->query("SELECT * FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0];
			$url=Get_Level_Of_Deep($db, $item['parant_id'])."/".$item['alias'];
			$upload_url='/files/'.$url."/".$_GET['folder']."/";
			break;
		case 'new':
			$item=$db->query("SELECT * FROM `objects2` WHERE `id`='".$_GET['edit_id']."'", true)[0];
			$url=Get_Level_Of_Deep($db, $item['parant_id'])."/".$item['alias'];
			$upload_url='/files/'.$url."/".$_GET['folder']."/";
			break;
	}

}
if (isset($_FILES['files']['name'][0]) && $_FILES['files']['name'][0] != false) {//фикс для кирилических символов
	preg_match_all('/(.+)\.(\w+$)/', $_FILES['files']['name'][0], $out, PREG_PATTERN_ORDER);
	$out[1][0]=str2url($out[1][0]);
	$text=$out[1][0].'.'.$out[2][0];
	$_FILES['files']['name'][0] = $text;
}
$upload_handler = new UploadHandler($upload_url,$my_query_string, $db, $special_data_arr);

die(1);