<?php
  try{
    # GETリクエストからMACアドレスをもらう
    $mac = $_GET["mac"];

    # OUIデータベースに接続(SQLite)
    $dbcon = new PDO("sqlite:../oui.db");
    if(!$dbcon){
      throw new Exception('SQLite読み込みエラー');
    }

    # $MACにアドレスが入っていれば、データベースから検索
    $mac = strtolower(preg_replace('/[^0-9a-fA-F]/', '', $mac));
    if(mb_strlen($mac) >= 6){
      $mac = substr($mac, 0, 6);
      $stmt = $dbcon -> prepare("select * from `oui` where `mac` like ?;");
      $stmt -> execute([$mac]);
      $result = $stmt->fetchAll();
      $data = array(
        'Vender code' => substr($mac, 0, 2) . "-" . substr($mac, 2, 2) . "-" . substr($mac, 4, 2),
        'Vender name' => substr($result[0]['vender'], 0, -2)
      );
      header('Access-Control-Allow-Origin: *');
      header('Content-type: application/json');
      echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
  }catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
  }

?>
