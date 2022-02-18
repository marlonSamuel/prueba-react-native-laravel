<?php

namespace App\Http\Controllers;
use App\tc_variable_type;
use App\tc_history;
use App\tc_history_detail;
use GuzzleHttp\Client;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DateTime;
use Carbon\Carbon;

use Illuminate\Http\Request;

class TipoCambioController extends ApiController
{
	//obtener tipos de variables
    public function getTipoVariables()
    {

        $respArray = $this->SoapTipoVariable();

        foreach ($respArray as $item) {
            $_row = tc_variable_type::where('moneda',$item->moneda)->first();
            if(is_null($_row)){
                tc_variable_type::create([
                    'moneda'=> $item->moneda,
                    'desc'=>$item->descripcion
                ]);
            }
        }

        $data = tc_variable_type::all();

        return $this->showAll($data);
    }


    //funcion para obtener historial
    public function getHistory()
    {
        $data = tc_history::with('detail')->get();
        return $this->showAll($data);
    }


    //obtener tipo de cambio por fechas
    public function getTipoCambio($init, $end)
    {
        //parse dates
        $initparse = Carbon::parse($init);
        $endparse = Carbon::parse($end);

        if($endparse < $initparse){
            return $this->errorResponse('Se debe especificar una fecha de inicio menor a fecha fin', 422);
        }

        $dataArray = $this->SoapTipoCambio($init, $end);

        $json = json_encode($dataArray);
        $decarray = json_decode($json,TRUE);
        $decarray = $decarray['Var'];

        $dlen = count($decarray);

        $groubymoneda = collect($decarray)->groupBy('moneda');

        DB::beginTransaction();
        $peticion = tc_history::all()->count() + 1;

        foreach ($groubymoneda as $key => $item) {
            $tipomoneda = tc_variable_type::where('moneda',$key)->first();

            if(!is_null($tipomoneda)){
                $prom_tc_compra = $item->sum('compra')/count($item);
                $prom_tc_venta = $item->sum('venta')/count($item);

                $history = tc_history::create([
                            'peticion' => $peticion,
                            'variable_type_id' => $tipomoneda->id,
                            'inicio' => $initparse,
                            'fin'=>$endparse,
                            'prom_tc_compra'=>$prom_tc_compra,
                            'prom_tc_venta' =>$prom_tc_venta
                        ]);

                foreach ($item as $item3) {
                     $fecha = str_replace('/', '-', $item3['fecha']);
                     $fecha = Carbon::parse($fecha);
                     tc_history_detail::create([
                        'history_id' => $history->id,
                        'fecha' => $fecha,
                        'tc_compra' => $item3['compra'],
                        'tc_venta' => $item3['venta']
                    ]);
                }
            }
        }
        DB::commit();

        $data = tc_history::with('detail')->get();

        return $this->showAll($data);
    }

    //obtener listado de variables.
    public function SoapTipoVariable()
    {
        $http = new Client(
            [
                'verify' => false
            ]
        );

        $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                          <soap:Body>
                            <VariablesDisponibles xmlns="http://www.banguat.gob.gt/variables/ws/" />
                          </soap:Body>
                        </soap:Envelope>';

        try {
            $response = $http->post(
            'https://www.banguat.gob.gt/variables/ws/TipoCambio.asmx',
                [
                    'body'    => $xmlRequest,
                    'headers' => [
                    'Content-Type' => 'text/xml',
                    'SOAPAction' => 'http://www.banguat.gob.gt/variables/ws/VariablesDisponibles', // SOAP Method to post to
                    ]
                ]
            );
        } catch (\Exception $e) {
            return 'Exception:' . $e->getMessage();
        }

        if ($response->getStatusCode() === 200) {
            // Success!
            $xmlResponse = simplexml_load_string($response->getBody()->getContents()); // Convert response into object for easier parsing
        } else {
            return response()->json("fallo peticion");
        }

        $resp = $xmlResponse->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->VariablesDisponiblesResponse;

        return $resp->VariablesDisponiblesResult->Variables->Variable;
    }


 	//obtener tipo de cambios
 	public function SoapTipoCambio($init, $end)
 	{
        $http = new Client(
            [
                'verify' => false
            ]
        );

        $xmlRequest = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                          <soap:Body>
                            <TipoCambioRango xmlns="http://www.banguat.gob.gt/variables/ws/">
                              <fechainit>'.$init.'</fechainit>
                              <fechafin>'.$end.'</fechafin>
                            </TipoCambioRango>
                          </soap:Body>
                        </soap:Envelope>';

        try {
            $response = $http->post(
            'https://www.banguat.gob.gt/variables/ws/TipoCambio.asmx',
                [
                    'body'    => $xmlRequest,
                    'headers' => [
                    'Content-Type' => 'text/xml',
                    'SOAPAction' => 'http://www.banguat.gob.gt/variables/ws/TipoCambioRango', // SOAP Method to post to
                    ]
                ]
            );
        } catch (\Exception $e) {
            return 'Exception:' . $e->getMessage();
        }

        if ($response->getStatusCode() === 200) {
            // Success!
            $xmlResponse = simplexml_load_string($response->getBody()->getContents()); // Convert response into object for easier parsing
        } else {
            return response()->json("fallo peticion");
        }

        $resp = $xmlResponse->children('http://schemas.xmlsoap.org/soap/envelope/')->Body->children()->TipoCambioRangoResponse->TipoCambioRangoResult;

        return $resp->Vars;
 	}
}
