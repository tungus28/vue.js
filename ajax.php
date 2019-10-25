<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $DB;

function deleteUrlicoSpacesAndQuotes($str) {
		$str = str_replace(" ", "", $str);
		$str = str_replace("'", "", $str);
		$str = str_replace('"', "", $str);
		$str = str_replace('«', "", $str);
		$str = str_replace('»', "", $str);		
		$str = str_replace('&quot;', "", $str);		
		return $str;
	}
    
$_POST = json_decode(file_get_contents('php://input'), true);

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['type'] == 'change_max_workers') {
    
   
    
    $urlico = deleteUrlicoSpacesAndQuotes($_POST['urlico']);
    
    $max_workers_cnt = (int)($_POST['max_workers_cnt']);
    
    //echo json_encode(['urlico' => $urlico]);
    
    
    
    $strSql = "replace into urlico_max_workers 
                set
                urlico = '".$DB->ForSql($urlico)."',
                max_workers = '".$max_workers_cnt."',
                author_id = '".CUser::GetID()."'
                ";
	
	//die($strSql);
			   
	$DB->Query($strSql, false, $err_mess.__LINE__);
	
	
    
    die('ok');
}