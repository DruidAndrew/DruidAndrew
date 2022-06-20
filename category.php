<?php

if(!defined("ENGINE")){
	die("404");
}
//глобальная переменная(id текущей страницы)
$item_id=$GLOBALS['identificator'];
//выбор на основе текущей страницы
$category = $db->query("SELECT * FROM `category2` WHERE `id`='$item_id'", true);
//инициализируем шаблоны первого и второго уровня. один для категорий второй для навигации
$tpl = new Template($config["template"]);
$tpl2 = new Template($config["template"]);
$tpl3 = new Template($config["template"]);

//-----метаописания(являются глобальными переменными для index.php)
$gdescription = str_replace("\"", "&#34;", $category[0]["meta_description"]);
$gkeywords = str_replace("\"", "&#34;", $category[0]["meta_keywords"]);
$gtitle = str_replace("\"", "&#34;", $category[0]["meta_title"]);
//-----/метаописания

//видимые только для пользователя данные
if($user["uid"] !== null) {
	$tpl->set_block("user_only", true);
}else{
	$tpl->set_block("user_only", false);
	$update=json_decode($_COOKIE['_cp_b']);
	$current_url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$update = $update->p->u;
	if($_SESSION['first_visit']==1 && $update!=$current_url){
		add_view($db, $item_id, 'category2');
	}
}
if($category[0]['podborki']!=false){
	$podborki = $db->query("SELECT * FROM `podborki` WHERE `id` in (".$category[0]['podborki'].")", true);
	$tpl->set_block("podborki_container", true);
	$buffer='';
	foreach($podborki as $k=>$v){
		$buffer.="<div class='checkbox_container'><i class='jfont'></i>".$v['name']."</div>";
	}
	$tpl->set("{podborki}", $buffer);
}else{
	$tpl->set_block("podborki_container", false);
	$tpl->set("{podborki}", '');
}

//внутренние данные
$tpl->set("{id}", $category[0]["id"]);
$tpl->set("{title}", $category[0]["title"]);
$tpl->set("{viewes}", $category[0]["viewes"]);

if($category[0]["description"]!=false){
	$tpl->set_block("decript", true);
	$tpl->set("{description}", $category[0]["description"]);
}else{
	$tpl->set("{description}", '');
	$tpl->set_block("decript", false);
}

//Устанавливает блок если существует переменная
//Параметры: (шаблон, массив_со_значением, имя_значения, имя_блока/переменной в которую вставлять)
$tpl->set("{sity}", 'Краснодар');//Город
set_if_exists($tpl, $category['0'], 'communications', 'communications');//коммуникации
set_if_exists($tpl, $category['0'], 'address', 'address');//адрес

//получаем регион и выводим его название
$region=get_region($category['0']['region'], $db);
set_if_exists($tpl, $region, 'title', 'region');//район

//данная функция в поисках потомков и их значений пожирает много ресурсов
if ($category[0]['parant_id'] != 0) {
	$class = find_children_values('class', $db, $item_id, true);//последняя переменная не дает спускаться до поиска на обьекты
	if($class!=false){
		$class = implode(', ', $class);
		$tpl->set_block("not_null_class", true);
		$tpl->set("{this_class}", $class);
	}
	else{
		$tpl->set_block("not_null_class", false);
	}
}
//Срок сдачи
if($category[0]["end_time"]!=false){
	$tmp=explode('-',$category[0]['end_time']);
	$end_time=$tmp[0]."<sup>".$tmp[1]."кв.</sup>";
	$tpl->set_block("not_null_end", true);
	if($category[0]['end_time']=='Сдан'){
		$end_time='Сдан';
	}
}else{
	$end_time='';
	$tpl->set_block("not_null_end", false);
}
$tpl->set("{this_end}", $end_time);

//Отделка(быстрофикс. можно выводить список как для классов.)
set_if_exists($tpl, $category[0], 'otdelka', 'otdelka');
//Конструктив
set_if_exists($tpl, $category[0], 'constructive', 'construct');
//Конструктив(быстрофикс. можно выводить список как для классов.)
set_if_exists($tpl, $category[0], 'floring', 'flor');

//Вкладка: контакты
$category[0]['json_tables'] = str_replace(array("&#34;"), array("\""), $category[0]['json_tables']);

//$json_tables = json_decode('[["\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512"],["\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512","\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u043512"]]');
$json_tables = json_decode($category[0]['json_tables']);

$contacts = "";
if(count($json_tables->contacts) > 0){
	foreach ($json_tables->contacts as $key => $value) {//Контакты

		$tpl2 = new Template($config['template']);
		$tpl2->set("{telephone}", $value[0]);
		$tpl2->set("{fio}", $value[1]);
		$tpl2->set("{adress}", $value[2]);
		$tpl2->set("{dop}", $value[3]);
		$contacts .= $tpl2->load("object_contacts.tpl");
	}
	$tpl->set_block("contact_data", true);
}elseif($json_tables!=false){
	foreach ($json_tables as $key => $value) {//Контакты

		$tpl2 = new Template($config['template']);
		$tpl2->set("{telephone}", $value[0]);
		$tpl2->set("{fio}", $value[1]);
		$tpl2->set("{adress}", $value[2]);
		$tpl2->set("{dop}", $value[3]);
		$contacts .= $tpl2->load("object_contacts.tpl");
	}
	$tpl->set_block("contact_data", true);
}else{
	$tpl->set_block("contact_data", false);
}
$tpl->set("{contacts}", $contacts);
//создает области с фотографиями такие как фото этапов и планов
//id текущего элемента; бд; обьект; шаблон; что взять?; имя папки с фото; куда вставить;
//планировки
Make_Editional_Images_Tab($item_id, $db, $category, $tpl, 'plan_photo', 'plan', 'planirovki');
//этапы строительства
Make_Editional_Images_Tab($item_id, $db, $category, $tpl, 'etap_photo', 'etap', 'etapu');

//документы
$url = Get_Level_Of_Deep($db, $category[0]['parant_id']);
if($category[0]['documents'] != ""){
	$url=$url."/".$category[0]['alias']."/docs";
	$shaxf = $db->query("SELECT * FROM files WHERE id IN(" . $category[0]['documents'] . ")", true);
	$docfiles = "";
	$twos = false;
	foreach ($shaxf as $key => $value) {
		$this_url=$url."/".$value["fname"];
		$tpl2 = new Template($config["template"]);
		if($twos){
			$tpl2->set_block("two", true);
			$twos = false;
		}else{
			$tpl2->set_block("two", false);
			$twos = true;
		}
		$tpl2->set("{title}", $value["title"]);
		$tpl2->set("{file}", $this_url);
		$tpl2->set("{domain}", $config["url"]);
		$docfiles .= $tpl2->load("shaxfiles.tpl");
	}
	$tpl->set("{docfiles}", $docfiles);
	$tpl->set_block("docs", true);
}else{
	$tpl->set("{docfiles}", "");
	$tpl->set_block("docs", false);

}
//может ли пользователь редактировать категорию?
can_edit($db, $category[0], $item_id, $user, $tpl);
//Застройщик
$builder=get_builder($category[0]['builder_id'], $db);
if($builder!=false){
	$tpl->set_block("is_builder", true);
	$tpl->set("{builder_title}", $builder["title"]);
	$tpl->set("{builder_site_url}", $builder["site_url"]);
}else{
	$tpl->set_block("is_builder", false);
}

//-----слайдер--------
$photo = explode(',', $category[0]['main_photo']);
if ($photo[0] != false) {
	//глубина для урла картикон
	$url = Get_Level_Of_Deep($db, $item_id);
	//получить все фотки
	$photo_name = $db->query("SELECT * FROM `files` WHERE `id` in (" .$category[0]['main_photo'] . ") ORDER BY `is_main` DESC", true);
	//формируем слайдер для категории из ее фоток
	$image = '';
	$i = 0;
	foreach ($photo_name as $k => $v) {
		if ($i == 0) {
			$active = 'active';
		} else {
			$active = '';
		}
		$photo_url = "files/" . $url . "/photos/" . $v['fname'];

		$image .= "<img src='/$photo_url' alt='" . $v['alt'] . "' title='" . $v['title'] . "' />";
		$i++;
	}
	$tpl->set("{slider_images}", $image);
} else {
	$photo_url = "<img src='/files/blocker.jpg' alt='Фото обрабатывается' title='Фото обрабатывается' /> ";
	$tpl->set("{slider_images}", $photo_url);
}
//-----/слайдер--------
//скрываем слайдер и прочие вычисления для категорий верхнего уровня
if($category[0]['parant_id']==0){
	$tpl->set_block("top_area", false);
}else{
	$tpl->set_block("top_area", true);
}



//инициализируем класс для работы с категориями и обьектами
$Cat_Former = new Category_Former();
//выбираем все обьекты текущей категории
$sub_objects = $db->query("SELECT * FROM `category2` WHERE `parant_id`='" . $category[0]["id"] . "'  AND `activated`=true", true);

if ($sub_objects[0]['id'] != false) {//если ниже лежат категории

	//создаем навигацию
	$sub_objects = make_navigation($tpl, $sub_objects, $tpl2);
	//узнаем лежат ли ниже литера
	$whosdady=get_granddady_value($db, $sub_objects[0]['parant_id'], 'id');
	if($whosdady==10 && $GLOBALS['identificator']!=10){
		$cat_list = $Cat_Former->list_of_liters($sub_objects, $tpl2, $tpl3, $db, $user, $item_id);
		$price=Get_The_Borders_Prices($item_id, $db, 'MIN');
	}else{
	//создаем карточки категорий
		$cat_list = $Cat_Former->list_of_subcats($sub_objects, $tpl2, $db, $user, $item_id);
	}

} else {//если ниже лежат обьекты
	$sub_objects = $db->query("SELECT * FROM `objects2` WHERE `parant_id`='" . $category[0]["id"] . "' AND `activated`=true", true);
	//создаем навигацию (попутно отрезаем лишнее от массива обьектов)
	$sub_objects = make_navigation($tpl, $sub_objects, $tpl2);
	//создаем карточки обьектов
	$cat_list = $Cat_Former->list_of_objects($sub_objects, $tpl2, $db, $user, $item_id);

	$whosdady=get_granddady_value($db, $item_id, 'id');
	if($whosdady==10){
		$price=Get_The_Borders_Prices($item_id, $db, 'MIN');
	}
}
//получаем пользователя
$agent = $db->query("SELECT * FROM users WHERE id=" . $category[0]['user_id'], true);
$tpl->set("{agent_name}", $agent[0]['last_name'] . " " . $agent[0]['first_name']);
$tpl->set("{agent_dolz}", $agent[0]['dolz']);
$tpl->set("{agent_id}", $agent[0]['id']);
$tpl->set("{agent_photo}", $agent[0]['avatar']);
$tpl->set("{agent_telephone}", $agent[0]['atel']);
$tpl->set("{agent_email}", $agent[0]['email']);
$tpl->set("{agent_facebook}", $agent[0]['facebook']);
$tpl->set("{agent_vk}", $agent[0]['vk']);
$tpl->set("{agent_skype}", $agent[0]['skype']);
$tpl->set("{agent_odnoklassniki}", $agent[0]['odnoklassniki']);
$tpl->set("{agent_descr}", $agent[0]['description']);
//для малого поиска список новостроек
$newbuilds = $db->query("SELECT * FROM `category2` WHERE `parant_id`='10'", true);
$newb_name=new_builds_list($newbuilds);//новостройки с учетом выбранной;
$tpl->set("{newb_options}", $newb_name);
//цена

if($price!=false){
	$price='от '.priceView($price).' &#8381;';
	$tpl->set("{price}", $price);
}else{
	$tpl->set("{price}", 'Распродано');
}


//вставляем полученные обьекты
$tpl->set("{content}", $cat_list);
//для вставки используется основной шаблон(обертка)
$global_content = $tpl->load("categories/category.tpl");


/*echo "<pre style='padding-top: 150px; background-color: white; margin-bottom: -90px'>";
print_r($category);
echo "</pre>";*/
?>