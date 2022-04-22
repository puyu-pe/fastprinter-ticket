<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Service\SweetTicketPrinter;
use Symfony\Component\HttpFoundation\JsonResponse;

class PrinterController
{
    public function printTicket(Request $request)
    {
        $response = [];
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

            $response = [
                'success' => TRUE,
                'message' => 'Se imprimio correctamente'
            ];;
        } catch (\Throwable $th) {
            $response = [
                'success' => FALSE,
                'message' => "File : " . $th->getFile() . " | Line : " . $th->getLine() . " | Msg :" . $th->getMessage()
            ];
        } finally {

            return new JsonResponse($response);
        }
    }
}
>>>>>>> ece245b4105b08e8c1f1e192abb7a849d0474143
