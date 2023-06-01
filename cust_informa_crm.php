#!/usr/bin/php
<?php
/*Script criado para informar ao CRM do cliente os dados do agente que atendeu a chamada.
Esse script é utilizado na macro fila e somente é executando quando um agente atende a chamada.
Alem de informar o CRM, o script salva no relatório de ligações de fila o número do protoclo. Esse número de protocolo é retirando do UNIQUEID da ligação.
Versao: 3 
Data de Criação: não faco ideia
Data de alteração: 14/04/2023
*/

require_once ('phpagi.php');
include('httpful.phar');
date_default_timezone_set('America/Bahia');

set_time_limit(0);

$agi = new AGI();

// Pega agente 
$agente = $agi->get_variable("MEMBERNAME")['data'];
$agi->verbose("Quem atendeu: ".$agente);
//Pega ramal
$ramal = explode('/',$agi->get_variable("MEMBERINTERFACE")['data']);
$agi->verbose("Ramal: ".$ramal[1]);
// Pega fila
$filaatende = $agi->get_variable("QUEUENAME")['data'];
$agi->verbose("Fila: ".$filaatende);
// Pega numero de A
$origem = $agi->request["agi_callerid"];
$agi->verbose("Quem Ligou: ".$origem);
//Pega data
$v_data = date('Y-m-d');
$agi->verbose("Data: ".$v_data);
//Pega hora
$v_hora = date('His');
$agi->verbose("Data: ".$v_hora);
//Pega UNIQUEID
$protocolo =$agi->get_variable('UNIQUEID')['data'];
$agi->verbose("Protocolo: ".$protocolo);

//$filaatende = ("Atendimento");
//$ramal[1] = ("5566");
//$origem = ("51999776655");


$agi->verbose("Enviando informacoes ao CRM");

try{
    $agi->verbose("Entrei no try");
    $uri = "http://x.x.x.x/api/omnipopupservice";
    $agi->verbose($uri);
    $data = ([
            'codigoAgetne' => "$ramal",
            'telefone' => "$origem",
            'dados' => "$protocolo"
    ]);

    $result = \Httpful\Request::post($uri)
        ->timeoutIn(10)
        ->sendsJson()
        ->addHeader ('Authorization', 'Bearer ' . '$token do cliente')
	->body(json_encode($data))
        ->send();
     $agi->verbose("Mostrando o Retorno: " .$result);
    //$agi->verbose("@@@@@@@@@@->    ".$retorno."    <-@@@@@@@@@@");
                        
    //return $retorno;
                
    }catch(Exception $e){
            print("Cocorreu um erro");     
    } 

try{
            $agi->verbose("Salvando o protocolo\n");
                            $uri = "http://127.0.0.1:8001/api/v2/ligacao/classifica";
                            $data = ([
                                'uniqueid' => "$protocolo",
                                'protocolo' => "$protocolo"
                            ]);
                                    
                            $retorno = \Httpful\Request::post($uri)
                                            ->timeoutIn(10)
                                            ->sendsJson()
                                            ->addHeader('Content-Type:', 'application/json')
                                            ->addHeader ('Authorization', 'Bearer ' . '$token do Xcontact')
                                            ->body(json_encode($data))
                                            ->send();
                            
                            $ret_cod = $retorno->code;                 
                            if($ret_cod == 200){
                                $agi->verbose("Protocolo salvo com sucesso.\n");
                               
                                return $retorno;
    
                            }else{
                                $agi->verbose("Falha ao salvar o protocolo, o retorno foi: " . $ret_cod . "\n");}
                            exit;         
         
            exit();
    
        }catch(Exception $e){
            $agi->verbose("Ocorreu um erro no try cat do protocolo\n\n");
                            
                    } 
        exit(); 
exit();
?>

