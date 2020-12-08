<?php
namespace smet;
use \smet\Token;
class Tokens
{
    private $entityManager;

    public function __construct($entityManager){
        $this->entityManager = $entityManager;
    }

    public function isValid($sl_token, $nowTime){
        $sql = "SELECT t.*
                FROM Token t
                WHERE t.sl_token = ? AND ((? - t.created) < 3600)";
        $conn = $this->entityManager->getConnection();

        $stmt = $conn->prepare( $sql );
        $stmt->bindValue(1, $sl_token);
        $stmt->bindValue(2, $nowTime);
        $stmt->execute();

        return
            count($stmt->fetchAll());
    }

    public function isParamsOk($params){
        return
            isset($params['client_id']) &&
            isset($params['name']) &&
            isset($params['email']);
    }

    public function addNew($params, $newToken){
        $token = new Token();

        $token->setClientId($params['client_id']);
        $token->setName($params['name']);
        $token->setEmail($params['email']);
        $token->setCreated( time() );
        $token->setSlToken( $newToken );

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return
            $token->getId();
    }

    public function getNewToken(){
        return
            bin2hex(random_bytes(10));
    }

}