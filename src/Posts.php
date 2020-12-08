<?php
namespace smet;
use \smet\Post;
use Doctrine\ORM\Query\ResultSetMapping;

class Posts {
    private $entityManager;

    public function __construct($entityManager){
        $this->entityManager = $entityManager;
    }

    public function generateTestPost($getRandomText){
        $month = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $date = \DateTime::createFromFormat('Y-m-d', '2020-'.$month[ rand(0,11) ].'-01');

        return [
            'text' => call_user_func($getRandomText),
            'user_id' => rand (1,10),
            'created' => $date->getTimestamp()
        ];
    }

    public function generateTestPosts($howMuch, $getRandomText){
        for($i =0; $i < $howMuch; $i++) {
            $postValues = $this->generateTestPost(
                $getRandomText
            );

            $post = new Post();
            $post->setCreated($postValues['created'] );
            $post->setText($postValues['text'] );
            $post->setUserId($postValues['user_id']);

            $this->entityManager->persist($post);
            $this->entityManager->flush();
        }
    }

    public function isPostEmpty(){
        $query = $this->entityManager->createQuery("SELECT COUNT(u) FROM smet\\Post u");
        return
            0 == $query->getSingleScalarResult();;
    }
    /**
     * Average character length of posts per month
     **/
    function getAvgLengthPerMonth(){
        $sql = "SELECT
                    strftime('%m', datetime(post.created, 'unixepoch')) as month,
                    ROUND(AVG(LENGTH(post.text)),0) as avg_length
                FROM
                    post  
                GROUP BY
                    strftime('%m', datetime(post.created, 'unixepoch'))";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return
            $stmt->fetchAll();
    }

    public function getLongestPostPerMonth(){
        $sql = "SELECT
                    strftime('%m', datetime(post.created, 'unixepoch')) as month,
                    MAX(LENGTH(post.text)) as max_length
                FROM
                    post  
                GROUP BY
                    strftime('%m', datetime(post.created, 'unixepoch'))";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return
            $stmt->fetchAll();
    }

    //Total posts split by week number
    public function getTotalPostPerWeek(){
        $sql = "SELECT
                    strftime('%W', datetime(post.created, 'unixepoch')) as week,
                    count(post.text) as post_count
                FROM
                    post  
                GROUP BY
                    strftime('%W', datetime(post.created, 'unixepoch'))";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return
            $stmt->fetchAll();
    }
    //Average number of posts per user per month.
    public function getAvgPostPerUserPerMonth(){
        $sql = "SELECT
                    strftime('%m', datetime(post.created, 'unixepoch')) as month,
                    post.user_id as user_id,
                    count(post.text) as post_count
                FROM
                    post  
                GROUP BY
                    post.user_id,
                    strftime('%m', datetime(post.created, 'unixepoch'))";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return
            $stmt->fetchAll();
    }

    public function getPosts($page, $limit){
        $sql = "SELECT
                    p.*
                FROM
                    post p
                LIMIT ? OFFSET ?";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $limit);
        $stmt->bindValue(2, ($page-1) * $limit );

        $stmt->execute();
        return
            $stmt->fetchAll();
    }
}