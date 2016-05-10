<?php
    require("database.php");
    require("get.php");
    require("xml.php");

    $requestMethod = $_SERVER["REQUEST_METHOD"];
    $currentUrl = $_SERVER['REQUEST_URI'];
    $accept = $_SERVER["HTTP_ACCEPT"];
    $limit = 0;
    $start = 0;

    if (isset($_GET["limit"]))
    {
        $limit = $_GET["limit"];
    }

    if (isset($_GET["start"]))
        $start = $_GET["start"];

    $collection = [];
    $data = [];
    $pagination = [];

    switch($requestMethod)
    {
        case "GET":

            if ($accept == "application/json")
            {
                header("Content-Type: application/json");

                echo json_encode(getAllData());
            }
            else if ($accept == "application/xml")
            {
                header("Content-Type: application/xml");

            // create new instance of simplexml
            $xml = new SimpleXMLElement('<root/>');

            // function callback
            array2XML($xml, getAllData());

            // save as xml file
            echo $xml->asXML();

            }
            else
                header("HTTP/1.1 415 Unsupported Media Type");
            break;

        case "POST":
            $content = $_SERVER["CONTENT_TYPE"];

            if ($content == "application/json")
            {
                $body = file_get_contents("php://input");
                $json = json_decode($body);

                if(!isset($json->name) || !isset($json->genre) || !isset($json->publisher) || !isset($json->publish_date))
                {
                    header("HTTP/1.1 400 Bad Request");
                }
                else
                {
                    $query = "INSERT INTO 2016_p2_restful_games (name, genre, publisher, publish_date) VALUES ('$json->name', '$json->genre', '$json->publisher', '$json->publish_date')";

                    if($conn->query($query) === TRUE)
                    {
                        http_response_code(201);
                        header("HTTP/1.1 201 Created");
                    }
                    else
                    {
                        echo $conn->error;
                        header("HTTP/1.1 400 Bad Request");
                    }
                }
            }
            else if ($content == "application/x-www-form-urlencoded")
            {
                if(!isset($_POST["name"]) || !isset($_POST["genre"]) || !isset($_POST["publisher"]) || !isset($_POST["publish_date"]))
                {
                    header("HTTP/1.1 400 Bad Request");
                }
                else
                {
                    $query = "INSERT INTO 2016_p2_restful_games (name, genre, publisher, publish_date) VALUES ('".$_POST["name"]."', '".$_POST["genre"]."', '".$_POST["publisher"]."', '".$_POST["publish_date"]."')";

                    if($conn->query($query) === TRUE)
                    {
                        http_response_code(201);
                        header("HTTP/1.1 201 Created");
                    }
                    else
                    {
                        echo $conn->error;
                        header("HTTP/1.1 400 Bad Request");
                    }
                }
            }
            else
            {
                Echo "unsupported";
                header("HTTP/1.1 415 Unsupported Content Type");
            }
            break;

        case "OPTIONS":
            header("Allow: GET,POST,OPTIONS");
            break;
        default:
            header("HTTP/1.1 405 Method Not Allowed");
            break;
    }
?>