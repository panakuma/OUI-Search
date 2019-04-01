<?php
  try{
    $oui = file(__DIR__ . '/oui.txt', FILE_SKIP_EMPTY_LINES);
    if(!$oui){
      throw new Exception('ファイル読み込みエラー');
    }
    
    $dbcon = new PDO("sqlite:oui.db");
    if(!$dbcon){
      throw new Exception('SQLite読み込みエラー');
    }
    
    $stmt = $dbcon -> prepare("delete from oui;");
    $stmt -> execute();
    
    foreach($oui as $data){
      $mac = preg_replace('/[^0-9a-fA-F]/', '', substr($data, 0, 8));
      $ven = substr($data, 10);
      echo "MAC address : ", $mac, "\n", "Vender : ", $ven, "\n";
    
      $stmt = $dbcon -> prepare("insert into oui (mac, vender) values (:mac, :vender);");
      $stmt -> bindParam(':mac', $mac, PDO::PARAM_STR);
      $stmt -> bindParam(':vender', $ven, PDO::PARAM_STR);
      $stmt -> execute();
    }
  } catch(Exception $error){
    echo "エラーが発生しました", $e->getMessage(), "\n";
    die();
  }
?>
000000