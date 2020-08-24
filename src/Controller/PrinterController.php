<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\SweetTicketPrinter;

class PrinterController
{
    public function printTicket(Request $request)
    {
        $response = new Response();
        try {
            if ($request->isMethod('POST'))
                $data = $request->getContent();
            else
                $data = $request->query->get('data');

            $data = json_decode($data);

            if (!$data)
                throw new \Exception('Formato incorrecto para impresion');

            if (is_array($data)) {
                foreach ($data as $ticket) {
                    $STPrinter = new SweetTicketPrinter($ticket);
                    $STPrinter->printTicket();
                }
            } else {
                $STPrinter = new SweetTicketPrinter($data);
                $STPrinter->printTicket();
            }

            $response->setContent(json_encode([
                'message' => 'Se imprimio correctamente'
            ]))
                ->setStatusCode(200);
        } catch (\Throwable $th) {

            $response->setContent(json_encode([
                'message' => $th->getMessage()
            ]))
                ->setStatusCode(500);
        }

        // $response->headers->set('Content-Type', 'application/json');
        // $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
