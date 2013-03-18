<?php
//TODO - Implementar o IPN Listener
//TODO - Baixar uma classe para ajudar aqui: https://github.com/Quixotix/PHP-PayPal-IPN
	/*
      
      <?php
 
// STEP 1: Read POST data
 
// reading posted data from directly from $_POST causes serialization 
// issues with array data in POST
// reading raw POST data from input stream instead. 
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}
 
 
// STEP 2: Post IPN data back to paypal to validate
 
$ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
// In wamp like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
// of the certificate as shown below.
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    // error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);
 
 
// STEP 3: Inspect IPN validation result and act accordingly
 
if (strcmp ($res, "VERIFIED") == 0) {
    // check whether the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process payment
 
    // assign posted variables to local variables
    $item_name = $_POST['item_name'];
    $item_number = $_POST['item_number'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];
} else if (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
}
?>

   */
   
$tmpPath  = str_replace( "\\", "/", __FILE__ );
$tmpPathA = explode( "/", $tmpPath );
$tmpPath  = substr( $tmpPath, 0, strlen( $tmpPath ) - strlen( end( $tmpPathA ) ) );
set_include_path( $tmpPath . 'paypal' );
	
require_once( 'services/PayPalApi/PayPalAPIInterfaceServiceService.php' );
require_once( 'PPLoggingManager.php' );
require_once( 'ipnlistener.php' );
require_once( 'properties.class.php' );
/*

	HOWTO - Defina o endereço de entrega(setAddress()), informe se vai ter frete(setShipping), adiciona as infos
	dos nos itens (addItem), feito tudo, execute o SetExpressCheckout(), que vai retornar um objeto com o ID da
	transação, TOKEN da transação, usado para gerar a url de redirecionamento, com o metodo paypalUrl();

*/

//CONSTANTES
	
	//Status do pedido
	define( 'PP_STATUS_NEW', 0 );			//Acabou de instanciar o objeto
	
	//Tipo de itemPhysical
	define( 'PP_ITEMCAT_DIGITAL' , "Digital");
	define( 'PP_ITEMCAT_PHYSICAL', "Digital");
	
	//Tipos de pagamento
	define( 'PP_PAYMENT_SALE' , 'Sale' );
	define( 'PP_PAYMENT_AUTH' , 'Authorization' );
	define( 'PP_PAYMENT_ORDER', 'Order' );
	
	//Tipo de solicitação de endereço
	define( 'PP_SHIPPING_SHOW'  , 0 );  	//Solicita endereço de entrega no paypal
	define( 'PP_SHIPPING_NOSHOW', 1 );  	//Não solicita endereço de entrega no paypal
	define( 'PP_SHIPPING_AUTO'  , 2 );  	//Se não informado, usa o da conta do usuário
	
	
//TODO - Colocar a parte de valores

   class Paypal extends Properties{
		
		private $globals;
		private $configs;
		private $transaction;						//Todos os dados usados na transação
		
		
		/***   Construtores ***/
		function __construct(){
			
			$a = func_get_args();
			$i = func_num_args();
			
			if (method_exists($this,$f='__construct'.$i)) {
				call_user_func_array(array($this,$f),$a); 
			} else {
				throw( new Exception('Numero de parametros invalido') );
			}
			$this->init();
			
		}

		function __construct2( $globals, $returnUrl ){	
			
			$this->globals     = $globals;
			$this->transaction->returnUrl = $returnUrl;
			$this->transaction->cancelUrl = $returnUrl;
			
		}
		
		function __construct3( $globals, $returnUrl, $cancelUrl ) {
			
			$this->globals     = $globals;
			$this->transaction->returnUrl = $returnUrl;
			$this->transaction->cancelUrl = $cancelUrl;
			
		}
	  
		//Destructor força a finalização dos objetos e aberto
		function __destruct() {}
		
		
		//Inicia(ou reseta) os componentes do objeto
		private function init(){
			
			$cfg = array( array( "CURRENCY"     , "BRL" ),
							  array( "COUNTRYCODE"  , "BR" ),
							  array( "SHIPPING_TYPE", PP_SHIPPING_NOSHOW),
							  array( "PAYMENT_TYPE" , PP_PAYMENT_SALE),
							  array( "VERSION"      , '95.0') );
			
			//Inicia as configs do paypal
			foreach( $cfg as $a ){
				$this->config( $a[0], $this->globals->cfg->getConfig( "PAYPAL", $a[0], $a[1] ) );
			}
			
			
			$this->transaction->status   = PP_STATUS_NEW;
			$this->transaction->items    = array();
			$this->transaction->shipping = array( '', 0);
			$this->transaction->address  = null;
			
		}
		
		
		
		//Retorna (ou seta) uma nova configuração
		function config( $key, $val = '' ){
			
			if( !is_array( $this->configs ) ){
            $this->configs = array();
         }
			
			$key = strtolower( $key );
			
			if( func_num_args() > 1 ){
				$this->configs[ $key ] = $val;
			} else {
				if( isset( $this->configs[ $key ] ) ){
					return $this->configs[ $key ];
				}
			}
			
			return $val;
		}
		
		
		
		//Seta o endereço do pedido
		function setAddress( $name, $type, $street, $num, $comp, $district, $city, $state, $zipcode ){
			
			$this->transaction->address->Name        = $name;
			$this->transaction->address->line1       = $type . ' ' . $street . ', ' . $num . (empty( $comp ) ? '' : ' - ' . $comp);
			$this->transaction->address->line2       = $district;
			$this->transaction->address->city        = $city;
			$this->transaction->address->state 		  = $state;
			$this->transaction->address->PostalCode  = $zipcode;
			$this->transaction->address->countryCode = $this->config( 'countrycode' );
		
		}
		
		
		//Adiciona o valor do pagamento da parte de entrega
		function setShipping( $method, $value ){
			
			if( empty( $method ) ) throw( new Exception( "Metodo de entrega informado não é inválido" ) );
			if( $value <= 0 ) throw( new Exception( "Metodo de entrega informado é inválido" ) );
			$this->transaction->shipping = array( $method, $value );
			
		}
		
		
		//adiciona um item na venda
		function addItem( $name, $qtde, $value, $category, $desc = "" ){
			
			if( empty( $name ) )	throw( new Exception("Nome inválido para o item") );
			if( $qtde <= 0 )     throw( new Exception("Quantidade inválida para o item") );
			if( $value <= 0 )    throw( new Exception("Valor inválido para o item") );
			if( $category != PP_ITEMCAT_DIGITAL &&
			    $category != PP_ITEMCAT_PHYSICAL )
				 						throw( new Exception("Categoria inválida para o item") );
			
			$item = null;
			$item->name        = $name;
			$item->qtde        = $qtde;
			$item->value       = $value;
			$item->category    = $category;
			$item->description = $desc;
			
			$this->transaction->items[] = $item;
			
			return true;
			
		}
		
		
		//Seta a transação para o express checkout
		function setExpressCheckout(){

			$totalValue     = 0;
			$itemTotal      = 0;
			$transaction    = $this->transaction;
			$PaymentDetails = new PaymentDetailsType();
			
			//Se for informado o endereço de entrega, informa ele aqui
			if( is_object( $transaction->address ) ){
				
				$address = new AddressType();
				$address->Name        = $transaction->address->Name;
				$address->city        = $transaction->address->city;
				$address->line1       = $transaction->address->line1;
				$address->line2       = $transaction->address->line2;
				$address->state       = $transaction->address->state;
				$address->PostalCode  = $transaction->address->PostalCode;
				$address->countryCode = $transaction->address->countryCode;
				$PaymentDetails->ShipToAddress = $address;
				
			}
			
			
			//Os itens da transação
			foreach( $transaction->items as $item ){
				
				$itemDetails = new PaymentDetailsItemType();
				$itemDetails->Name         = $item->name;
				$itemDetails->Amount       = new BasicAmountType( $this->config('CURRENCY'), $item->value );
				$itemDetails->Quantity     = $item->qtde;
				$itemDetails->ItemCategory = $item->category;
				$itemDetails->Description  = $item->description;
				
				$PaymentDetails->PaymentDetailsItem[] = $itemDetails;
				$itemTotal += $item->value;
				
			}
			
			//Referente aos valores e formas de pagamentos
			//$PaymentDetails->TaxTotal = $taxTotal;
			$PaymentDetails->ItemTotal     = new BasicAmountType( $this->config('CURRENCY'), $itemTotal);
			$PaymentDetails->ShippingTotal = new BasicAmountType( $this->config('CURRENCY'), $transaction->shipping[1]);
			$PaymentDetails->OrderTotal    = new BasicAmountType( $this->config('CURRENCY'), $transaction->shipping[1] + $itemTotal );
			$PaymentDetails->PaymentAction = $this->config( 'PAYMENT_TYPE' );
			
			//Seta os dados da transferencia
			$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
			$setECReqDetails->PaymentDetails[0] = $PaymentDetails;
			$setECReqDetails->CancelURL = $transaction->cancelUrl;
			$setECReqDetails->ReturnURL = $transaction->returnUrl;
			//$setECReqDetails->NoShipping = $_REQUEST['noShipping'];		//TODO - verificar se isso é necessario
			$setECReqDetails->NoShipping = 1;     //TODO - verificar se isso é necessario



			$setECReqType = new SetExpressCheckoutRequestType();
			$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
			$setECReqType->Version = $this->config( 'VERSION' );
			$setECReq = new SetExpressCheckoutReq();
			$setECReq->SetExpressCheckoutRequest = $setECReqType;
			
			// storing in session to use in DoExpressCheckout
			$paypalService = new PayPalAPIInterfaceServiceService();
			$setECResponse = $paypalService->SetExpressCheckout($setECReq);
			
         if( $setECResponse->Ack == 'Success' ){
				
				$this->transaction->id = $setECResponse->CorrelationID;
				return $setECResponse;
				
			}
			
		}
		


      function doExpressCheckout( $token, $payerId, $total ){

         $totalValue    = 0;
         $itemTotal     = 0;
         $transaction   = $this->transaction;
         $logger        = new PPLoggingManager('DoExpressCheckout');

         
         // ------------------------------------------------------------------
         // this section is optional if parameters required for DoExpressCheckout is retrieved from your database
         $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
         $getExpressCheckoutDetailsRequest->Version = $this->config('VERSION');
         $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
         $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

         $paypalService = new PayPalAPIInterfaceServiceService();
         $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);

         //----------------------------------------------------------------------------
         

         $PaymentDetails= new PaymentDetailsType();


         /*//Se for informado o endereço de entrega, informa ele aqui
         if( is_object( $transaction->address ) ){
            
            $address = new AddressType();
            $address->Name        = $transaction->address->Name;
            $address->city        = $transaction->address->city;
            $address->line1       = $transaction->address->line1;
            $address->line2       = $transaction->address->line2;
            $address->state       = $transaction->address->state;
            $address->PostalCode  = $transaction->address->PostalCode;
            $address->countryCode = $transaction->address->countryCode;
            $PaymentDetails->ShipToAddress = $address;
            
         }
         
         
         //Os itens da transação
         foreach( $transaction->items as $item ){
            
            $itemDetails = new PaymentDetailsItemType();
            $itemDetails->Name         = $item->name;
            $itemDetails->Amount       = new BasicAmountType( $this->config('CURRENCY'), $item->value );
            $itemDetails->Quantity     = $item->qtde;
            $itemDetails->ItemCategory = $item->category;
            $itemDetails->Description  = $item->description;
            
            $PaymentDetails->PaymentDetailsItem[] = $itemDetails;
            $itemTotal += $item->value;
            
         }
         
         //Referente aos valores e formas de pagamentos
         $PaymentDetails->TaxTotal = $taxTotal;
         $PaymentDetails->ItemTotal     = new BasicAmountType( $this->config('CURRENCY'), $itemTotal);
         $PaymentDetails->ShippingTotal = new BasicAmountType( $this->config('CURRENCY'), $transaction->shipping[1]); */

         $PaymentDetails->OrderTotal    = new BasicAmountType( $this->config('CURRENCY'), $total );
         $PaymentDetails->PaymentAction = $this->config( 'PAYMENT_TYPE' );

         $DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
         $DoECRequestDetails->PayerID = $payerId;
         $DoECRequestDetails->Token = $token;
         $DoECRequestDetails->PaymentDetails[0] = $PaymentDetails;

         $DoECRequest = new DoExpressCheckoutPaymentRequestType();
         $DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
         $DoECRequest->Version = $this->config('VERSION');

         $DoECReq = new DoExpressCheckoutPaymentReq();
         $DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;

         $DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
         $DoECResponse->getExpressCheckoutInfos = $getECResponse;
         return $DoECResponse;
		
      }



      //Busca os dados de um express checkout
      function getExpressCheckout( $token ){

         $logger = new PPLoggingManager('GetExpressCheckout');

         $getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
         $getExpressCheckoutDetailsRequest->Version = $this->config('VERSION');

         $getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
         $getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

         $paypalService = new PayPalAPIInterfaceServiceService();
         $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);

         if($getECResponse->Ack =='Success'){
            return $getECResponse;
         }

         return null;

      }


      //Retorna a validação de um IPN para o paypal
      function verifyIpn( $lSandbox = false ){

         $listener = new IpnListener();
         $listener->use_sandbox = $lSandbox;

         try {
             $verified = $listener->processIpn();
         } catch (Exception $e) {
             //Erro fatal no processo de validacao
             if( file_exists('paypal_err.log')) $log = file_get_contents('paypal_err.log');
             $log = $log . print_r($e,true) . "\n";
             file_put_contents('paypal_err.log', $log);
         }

         if ($verified) return true;
         return false;

      }


		//Retorna a url para o paypal, já com o token
		function paypalUrl( $token, $lSandbox = false ){
         
         if( $lSandbox ) return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
         return 'https://www.paypal.com/webscr?cmd=_express-checkout&token=' . $token;

		}
		
		
		
		
		
	}

