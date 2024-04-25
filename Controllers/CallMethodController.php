<?php

declare(strict_types = 1);

namespace G_H_PROJECTS_INCLUDE\Controllers;


use G_H_PROJECTS_INCLUDE\Fetch;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CallMethodController
{
    public function __construct(
        // EntityManagert $entityManagert
    ){}
    public function index(Request $request, Response $response): Response
    {  
        //for now dont call method 
        $fetchDecoded = json_decode(file_get_contents('php://input'), true);//bool ascociative array
        $row = $fetchDecoded['row'];
        $action = $fetchDecoded['action'];
        $state = $fetchDecoded['state'];
        $video = $this->entityM
        return $response;
        // return $this->twig->render($response);
    }

}
