<?php
if(empty($_GET)){
	return header("HTTP/1.0 404 Not Found");
}
//Подключаем конфиг
include 'db_cfg.php';
//Создаем подключение к бд и устанавливаем кодировку
$conn = new mysqli(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
$conn->set_charset("utf8");

//Из GET ассива получаем user_id и service_id
$user_id = $_GET["id"];
$service_id = $_GET["service_id"];

$return_data = array();

//Если пользователя с таким id не существует, возвращаем {"result" : "error"}
$query = $conn->query("SELECT * FROM users WHERE ID = $user_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}

//Если сервиса с таким id не существует, возвращаем {"result" : "error"}
$query = $conn->query("SELECT * FROM services WHERE ID = $service_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}

//Если у пользователя нет сервиса, возвращаем {"result" : "error"}
$query = $conn->query("SELECT * FROM services WHERE ID = $service_id AND user_id = $user_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}



//Если запрос типа GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	//Записываем в массив user_tarifs_id все id сервисов у пользователя
	$query = $conn->query("SELECT tarif_id FROM services WHERE user_id = $user_id");
	$return_data["result"] = "ok";
	$user_tarifs_id = array();
	while ($row = $query->fetch_assoc()) {
		$user_tarifs_id[] = $row["tarif_id"];
	}
	
	//Записываем в массив return_data["tarifs"] данные по тарифам пользователя
	foreach ($user_tarifs_id as $user_tarif_id) {
		$query = $conn->query("SELECT * FROM tarifs WHERE id = $user_tarif_id");
		$row = $query->fetch_assoc();
		$tarif_group = $row["tarif_group_id"];
		$return_data["tarifs"] = array(
			"title" => $row["title"],
			"link" => $row["link"],
			"speed" => $row["speed"]
		);
	}

	//Записываем в массив return_data["tarifs"]["tarifs"] остальные тарифы которые имеют тот-же tarif_group_id
	$query = $conn->query("SELECT * FROM tarifs WHERE tarif_group_id = $tarif_group");
	while ($row = $query->fetch_assoc()) {
		$return_data["tarifs"]["tarifs"][] = array(
			"ID" => $row["ID"],
			"title" => $row["title"],
			"price" => (int) $row["price"],
			"pay_period" => $row["pay_period"],
			"new_payday" => strtotime(" +2 months") . date('O'),
			"speed" => $row["speed"]
		);
	}

	//Возвращаем массив return_data в формате JSON
	print json_encode($return_data, JSON_UNESCAPED_UNICODE);

}
//Если тип запроса PUT
else if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
	//Парсим данные из PUT
	parse_str(file_get_contents("php://input"),$post_vars);
	//Если в полученых данных нет tarif_id, то отправляем {"result" : "error"}
	if(!array_key_exists("tarif_id", $post_vars))
	{
		$return_data["result"] = "error";
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}else{
		if($post_vars["tarif_id"] == NULL){
			$return_data["result"] = "error";
			return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
		}
		$tarif_id = $post_vars["tarif_id"];
	}

	//Если не существуем тарифа с tarif_id, то отправляем {"result" : "error"}
	$query = $conn->query("SELECT * FROM services WHERE user_id = $user_id AND tarif_id = $tarif_id");
	$row = $query->fetch_assoc();
	if($row == NULL){
		$return_data["result"] = "error";
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}else{
		//Если тариф с tarif_id, записываем в массив return_data данные по тарифу из бд
		$query = $conn->query("SELECT tarif_id FROM services WHERE user_id = $user_id");
		//$result->data_seek(0);
		$return_data["result"] = "ok";
		$user_tarifs_id = array();
		while ($row = $query->fetch_assoc()) {
			$user_tarifs_id[] = $row["tarif_id"];
		}
	
		foreach ($user_tarifs_id as $user_tarif_id) {
			$query = $conn->query("SELECT * FROM tarifs WHERE id = $user_tarif_id");
			$row = $query->fetch_assoc();
			$tarif_group = $row["tarif_group_id"];
			$return_data["tarifs"] = array(
				"title" => $row["title"],
				"link" => $row["link"],
				"pay_period" => $row["pay_period"],
				"new_payday" => strtotime(" +2 months") . date('O'),
				"speed" => $row["speed"]
			);
		}
		//Отправляем массив return_data в формате JSON
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}
}
?>
