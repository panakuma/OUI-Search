<?php
  try{
    # GETリクエストからMACアドレスをもらう
    $_GET["MAC"] = $MAC;

    # OUIデータベースに接続(SQLite)
    $dbcon = new PDO("sqlite:../oui.db");
    if(!$dbcon){
      throw new Exception('SQLite読み込みエラー');
    }

    # $MACにアドレスが入っていれば、データベースから検索
    $MAC = mb_strlen(preg_replace('/[^0-9a-fA-F]/', '', $MAC));
    if($MAC >= 6){
      $MAC = substr($MAC, 0, 6);
      $stmt = $dbcon -> prepare("select * from `oui` where `mac` like ?;");
      $stmt -> execute([$MAC]);
      $result = $stmt->fetchAll();
      $data[] = array(
        'Vender code' => $MAC,
        'Vender name' => $result['vender']
      );
      header('Content-type: application/json');
      echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }


  }catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
  }

?>