<?php
include('./vendor/autoload.php');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use smet\Tokens;
use smet\Posts;
use smet\Post;
use smet\Assignment;
$config = Setup::createAnnotationMetadataConfiguration(
    array(__DIR__."/src"),
    $isDevMode = true,
    $proxyDir = null,
    null,
    $useSimpleAnnotationReader = false
);

$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

$api = new \smet\Api();
$api->addUrl('/assignment/register','POST',function ($postParams){
    global $entityManager;
    (new Assignment($entityManager))->register( $postParams );
});

$api->addUrl('/assignment/stats','POST,GET',function ($postParams){
    global $entityManager;
    (new Assignment($entityManager))->stats( $postParams );
});

$api->addUrl('/assignment/posts','GET',function ($postParams){
    global $entityManager;
    (new Assignment($entityManager))->posts( $postParams );
});



$posts = new smet\Posts($entityManager);

if ( $posts->isPostEmpty() ){
    $posts->generateTestPosts(1000, function(){
        return
            (new joshtronic\LoremIpsum())->paragraphs(rand(1,5));
    });
}
$urls = explode('?', $_SERVER['REQUEST_URI']);

if ( $api->isBind( $urls[0], $_SERVER['REQUEST_METHOD'] ) ) {
    $api->handle($urls[0], $_REQUEST);
} else {
    die(
        'You request "'.$_SERVER['REQUEST_METHOD'].' '.$urls[0].'" is not registred.'
    );
}