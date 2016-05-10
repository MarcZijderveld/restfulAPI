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

    $string = explode("/", $currentUrl);
    $id = $string[count($string)-1];

    switch($requestMethod)
    {
        case "GET":

            if ($accept == "application/json")
            {
                header("Content-Type: application/json");

                $json = json_encode(getSingleData($id));

                if($json === "null")
                {
                    header("HTTP/1.1 404 Not Found.");
                }
                else
                    echo $json;
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
            else {
                header("HTTP/1.1 415 Unsupported Media Type");
            }
            break;

        case "PUT":
            $content = $_SERVER["CONTENT_TYPE"];

            if ($content == "application/json")
            {
                $body = file_get_contents("php://input");
                $json = json_decode($body);

                if(!isset($json->name) || $json->name == "" || !isset($json->genre) || $json->genre == "" || !isset($json->publisher) || $json->publisher == "" || !isset($json->publish_date) || $json->publish_date == "")
                {
                    header("HTTP/1.1 400 Bad Request");
                }
                else
                {
                    $query = "UPDATE 2016_p2_restful_games SET name='$json->name', genre='$json->genre', publisher='$json->publisher', publish_date='$json->publish_date' WHERE id=".$id;

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

        case "DELETE":

            // sql to delete a record
            $sql = "DELETE FROM 2016_p2_restful_games WHERE id=".$id;

            if ($conn->query($sql) === TRUE)
            {
                header("HTTP/1.1 204 No Content");
            }
            else
            {
                header("HTTP/1.1 400 Bad Request");
                echo "Error deleting record: " . $conn->error;
            }

            break;
        case "OPTIONS":
            header("Allow: GET, DELETE, PUT,OPTIONS");
            break;
        default:
            header("HTTP/1.1 405 Method Not Allowed");
            break;
    }
?>