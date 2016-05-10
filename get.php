<?php
    function getAllData()
    {
        global $data, $collection, $pagination, $limit, $start, $conn, $currentUrl;

        if ($limit <= 0)
            $query = "SELECT * FROM 2016_p2_restful_games LIMIT 18446744073709551615 OFFSET $start";
        else
            $query = "SELECT * FROM 2016_p2_restful_games LIMIT $limit OFFSET $start";

        $result = $conn->query($query);

        $parsed = explode("?", $currentUrl, 2);
        $parsedUrl = "https://" . $_SERVER["HTTP_HOST"] . $parsed[0];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $item = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "genre" => $row["genre"],
                    "publish_date" => $row["publish_date"],
                    "links" =>
                        [
                            [
                                "rel" => "self", "href" => $parsedUrl .$row["id"]
                            ],
                            [
                                "rel" => "collection", "href" => $parsedUrl
                            ]
                        ]
                ];
                array_push($data, $item);
            }
        }

        $links = [
            ["rel" => "self", "href" => $parsedUrl]
        ];

        $totalItems = mysqli_num_rows($conn->query("SELECT * FROM `2016_p2_restful_games`"));
        $currentItems = $result->num_rows;

        if ($limit > 0) {
            $totalPages = ceil($totalItems / $limit);
        } else
            $totalPages = 1;

        if ($limit > 0)
            $currentPage = ceil(($start + 1) / ($limit <= 0 ? 1 : $limit));
        else
            $currentPage = 1;

        $pagination = [
            "currentPage" => $currentPage,
            "currentItems" => $currentItems,
            "totalPages" => $totalPages,
            "totalItems" => $totalItems,
            "links" => [
                [
                    "rel" => "first",
                    "page" => "1",
                    "href" => $parsedUrl . "?limit=$limit" . "&start=1"
                ],
                [
                    "rel" => "last",
                    "page" => $totalPages,
                    "href" => $parsedUrl . "?limit=$limit" . "&start=" . ($totalItems - ($totalItems % ($limit <= 0 ? 1 : $limit)))
                ],
                [
                    "rel" => "previous",
                    "page" => $totalPages <= 1 ? 1 : $currentPage - 1,
                    "href" => $parsedUrl . "?limit=$limit" . "&start=" . (($start - $limit) <= 0 ? 0 : ($start - $limit))
                ],
                [
                    "rel" => "next",
                    "page" => $currentPage = $totalPages ? $currentPage + 1 : $totalPages,
                    "href" => $parsedUrl . "?limit=$limit" . "&start=" . (($start + $limit) >= $totalItems ? $totalItems : ($start + $limit))
                ],
            ]
        ];

        $collection["items"] = $data;
        $collection["links"] = $links;
        $collection["pagination"] = $pagination;

        return $collection;
    }

    function getSingleData($id)
    {
        global $conn, $currentUrl;

        $query = "SELECT * FROM 2016_p2_restful_games WHERE id= ".$id;

        $parsed = explode("?", $currentUrl, 2);
        $parsedUrl = "https://" . $_SERVER["HTTP_HOST"] . $parsed[0];

        $tokens = explode('/', $parsedUrl);      // split string on :
        array_pop($tokens);                   // get rid of last element
        $newString = implode('/', $tokens);   // wrap back

        if ($result = $conn->query($query)) {

            /* fetch associative array */
            while ($row = $result->fetch_assoc()) {

                $item = [
                    "id" => $row["id"],
                    "name" => $row["name"],
                    "genre" => $row["genre"],
                    "publisher" => $row["publisher"],
                    "publish_date" => $row["publish_date"],
                    "links" =>
                        [
                            [
                                "rel" => "self", "href" => $parsedUrl
                            ],
                            [
                                "rel" => "collection", "href" => $newString
                            ]
                        ]
                ];
                return $item;
            }
        }
        else
            echo "invalid Id";
    }
?>