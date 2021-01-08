<?php
$oui_data_url = "https://macaddress.io/database/macaddress.io-db.json";


try {
    echo("macaddress.ioからデータを取得します。\n");
    $curl_session = curl_init($oui_data_url);
    curl_setopt_array($curl_session, [
        CURLOPT_FAILONERROR => true,
        CURLOPT_RETURNTRANSFER => true
    ]);
    $curl_error = curl_error($curl_session);
    $curl_errno = curl_errno($curl_session);
    $oui_json_data = curl_exec($curl_session);
    if (CURLE_OK !== $curl_errno) throw new Exception('ダウンロードエラー');
    curl_close($curl_session);
    echo("ダウンロードが完了しました。\n");
    $oui_json_array = explode(
        "\n",
        str_replace(
            array("\r\n", "\r", "\n"),
            "\n",
            $oui_json_data
        )
    );

    echo("SQLiteデータベースを初期化します。\n");
    $dbcon = new PDO("sqlite:oui_new.db");
    if (!$dbcon) throw new Exception('SQLite読み込みエラー');
    $stmt = $dbcon->prepare("select count(*) from sqlite_master where type = 'table' and name = 'oui';");
    $stmt->execute();
    $res = $stmt->fetch();
    if ($res[0] != "0") {
        $stmt = $dbcon->prepare("drop table oui;");
        $stmt->execute();
        echo("データベースをパージしました。\n");
    }

    $stmt = $dbcon->prepare("create table oui(
        oui text,
        isPrivate integer,
        companyName text,
        companyAddress text,
        countryCode text,
        assignmentBlockSize text,
        dateCreated text,
        dateUpdated text
    )");
    $stmt->execute();
    echo("データベースを初期化しました。\n");
    echo("データを挿入します。この作業にはしばらく時間がかかります。\n");

    foreach ($oui_json_array as $data) {
        if ($data == NULL) continue;
        $raw_data = json_decode($data, true);
        $stmt = $dbcon->
        prepare("insert into oui (
                oui,
                isPrivate,
                companyName,
                companyAddress,
                countryCode,
                assignmentBlockSize,
                dateCreated,
                dateUpdated
            ) values (
                :oui,
                :isPrivate,
                :companyName,
                :companyAddress,
                :countryCode,
                :assignmentBlockSize,
                :dateCreated,
                :dateUpdated
            );
        ");
        if ($raw_data['isPrivate'] == true) $isPrivate = 1; else $isPrivate = 0;
        $stmt->bindParam('oui', $raw_data['oui'], SQLITE3_TEXT);
        $stmt->bindParam('isPrivate', $isPrivate, SQLITE3_INTEGER);
        $stmt->bindParam('companyName', $raw_data['companyName'], SQLITE3_TEXT);
        $stmt->bindParam('companyAddress', $raw_data['companyAddress'], SQLITE3_TEXT);
        $stmt->bindParam('countryCode', $raw_data['countryCode'], SQLITE3_TEXT);
        $stmt->bindParam('assignmentBlockSize', $raw_data['assignmentBlockSize'], SQLITE3_TEXT);
        $stmt->bindParam('dateCreated', $raw_data['dateCreated'], SQLITE3_TEXT);
        $stmt->bindParam('dateUpdated', $raw_data['dateUpdated'], SQLITE3_TEXT);
        $stmt->execute();
    }
} catch (Exception $error) {
    echo "エラーが発生しました", $error->getMessage(), "\n";
    die();
}
echo("完了しました。\n");

?>
