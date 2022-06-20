<?php

if(!defined("ENGINE")){
	die("404 not found");
}

$logout = false;

/*if(isset($_GET['do']) && $_GET['do'] == "logout"){//Выход пользователя
	$logout = true;
	session_destroy();
	$this_place="location:".$_SERVER['HTTP_REFERER'];
	header($this_place);
}*/

if(isset($_GET["auth_to_back"], $_SESSION["old_uid"], $_SESSION["old_token"])){
	$_SESSION["uid"] = $_SESSION["old_uid"];
	$_SESSION["token"] = $_SESSION["old_token"];

	unset($_SESSION["old_token"]);
	unset($_SESSION["old_uid"]);
	header('Location:/');
}


$user = array(
	"auth" => 0,
	"token" => null,
	"uid" => null,
	"group" => 0,
	"invisible" => 0
);

if(isset($_SESSION['uid']) && isset($_SESSION['token']) && !$logout){
	$res = $db->query("SELECT * FROM sessions s, users u WHERE s.token='" . $_SESSION['token'] . "' AND s.uid = u.id AND s.uid='" . $_SESSION['uid'] . "'", true);

	if(count($res) > 0){
		$user = array(
			"auth" => 1,
			"first_name" => $res[0]["first_name"],
			"last_name" => $res[0]["last_name"],
			"dolz" => $res[0]["dolz"],
			"agenstvo" => $res[0]["uagenstvo"],
			"avatar" => $res[0]["avatar"],
			"token" => $_SESSION['token'],
			"uid" => $_SESSION['uid'],
			"group" => $res[0]["ugroup"],
			"invisible" => $res[0]["invisible"]
		);
	}
}

if(isset($_GET["auth_as_user_id"]) && $user["group"] == 4){//Суперадмин может зайти под любым пользователем.
	$res = $db->query("SELECT * FROM users WHERE id=".(int)$_GET["auth_as_user_id"], true);

	if(count($res) > 0){
		$_SESSION["old_uid"] = $user["uid"];
		$_SESSION["old_token"] = $_SESSION['token'];
		$token = md5(date("d.m.Y H:i:s") . ":" . rand(0, 200000000));
		$uid = $res[0]["id"];
		$db->query("INSERT INTO sessions(uid, token) VALUES('$uid', '$token')");
		$_SESSION["uid"] = $uid;
		$_SESSION["token"] = $token;
		header('Location:/');
	}
}

$apages = array(//Список всех странц сайта
	"main" => "/",
);


?>