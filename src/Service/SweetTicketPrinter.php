<?php


namespace App\Service;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class SweetTicketPrinter
{
    /**
     * @var Int
     */
    private $width = 42;
    /**
     * @var Object
     */
    private $printer;
    /**
     * @var string
     */
    private $type;
    /**
     * @var Printer
     */
    private $ticket;
    /**
     * @var int
     */
    private $times;
    /**
     * @var Object
     */
    private $data;

    public function __construct($data)
    {
        $this->printer = $data->printer;
        $this->type = $data->type;
        $this->data = $data->data;
        $this->times = (!$data->times) ? 1 : $data->times;
    }

    public function init( $printer )
    {
        $this->ticket = $this->connect($printer);
    }

    public function connect($printer, $only_check = FALSE)
    {
        $connector = null;
        try {
            switch ( $printer->type )
            {
                case 'windows-usb':
                case 'smb':
                    $connector = new WindowsPrintConnector( $printer->name_system );
                    break;

                case 'ethernet':
                    $connector = new NetworkPrintConnector( $printer->name_system, $printer->port, 3);

                    break;

                case 'cups':
                    $connector = new CupsPrintConnector( $printer->name_system );
                    break;

                default:
                    throw new \Exception("Tipo de ticketera no soportado");
                    break;
            }

            $ticket = new Printer($connector);

            if($only_check)
                $ticket->close();
            else
                return $ticket;

        } catch (\Throwable $th) {
            throw new \Exception("No se pudo conectar con la tiketera");
        }
    }


    public function printTicket()
    {
        $this->init( $this->printer );
        $times = $this->times;

        for ($i=0; $i < $times; $i++) {
            $this->printLayout();
        }

        $this->ticket->close();
    }


    private function printLayout()
    {
        try {
            $this->header();
            switch ( $this->type )
            {
                case 'invoice':
                    $this->businessAdditional();
                    $this->documentLegal();
                    $this->ticket->feed(1);
                    $this->customer();
                    $this->additional();
                    $this->ticket->feed(1);
                    $this->detail();
                    $this->amounts();
                    $this->finalMessage();
                    $this->qr();

                    $this->ticket->pulse();
                    break;

                case 'note':
                    $this->documentLegal();
                    $this->ticket->feed(1);

                    $this->customer();
                    $this->additional();
                    $this->ticket->feed(1);

                    $this->detail();
                    $this->total();
                    break;

                case 'command':
                    $this->ticket->feed(1);
                    $this->print_location();
                    $this->documentLegal();
                    $this->additional();
                    $this->table_waiter();
                    $this->ticket->feed(1);
                    $this->annulment();
                    $this->ticket->feed(1);
                    $this->detail();
                    break;

                case 'precount':
                    $this->documentLegal();
                    $this->ticket->feed(1);
                    $this->additional();
                    $this->table_waiter();
                    $this->ticket->feed(1);
                    $this->detail();
                    $this->total_();
                    break;

                case 'extra':
                    $this->ticket->feed(1);
                    $this->extra_header();
                    $this->ticket->feed(1);
                    $this->extra_general();
                    $this->ticket->feed(1);
                    $this->detail();
                    break;

                default:
                    throw new \Exception("No se pudo conectar con la tiketera");
                    break;
            }

            $this->ticket->feed(4);
            $this->ticket->cut('CUT_PARTIAL');
            //$this->ticket->close();
        } catch (Exception $e) {
            echo "No se pudo imprimir en esta ticketera: " . $e->getMessage() . "\n";
        }
    }

    /*----------  privates  ----------*/

    private function header()
    {
        if($this->data->business->comercialDescription->type == 'text'){
            $this->ticket->setEmphasis( true );
            $this->ticket->setTextSize(2, 2);
            $this->ticket->text( str_pad(
                    strtoupper( $this->data->business->comercialDescription->value ),
                $this->width/2, ' ', STR_PAD_BOTH ) ) ;
            $this->ticket->setEmphasis( false );
            $this->ticket->setTextSize(1, 1);

            $this->ticket->feed(1);
            $this->ticket->text( str_pad( ' '.$this->data->business->description.' ', $this->width, '*', STR_PAD_BOTH ) );
        }
        if($this->data->business->comercialDescription->type == 'img') {
            throw  new \Exception('No se soporta imagen aun');
        }

        $this->ticket->feed(1);
    }

    private function businessAdditional()
    {
        if(!isset($this->data->business->additional))
            return;

        foreach ($this->data->business->additional as $additional){
            $this->ticket->text( str_pad( $additional, $this->width, ' ', STR_PAD_BOTH ) );
            $this->ticket->feed(1);
        }
    }

    private function documentLegal()
    {
        $this->ticket->setEmphasis( true );

        switch ( $this->type ) {
            case 'invoice':
            case 'note':
            case 'command':
                $this->ticket->text(  str_pad(
                $this->data->document.' : '.
                $this->data->documentId,
                $this->width, ' ', STR_PAD_BOTH ) );
                break;

            case 'precount':
                $this->ticket->setTextSize(2, 2);
                $this->ticket->text( str_pad(
                    strtoupper( $this->data->document ),
                    $this->width/2, ' ', STR_PAD_BOTH ) ) ;
                $this->ticket->setTextSize(1, 1);
                break;
        }

        $this->ticket->feed(1);
        $this->ticket->setEmphasis( false );
    }

    private function customer()
    {
        if(!isset($this->data->customer))
            return;

        $this->ticket->setEmphasis( true );
        $this->ticket->text( str_pad( 'ADQUIRIENTE : ', $this->width, ' ', STR_PAD_RIGHT ) );
        $this->ticket->feed(1);
        $this->ticket->setEmphasis( false );

        if( $this->data->customer )
        {
            $customer = $this->data->customer;

            $this->ticket->text( str_pad( $customer->document_type . ' : ' . $customer->document_number, $this->width, ' ', STR_PAD_RIGHT ) );
            $this->ticket->feed(1);

            $this->ticket->text( str_pad( $customer->description, $this->width, ' ', STR_PAD_RIGHT ) );
            $this->ticket->feed(1);

            $this->ticket->text( str_pad( $customer->address ?? '', $this->width, ' ', STR_PAD_RIGHT ) );
            $this->ticket->feed(1);
        }
        else
        {
            $this->ticket->text( str_pad( '--', $this->width, ' ', STR_PAD_RIGHT ) );
            $this->ticket->feed(1);
        }
    }

    private function additional()
    {
        if(!isset($this->data->additional))
            return;

        foreach ($this->data->additional as $additional){
            $this->ticket->text( str_pad( $additional, $this->width, ' ', STR_PAD_RIGHT ) );
            $this->ticket->feed(1);
        }
    }

    private function detail()
    {
        if(!isset($this->data->items))
            return;

        $this->ticket->setEmphasis( true );
        $this->ticket->text( str_pad( ' DESCRIPCIÓN', 35, ' ', STR_PAD_RIGHT ) );
        $this->ticket->text( str_pad( 'TOTAL', 7, ' ', STR_PAD_RIGHT ) );

        $this->ticket->feed(1);
        $this->ticket->text( str_repeat( '-', $this->width ) );
        $this->ticket->feed(1);
        $this->ticket->setEmphasis( false );

        foreach ($this->data->items as $item)
        {
            if(is_array($item->description)){
                $descriptionLength = 35;
                for ($i = 0; $i < count($item->description); $i++){
                    $this->ticket->text( str_pad($item->description[$i], $descriptionLength , ' ', STR_PAD_RIGHT)  );

                    if($i == 0 ){
                        $this->ticket->text( str_pad($item->totalPrice, 7 , ' ', STR_PAD_LEFT)  );
                        $descriptionLength = $this->width;
                    }
                    $this->ticket->feed(1);
                }
            }
            else{
                $this->ticket->text( str_repeat(' ', 2) );
                $this->ticket->text( str_pad($item->description, 33 , ' ', STR_PAD_RIGHT)  );
                $this->ticket->text( str_pad($item->totalPrice, 7 , ' ', STR_PAD_LEFT)  );
                $this->ticket->feed(1);
            }
        }

        $this->ticket->text( str_repeat( '-', $this->width )."\n" );
    }

    private function amounts()
    {
        if(!isset($this->data->amounts))
            return;

        foreach ($this->data->amounts as $field => $value){
            $this->ticket->text( $this->total_align_text($field) );
            $this->ticket->text( $this->total_align_value($value) );
            $this->ticket->feed(1);
        }

        $this->ticket->text( str_repeat( '-', $this->width ) );
        $this->ticket->feed(1);
    }

    private function total_align_text( $param )
    {
        return str_pad( $param , 35, " ", STR_PAD_LEFT );
    }

    private function total_align_value( $param )
    {
        return str_pad( $param , 7, ' ', STR_PAD_LEFT );
    }

    private function finalMessage()
    {
        if(!isset($this->data->finalMessage))
            return;

        $finalMessage = $this->data->finalMessage;
        if(!$finalMessage)
            return;

        if(is_array($finalMessage)){
            foreach ( $finalMessage as $message){
                $this->ticket->text(str_pad( $message, $this->width, ' ', STR_PAD_BOTH ) );
                $this->ticket->feed(1);
            }
        }
        else{
            $this->ticket->text(str_pad( $this->data->finalMessage, $this->width, ' ', STR_PAD_BOTH ) );
            $this->ticket->feed(1);
        }
    }

    private function qr()
    {
        if(!isset($this->data->stringQR))
            return;

        $this->ticket->setJustification( Printer::JUSTIFY_CENTER );

        $options = new QROptions([
            'version'   => 10,
            'eccLevel'  => QRCode::ECC_Q,
            'scale'     => 4
        ]);

        if ( $this->printer->name_system == '127.0.0.1' && $this->printer->type == 'ethernet') {
            $this->ticket->text($this->data->stringQR);
            $this->ticket->feed(1);
        } else {
            $qrGenerator = new QRCode($options);
            $qrGenerator->render($this->data->stringQR, 'qr/qr.png');
            $logo = EscposImage::load( 'qr/qr.png' , false);
            $this->ticket->graphics( $logo, Printer::IMG_DEFAULT );
        }
    }
}