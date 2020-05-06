<?php
require_once("./Models/AccessDB.php");
require_once("./Models/Video.php");
require_once("./Models/Comment.php");

class C_Video {
    // Private ----------------------------------------------------------------
    /* GetBdd: Return a connection to a database
     *      Output:
     *          - AccessDB object: connected database
     */
    private static function GetBdd() {
        $bdd = new AccessDB();
        $bdd->connect();
        return $bdd;
    }
    /* GenerateVideos: Create a list of Video objects from a list
     *      Input:
     *          - $videos: list of videos to transform into Video objects
     *      Output:
     *          - array: list of Video objects
     */
    public static function GenerateVideos($videos) {
        $list = [];
        for ($i=0; $i < count($videos); $i++) {
            $v = $videos[$i];
            $views = C_Video::GetViews($v["Id"]);
            $list[] = new Video(
                $v["Id"],
                $v["OwnerId"],
                $v["ThemeId"],
                $v["Name"],
                $v["Description"],
                $v["Publication"],
                $v["Price"],
                $views,
                $v["Url"],
                $v["Thumbnail"]
            );
        }
        return $list;
    }
    /* GenerateComments: Create a list of Comment objects from a list
     *      Input:
     *          - $comments: list of comments to transform into Comment objects
     *      Output:
     *          - array: list of Comment objects
     */
    public static function GenerateComments($comments) {
        $list = [];
        for ($i=0; $i < count($comments); $i++) {
            $c = $comments[$i];
            $list[] = new Comment(
                $c["Id"],
                $c["VideoId"],
                $c["UserId"],
                $c["Content"],
                $c["Date"]
            );
        }
        return $list;
    }

    // Public -----------------------------------------------------------------
    /* GetVideos: Get all videos from database
     *      Output:
     *          - array: list of Video objects
     */
    public static function GetVideos() {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM Video", []);
        return C_Video::GenerateVideos($videos);
    }
    /* GetVideosByThemeId: Get videos that match the theme from database
     *      Input:
     *          - $id: Theme id
     *      Output:
     *          - array: list of Video objects
     */
    public static function GetVideosByThemeId($id) {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM Video WHERE ThemeId = :id", ["id" => $id]);
        return C_Video::GenerateVideos($videos);
    }
    /* GetVideoById: Get video that match the id
     *      Input:
     *          - $id: Video id
     *      Output:
     *          - Video: Video objects
     */
    public static function GetVideoById($id) {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM Video WHERE Id = :id", ["id" => $id]);
        return C_Video::GenerateVideos($videos)[0];
    }
    /* GetComments: Get video's comments
     *      Input:
     *          - $id: Video id
     *      Output:
     *          - array: array of Comment objects
     */
    public static function GetComments($id) {
        $bdd = C_Video::GetBdd();
        $comments = $bdd->select("SELECT * FROM Comment WHERE VideoId = :id ORDER BY Id DESC", ["id" => $id]);
        return C_Video::GenerateComments($comments);
    }
    /* GetLikes: Get video's likes number
     *      Input:
     *          - $id: Video id
     *      Output:
     *          - int: Number of likes fot the video
     */
    public static function GetLikes($id) {
        $bdd = C_Video::GetBdd();
        return count($bdd->select("SELECT * FROM UserLike WHERE VideoId = :id", ["id" => $id]));
    }
    /* GetViews: Get video's views number
     *      Input:
     *          - $id: Video id
     *      Output:
     *          - int: Number of views fot the video
     */
    public static function GetViews($id) {
        $bdd = C_Video::GetBdd();
        return count($bdd->select("SELECT * FROM See WHERE VideoId = :id", ["id" => $id]));
    }
    /* GetRelatedVideos: Get an array of Video related to the one given
     *      Input:
     *          - $vid: Video object
     *      Output:
     *          - array: array of Video objects
     */
    public static function GetRelatedVideos($vid) {
        $bdd = C_Video::GetBdd();
        // First we search for the 3 latest videos uploaded by same owner
        $req2 = "SELECT *
                FROM Video
                WHERE OwnerId = :idOwner
                AND Id != :idVideo
                ORDER BY Publication DESC
                LIMIT 3";
        $latest = $bdd->select($req2, [
            "idOwner" => $vid->getOwnerId(),
            "idVideo" => $vid->getId()
        ]);

        // Then we look for similar theme videos
        $req = "SELECT *
                FROM Video
                WHERE ThemeId = :idTheme
                AND OwnerId != :idOwner
                LIMIT 10";
        $videos = $bdd->select($req, [
            "idTheme" => $vid->getThemeId(),
            "idOwner" => $vid->getOwnerId()
        ]);
        return C_Video::GenerateVideos(array_merge($latest, $videos));
    }
    /* GetLatestVideosByUserId: Get an array of the user's latest videos
     *      Input:
     *          - $id: user id
     *      Output:
     *          - array: array of Video objects
     */
    public static function GetLatestVideosByUserId($id) {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM Video WHERE OwnerId = $id ORDER BY Publication DESC LIMIT 5", []);
        return C_Video::GenerateVideos($videos);
    }
    /* GetMostViewedVideosByUserId: Get an array of the user's most viewed videos
     *      Input:
     *          - $id: user id
     *      Output:
     *          - array: array of Video objects
     */
    public static function GetMostViewedVideosByUserId($id) {
        $bdd = C_Video::GetBdd();

        // Select user videos
        $videos = $bdd->select("SELECT * FROM Video WHERE OwnerId = $id ORDER BY Id", []);

        // Count how much views each video has
        $list = [];
        for ($i=0; $i < count($videos); $i++) {
            $list[strval($videos[$i]['Id'])] = $bdd->select("SELECT COUNT(*) FROM See WHERE VideoId = :id", ["id" => $videos[$i]['Id']])[0][0];
        }

        // Sort by views
        uasort($list, function($a, $b) {
            return $a < $b;
        });

        // Take the x most viewed videos
        $final = [];
        $nb = (count($videos) > 7 ? 7 : count($videos));
        for ($i=0; $i < $nb; $i++) {
            for ($j=0; $j < count($videos); $j++) {
                if ($videos[$j]["Id"] == array_keys($list)[$i]) {
                    $final[] = $videos[$j];
                }
            }
        }

        // return list
        return C_Video::GenerateVideos($final);
    }
    /* GetVideosByUserId: Get an array of every videos from a user
     *      Input:
     *          - $id: user id
     *      Output:
     *          - array: array of Video objects
     */
    public static function GetVideosByUserId($id) {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM Video WHERE OwnerId = $id ORDER BY Publication DESC", []);
        return C_Video::GenerateVideos($videos);
    }
    public static function GetVideosByThemes($list) {
        $bdd = C_Video::GetBdd();
        $res = [];
        for ($i=0; $i < count($list); $i++) {
            $vids = $bdd->select("SELECT * FROM Video WHERE ThemeId = :id ORDER BY Publication DESC", ["id" => $list[$i]->getId()]);
            $res = array_merge($res, C_Video::GenerateVideos($vids));
        }
        return $res;
    }
    public static function GetVideosByName($name) {
        $bdd = C_Video::GetBdd();
        $videos = $bdd->select("SELECT * FROM VIDEO WHERE LOWER(Name) LIKE LOWER('%$name%')", []);
        return C_Video::GenerateVideos($videos);
    }
}
?>
