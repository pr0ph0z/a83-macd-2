<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Azure Blob and Cognitive</title>
    <style>
        /* Fonts */
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:400);

        /* fontawesome */
        @import url(http://weloveiconfonts.com/api/?family=fontawesome);
        [class*="fontawesome-"]:before {
            font-family: 'FontAwesome', sans-serif;
        }

        /* Simple Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* body */
        body {
            background: #e9e9e9;
            color: #5e5e5e;
            font: 400 87.5%/1.5em 'Open Sans', sans-serif;
        }

        /* Form Layout */
        .form-wrapper {
            background: #fafafa;
            margin: 3em auto;
            padding: 0 1em;
            max-width: 500px;
            padding: 10px;
        }
        
        .form-wrapper > img {
            width: 100%;
        }

        .form-wrapper > p {
            text-align: center;
            padding: 9.5px;
            margin: 0 0 10px;
            font-size: 13px;
            word-break: break-all;
            word-wrap: break-word;
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        h1 {
            text-align: center;
            padding: 1em 0;
        }

        form {
            padding: 0 1.5em;
        }

        .form-item {
            margin-bottom: 0.75em;
            width: 100%;
        }

        .form-item input {
            background: #fafafa;
            border: none;
            border-bottom: 2px solid #e9e9e9;
            color: #666;
            font-family: 'Open Sans', sans-serif;
            font-size: 1em;
            height: 50px;
            transition: border-color 0.3s;
            width: 100%;
        }

        .form-item input:focus {
            border-bottom: 2px solid #c0c0c0;
            outline: none;
        }

        .button-panel {
            margin: 2em 0 0;
            width: 100%;
        }

        .button-panel .button {
            background: #f16272;
            border: none;
            color: #fff;
            cursor: pointer;
            height: 50px;
            font-family: 'Open Sans', sans-serif;
            font-size: 1.2em;
            letter-spacing: 0.05em;
            text-align: center;
            text-transform: uppercase;
            transition: background 0.3s ease-in-out;
            width: 100%;
        }

        .button:hover {
            background: #ee3e52;
        }

        .form-footer {
            font-size: 1em;
            padding: 2em 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-wrapper">
        <h1>Azure Blob and Cognitive</h1>
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <div class="form-item">
                <label for="gambar">Pilih gambar</label>
                <input type="file" name="gambar" id="gambar" required>
            </div>
            <div class="button-panel">
                <input type="submit" value="Submit" class="button"/>
            </div>
        </form>
        <div class="form-footer"></div>
    </div>

<?php
require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=rdsdicodingstorage;AccountKey=BWc7WwCpvmU5bFL6PPZaUQX/kUDANOPN6hG1WVghunsCBz+HIXJ3wxX04tUSCsM3AWNBCFUCpdFhhoGwgSo6Kw==";
$status = false;

if (isset($_FILES['gambar'])) {
    $check = getimagesize($_FILES["gambar"]["tmp_name"]);
    if ($check !== false) {
        $filename = md5(date("Y-m-d H:i:s")).".".getExtension($_FILES['gambar']['name']);
        $blobClient = BlobRestProxy::createBlobService($connectionString);
        $blobClient->createBLockBlob("submission", $filename, fopen($_FILES["gambar"]["tmp_name"], "r"));
        $status = true;
        request($filename);
    } else {
        $status = false;
    }
}

function getExtension($filename) {
    return explode(".", $filename)[count(explode(".", $filename))-1];
}

function request($filename) {
    global $url;
    global $caption;
    $url = "https://rdsdicodingstorage.blob.core.windows.net/submission/".$filename;
    $data = '{"url":"'.$url.'"}';
    $headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Ocp-Apim-Subscription-Key: 218a04af8af2441aa8c09f79058110b7";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://southeastasia.api.cognitive.microsoft.com/vision/v1.0/describe?maxCandidates=1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $json = json_decode($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);
    $caption = $json->description->captions[0]->text;
}

if ($status):
?>

<div class="form-wrapper">
    <img src="<?= $url ?>">
    <p><?= $caption ?></p>
</div>
<?php endif; ?>
*kode style form: <a href="https://codepen.io/bowie/pen/erEoh">https://codepen.io/bowie/pen/erEoh</a><br>
</body>
</html>