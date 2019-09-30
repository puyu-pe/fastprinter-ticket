<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrinterController
{
    public function printTicket(Request $request)
    {
        $response = new Response();

        try{
            $data = json_decode($request->getContent());

            if(!$data)
                throw new \Exception('Formato incorrecto');

            $response->setContent(json_encode([
                'message' => $data
            ]), 200);
        }
        catch(\Throwable $th){

            $response->setContent(json_encode([
            'message' => $th->getMessage()
            ]), 500);
        }

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}