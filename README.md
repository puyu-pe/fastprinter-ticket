# Print Ticket API

Imprime un **ticket** segun el tipo, por el momento solo se soporta `type` : `invoice`, `note`, `command`, `precount`, `extra`

> Nota: La API responde con `200` y mensaje de confirmacion de impresion y en caso de algun error con `500`.

**URL** : `/print_ticket`

**Method** : `POST`

**Auth required** : NO

**Campos**
- Puedes enviar varios tickets dentro de un array `[ objectTicket01, objectTicket02 ]`
- Modelo para boleta o factura

```js

    {
        "type": "invoice",
        "times": 1,
        "printer": {
            "type": "ethernet",
            "name_system": "192.168.1.245",
            "port": "9100"
        },
        "data": {
            "business": {
                "comercialDescription": {
                    "type": "text",
                    "value": "REY DE LOS ANDES"
                },
                "description": "EMPRESA DE TRANSPORTES REY DE LOS ANDES S.A.C.",
                "additional": [
                    "RUC 20450523381 AGENCIA ABANCAY",
                    "DIRECCIÓN : Av. Brasil S/N",
                    "TELÉFONO : 989290733"
                ]
            },
            "document": {
                "description": "Boleta de Venta\r ELECTRONICA",
                "indentifier": "B001 - 00000071"  
            },
            "customer": [
	    	"ADQUIRIENTE",
	    	"DNI: 20564379248",
	        "FASTWORKX SRL",
	        "AV CANADA N 159 ABANCAY ABANCAY APURIMAC"
            ],
            "additional": [
                "FECHA EMISIÓN : 01/10/2019 14:51:26",
                "MONEDA : SOLES",
                "USUARIO : "
            ],
            "items": [
                {
                    "description": [
                        "Ruta : ABANCAY-CHALHUANCA",
                        "Embarque : ABANCAY",
                        "Destino : CHALHUANCA",
                        "Asiento : 2",
                        "Pasajero : EMERSON ÑAHUINLLA VELASQUEZ",
                        "DNI : 70930383",
                        "F. Viaje : 01/10/2019 02:00 PM"
                    ],
                    "totalPrice": "9.00"
                }
            ],
            "amounts": {
                "Operacion no gravada": "9.00",
                "IGV": 0,
                "Total": "9.00"
            },
            "finalMessage": [
                "REPRESENTACIÓN IMPRESA COMPROBANTE ELECTRÓNICO",
                "PARA CONSULTAR EL DOCUMENTO VISITA NEXUS",
                "HTTPS://NEXUS.FASTWORKX.COM/20450523381",
                "RESUMEN: null",
                "",
                "POR FASTWORKX S.R.L. - PERÚ"
            ],
            "stringQR": "20450523381|03|B001 - 00000071|0|9.00|01/10/2019|6|[object Object]|"
        }
    },

```

- Imprimir logo  
  Agregar la carpeta y archivo `public/img/logo.png`
  Tamaño recomendado entre 180px o maximo de 230px el lado mas grande de la imagen, respetando la proporcion de esta.

```js
"data": {
        "business": {
            "comercialDescription": {
                "type": "img"
            }
```

- Modelo para extras

```js
    {
        "type": "invoice",
        "times": 1,
        "printer": {
            "type": "ethernet",
            "name_system": "192.168.1.245",
            "port": "9100"
        },
        "data": {
            "business": {
                "comercialDescription": {
                    "type": "text",
                    "value": "REY DE LOS ANDES"
                },
                "description": "EMPRESA DE TRANSPORTES REY DE LOS ANDES S.A.C.",
                "document": "RUC",
                "documentNumber": "20450523381"
            },
            "document": {
                "description": "Control de",
                "indentifier": "B001 - 00000071"  
            },
            "additional": [
                "FECHA EMISIÓN : 01/10/2019 14:51:26",
                "USUARIO : admin"
            ],
            "items": [
                {
                    "description": [
                        "Embarque : ABANCAY",
                        "Destino : CHALHUANCA",
                        "Asiento : 2",
                        "Pasajero : EMERSON ÑAHUINLLA VELASQUEZ",
                        "F. Viaje : 01/10/2019 02:00 PM",
                        "Conductor : QUISPE CONTRERAS GUILLERMO",
                        "Bus : TOYOTA  HIACE PLACA D6R-954",
                        ""
                    ],
                    "totalPrice": "9.00"
                }
            ],
            "finalMessage": "*** CONTROL DE BUS ***"
        }
    }
```

- modelo ejemplo para encomienda

```js
{
    "type": "invoice",
    "times" : 1,
    "printer": {
        "type": "ethernet",
        "name_system": "192.168.1.245",
        "port": "9100"
    },
    "data" : {
            "document": {
                "description": "Boleta de Venta\r ELECTRONICA",
                "indentifier": "B001 - 00000071"  
            },
	    "business": {
	        "comercialDescription": {
	            "type": "text",
	            "value": "REY DE LOS ANDES"
	        },
	        "description": "EMPRESA DE TRANSPORTES REY DE LOS ANDES S.A.C.",
	        "additional": [
            	"RUC 20450523381 AGENCIA ABANCAY",               
            	"DIRECCIÓN : Av. Brasil S/N",
                "TELÉFONO : 989290733"
	        ]
	    },
	    "customer": [
	    	"REMITENTE / CLIENTE",
	    	"DNI: 20564379248",
	        "FASTWORKX SRL",
	        "AV CANADA N 159 ABANCAY ABANCAY APURIMAC"
	    ],
	    "additional": [
	        "FECHA EMISIÓN : 01/10/2019 14:51:26",
	        "MONEDA : SOLES",
	        "CONSIGNADO : RENZO ZABALA"
        ],
	    "items": [
	        {
	            "description": "Tipo : Cajas cerradas",
	            "quantity": 2,
	            "totalPrice": "20.00"
	        },
	        {
	            "description": "Giro de dinero",
	            "quantity": 1,
	            "totalPrice": "5.00"
	        }
	    ],
	    "amounts": {
	        "Operacion no gravada": "25.00",
	        "IGV": 0,
	        "Total": "25.00"
	    },
	    "additionalFooter" :[
	    	"FECHA IMPR: 02/10/2019 16:12:34",
	        "USUARIO : ADMIN | AGENCIA : ABANCAY"	
	    ],
	    "finalMessage": [
	    	"REPRESENTACIÓN IMPRESA DE FACTURA ELECTRÓNICA",
	    	"PARA CONSULTAR EL DOCUMENTO VISITA NEXUS:",
	    	"HTTPS://NEXUS.FASTWORKX.COM/20450523381",
	    	"RESUMEN: Bfdfg+sdfsAfKfVs=",
	    	"",
	    	"POR FASTWORKX S.R.L. - PERÚ"
    	],
	    "stringQR": "20450523381|01|F001|00000006|0|9.00|30/09/2019|6|sdfsdfsdf|"
    }
}
```
- modelo ejemplo para commanda restaurant

```js
{
    "type": "command",
    "times": 1,
    "printer": {
        "type": "linux-usb",
        "name_system": "/dev/usb/lp1",
        "port": "9100"
    },
    "data": {
        "business": {
            "description": "Restaurant H. Pollos"
        },
        "productionArea": "Pizzeria Horno",
        "textBackgroundInverted" : "ANULACION",

            "document": {
                "description": "COMANDA : ",
                "indentifier": "71"  
            },
        "additional": [
            "FECHA EMISIÓN : 01/10/2019 14:51:26",
            "Mesero(a) : Luis",
            "Mesa : Delivery"
        ],
        "items": [
            {
                "quantity": 1,
                "description": "HAWAYANA (FAMILIAR)",
                "commentary" : "con arto queso"
            },
            {
                "quantity": 1,
                "description": "HAWAYANA (PERSONAL)"
            }
        ]
    }
}
```

- modelo ejemplo para extra restaurant

```js
{
    "type": "extra",
    "times": 1,
    "printer": {
        "type": "linux-usb",
        "name_system": "/dev/usb/lp1",
        "port": "9100"
    },
    "data": {
        "business": {
            "description": "Restaurant H. Pollos"
        },
        "titleExtra": {
            "title": "DELIVERY : D-1",
            "subtitle": "26-08-2020 14:40:30"
        },
        "additional": [
            "FUENTE: INTERNET",
            "CLIENTE: EMERSON ÑAHUINLLA VELASQUEZ",
            "DIRECCIÓN: AV VILLA EL SOL MZ E LT O",
            "CELULAR : 983780014",
            "REFERENCIA : DESVIO DE TIERRA DESPUES DE MECANICA DE MOTOS",
            "PAGARA : 100.00"
        ],
        "items": [
            {
                "quantity": 1,
                "description": "HAWAYANA (FAMILIAR)",
                "commentary" : "con arto quesooo",
                "totalPrice" : 14.50 
            },
            {
                "quantity": 1,
                "description": "HAWAYANA (PERSONAL)",
                "totalPrice" : 14.50
            }
        ]
    }
}

```
- modelo ejemplo para `precount` restaurant

```js
{
    "type": "precount",
    "times": 1,
    "printer": {
        "type": "linux-usb",
        "name_system": "/dev/usb/lp1",
        "port": "9100"
    },
    "data": {
        "business": {
            "description": "Restaurant H. Pollos"
        },
        "document": {
            "description": "PRECUENTA"
        },
        "additional": [
            "FECHA EMISIÓN : 01/10/2019 14:51:26",
            "Mesero(a) : Luis",
            "Mesa : Delivery"
        ],
        "items": [
            {
                "quantity": 1,
                "description": "HAWAYANA (FAMILIAR)",
                "totalPrice" : 14.50 
            },
            {
                "quantity": 1,
                "description": "HAWAYANA (PERSONAL)",
                "totalPrice" : 14.50
            }
        ],
	 "amounts": {
	     "Total": "25.00"
	  }
    }
}

```