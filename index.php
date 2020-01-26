<?php
if(empty($_GET)){
	return header("HTTP/1.0 404 Not Found");
}
include 'db_cfg.php';
$conn = new mysqli(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
$conn->set_charset("utf8");

$user_id = $_GET["id"];
$service_id = $_GET["service_id"];
$return_data = array();

//if user is not exist
$query = $conn->query("SELECT * FROM users WHERE ID = $user_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}

//if service is not exist
$query = $conn->query("SELECT * FROM services WHERE ID = $service_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}

//if user has no service
$query = $conn->query("SELECT * FROM services WHERE ID = $service_id AND user_id = $user_id");
$row = $query->fetch_assoc();
if($row == NULL){
	$return_data["result"] = "error";
	return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
}




if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$query = $conn->query("SELECT tarif_id FROM services WHERE user_id = $user_id");
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
			"speed" => $row["speed"]
		);
	}

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
	// while ($row = $result->fetch_assoc()) {
	// 	$return_data["id"] = $row["ID"];
	// 	$return_data["login"] = $row["login"];
	// 	$return_data["name_last"] = $row["name_last"];
	// 	$return_data["name_first"] = $row["name_first"];
	// }
	print json_encode($return_data, JSON_UNESCAPED_UNICODE);

}else if ($_SERVER['REQUEST_METHOD'] == 'PUT'){
	parse_str(file_get_contents("php://input"),$post_vars);
	if(!array_key_exists("tarif_id", $post_vars))
	{
		$return_data["result"] = "error";
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}else{
		$tarif_id = $post_vars["tarif_id"];
	}

	$query = $conn->query("SELECT * FROM services WHERE user_id = $user_id AND tarif_id = $tarif_id");
	$row = $query->fetch_assoc();
	if($row == NULL){
		$return_data["result"] = "error";
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}else{
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
		return print json_encode($return_data, JSON_UNESCAPED_UNICODE);
	}
}
?>
