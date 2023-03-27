<?php 
namespace App\Library;
use stdClass;
use SoapClient;

class MobitelSMS  
{
    private $mask;
    private $username;
    private $password;

    public function __construct()
    {
        $this->mask =  config('services.sms_conf.mobital.mask');
        $this->username =  config('services.sms_conf.mobital.username');
        $this->password =  config('services.sms_conf.mobital.password');
    }

    //here magic happens DO NOT CHANGE ANYTHING FOR GOD's SAKE
    private function getClient()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $client = new SoapClient("http://smeapps.mobitel.lk:8585/EnterpriseSMSV3/EnterpriseSMSWS?wsdl");
        return $client;
    }

    //create session before send SMS
    private function createSession($id,$username,$password,$customer)
    {
        $client = $this->getClient();
        $user = new stdClass();
        $user->id = $id;
        $user->username = $username;
        $user->password = $password;
        $user->customer = $customer;
        $createSession = new stdClass();
        $createSession->user = $user;
        $createSessionResponse = new stdClass();
        $createSessionResponse = $client->createSession($createSession);
        return $createSessionResponse->return;
    }

    //send SMS to number or numbers (you know what i mean!)
    private function sendMessages($session,$alias,$message,$recipients,$messageType)
    {
        $client= $this->getClient();
        $smsMessage= new stdClass();
        $smsMessage->message=$message;
        $smsMessage->messageId="";
        $smsMessage->recipients=$recipients;
        $smsMessage->retries="";
        $smsMessage->sender=$alias;
        $smsMessage->messageType=$messageType;
        $smsMessage->sequenceNum="";
        $smsMessage->status="";
        $smsMessage->time="";
        $smsMessage->type="";
        $smsMessage->user="";
        $sendMessages = new stdClass();
        $sendMessages->session = $session;
        $sendMessages->smsMessage = $smsMessage;
        $sendMessagesResponse = new stdClass();
        $sendMessagesResponse = $client->sendMessages($sendMessages);
        return $sendMessagesResponse->return;
    }

    //dont forget close session after you use it!
    private function closeSession($session)
    {
        $client = $this->getClient();
        $closeSession = new stdClass();
        $closeSession->session = $session;
        $client->closeSession($closeSession);
    }

    public function smsSend($message,...$mobile_number){

            //create a session with mobitel given username & password
            $session = $this->createSession('',$this->username, $this->password,'');

            //send message with created session like this
            $this->sendMessages($session, $this->mask, $message,$mobile_number, 0);

            //close session & check your phone ;)
            $this->closeSession($session);

    }
}
