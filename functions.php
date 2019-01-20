<?php
/**
 * Created by PhpStorm.
 * User: игорь
 * Date: 20.01.2019
 * Time: 17:03
 */

/**
 * @param string $question
 * @return false|string
 */
function getVideo($question = "anime")
{
    if (empty($question)) {
        $question = "anime";
    }
    static $page = 1;
    static $total = 0;
    static $oldquestion = 'anime';
    if ($total == 0 || $oldquestion != $question) {
        $response = file_get_contents("https://coub.com/api/v2/search/coubs?q=" . urlencode($question) . "&order_by=newest_popular&page=" . $page . "&per_page=1");
        $total = json_decode($response)->total_pages;
        if ($total == 0) {
            return json_encode(array("command" => "error_question"));
        }
        $oldquestion = $question;
        return getVideo($question);
    } else {
        $response = file_get_contents("https://coub.com/api/v2/search/coubs?q=" . urlencode($question) . "&order_by=newest_popular&page=" . rand(1, $total) . "&per_page=1");
        $total = json_decode($response)->total_pages;
    }
    $page++;
    return json_encode(array("command" => "ResponseVideo", "data" => json_decode($response)->coubs[0]));
}
