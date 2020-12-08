<?php
namespace smet;
class Assignment {
    private $entityManager;

    public function __construct($entityManager){
        $this->entityManager = $entityManager;
    }

    public function register($postParams){
        $token = new Tokens($this->entityManager);
        try {
            if ($token->addNew($postParams, $newToken = $token->getNewToken()) ){
                header("HTTP/1.1 201 OK");
                die(json_encode([
                    'sl_token' => $newToken,
                    'client_id' => $postParams['client_id'],
                    'email' => $postParams['email']
                ]));
            }
        } catch(\Exception $e){
            header("HTTP/1.1 400 Error");
            die(
                $e->getMessage()
            );
        }
    }

    public function stats($postParams){
        $post = new Posts(
            $this->entityManager
        );

        $stats = [
            'AvgLengthPerMonth' => $post->getAvgLengthPerMonth(),
            'MaxLengthPerMonth' => $post->getLongestPostPerMonth(),
            'TotalPostsPerWeek' => $post->getTotalPostPerWeek(),
            'AvgPostsPerUserPerMonth' => $post->getAvgPostPerUserPerMonth()
        ];

        die(
            json_encode( $stats )
        );
    }

    public function posts($postParams){
        if (!isset($postParams['page'])){
            header("HTTP/1.1 400 Error");
            die('"page" parameter is required.');
        }

        if (!isset($postParams['sl_token'])){

            header("HTTP/1.1 400 Error");
            die('"sl_token" parameter is required.');

        } else if ( !(new Tokens($this->entityManager))->isValid($postParams['sl_token'], time() )){
            header("HTTP/1.1 400 Error");
            die('"Token" is not valid '.$postParams['sl_token']);
        }

        $post = new Posts(
            $this->entityManager
        );

        die(
            json_encode([
                'posts' => $post->getPosts($postParams['page'],100),
                'page' => $postParams['page']
            ])
        );
    }
}