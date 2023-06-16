<?php

namespace Relesssar\Esedo;
use SimpleXMLElement;
class Esedo
{
    protected $host = '';
    protected $routeid = '';
    protected $senderid = '';
    protected $senderpass = '';
    protected $senderpass2 = '';
    protected $senderpass3 = '';

    public function __construct($esedoHost = 'https://195.12.113.7/bip-sync-wss-gost/',$routeId,$senderId,$senderPass,$senderPass2,$senderPass3)
    {
        $this->host    = $esedoHost;
        $this->routeid    = $routeId;
        $this->senderid    = $senderId;
        $this->senderpass    = $senderPass;
        $this->senderpass2    = $senderPass2;
        $this->senderpass3    = $senderPass3;
    }

    public function clean13 ($xml) {
        return str_replace('&#13;', '', $xml);
    }


    function xmltohtml ($xml) {
        $result = str_replace('> <', '&gt;&lt;', $result);
        $result = str_replace('<', '&lt;', $xml);
        $result = str_replace('>', '&gt;', $result);

        $result = str_replace(array("\r\n"), '', $result);
        //$result = preg_replace('&gt;\s&lt;', ' ', $result);
//        $result = str_replace('/(&gt;)\s(&lt;)/', '112233', $result);


        $result = preg_replace('/(&gt;)\s+(&lt;)/', '', $result);


//        echo '<br>'. 'DEBUG xmltohtml' . '<br>';
//        echo '<pre>' . htmlspecialchars( $result)  . '</pre>';
//        echo '<br>'.'DEBUG xmltohtml' . '<br>';

        return $result;
    }

    public function xmltoJson ($xml) {
        return str_replace('"', '\"', $xml);
    }

    public function xmlFlat($xml) {
        //$result = new SimpleXMLElement($xml);
        $result = $xml;

        // Convert the XML object to a single-line string
        //$one_line_string = str_replace(array("\r", "\n"), '', $result->asXML());
        $one_line_string = str_replace(array("\r", "\n"), '', $result);
        $one_line_string = str_replace(array("\r\n"), '', $result);

        $one_line_string = preg_replace('/\s+/', ' ', $one_line_string);
        $one_line_string = str_replace('> <', '><', $one_line_string);
        //$one_line_string = str_replace('>\r\n<', '><', $one_line_string);

//        echo 'CLEAN';
//        echo '<br>';
//        echo '<pre>' . htmlspecialchars( $one_line_string)  . '</pre>';
//        echo '<hr>';
        return $one_line_string;
    }

    public function esedo_doc_outgoing_xml ($data = null) {
        $file_xlm = '';
        if (count ($data['files']) > 0) {
            $file_xlm = '<attachments>';
            foreach ($data['files'] as $file) {
                $file_xlm .=  '<fileIdentifier>'. $file['fileIdentifier'] .'</fileIdentifier>';
            }
            $file_xlm .= '</attachments>';
        }

        $query = '
<metadataSystem>
    <performers>'.$data['performers'].'</performers>
    <activityId/>
    <from>'.$data['from'].'</from>
    <href>'.$data['docid'].'</href>
    <senderOrg>'.$data['senderorg'].'</senderOrg>
</metadataSystem>
<appendCount>'.$data['appendcount'].'</appendCount>
<authorNameKz>'.$data['authornamekz'].'</authorNameKz>
<authorNameRu>'.$data['authornameru'].'</authorNameRu>
<carrierType>'.$data['carriertype'].'</carrierType>
<character>'.$data['character'].'</character>
<controlTypeOuterCode/>
<controlTypeOuterNameKz>No</controlTypeOuterNameKz>
<controlTypeOuterNameRu>No</controlTypeOuterNameRu>
<description>'.$data['description'].'</description>
<docDate>'.$data['docdate'].'</docDate>
<docKind>'.$data['dockind'].'</docKind>
<docLang>'.$data['doclang'].'</docLang>
<docNo>'.$data['docno'].'</docNo>
<docNoR/>
<docRecPostKz/>
<docRecPostRu/>
<docToNumber/>
<documentReceiverKz/>
<documentReceiverRu/>
<documentSectionId/>
<employeePhone>'.$data['employeephone'].'</employeePhone>
<executor>'.$data['executor'].'</executor>
<idPortalInternal>'.$data['idportalinternal'].'</idPortalInternal>
<portalSign/>
<preparedDate>'.$data['prepareddate'].'</preparedDate>
<resolutionText/>
<secondSignData>'.$data['secondsigndata'].'</secondSignData>
<secondSignEnabled>'.$data['secondsignenabled'].'</secondSignEnabled>
<sectionId/>
<sheetCount>'.$data['sheetcount'].'</sheetCount>
<signerNameKz>'.$data['signernamekz'].'</signerNameKz>
<signerNameRu>'.$data['signernameru'].'</signerNameRu>
<userUin/>';
        $query = $file_xlm . $query;
        return $query;
    }

    public function esedo_doc_outgoing_env($param) {
        $xml = $this->esedo_doc_outgoing_xml($param);
        $envelop_xml = $this->soapEnvelop_ESEDO('','7ea6369f-18c5-4633-ad73-19de35883e0b',$xml);
        return $envelop_xml;
    }

    public function soapEnvelop_ESEDO($method='',$messageId,$data)
    {
        $date_string = date('Y-m-d\TH:i:s.');
        $date_string = $date_string . '000+06:00';

        $enveolpe = '<?xml version=\"1.0\" encoding=\"UTF-8\"?><soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"><soapenv:Body xmlns:wsu=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd\" wsu:Id=\"id-c4964de4-aac5-48e8-b178-e1c135910ed1\"><SendMessage xmlns=\"http://bip.bee.kz/SyncChannel/v10/Types\"><request xmlns=\"\"><requestInfo><messageId>' . $messageId . '</messageId><serviceId>ESEDO_UNIVERSAL_SERVICE</serviceId><messageDate>' . $date_string . '</messageDate><routeId>' . $this->routeid . '</routeId><sender><senderId>' . $this->senderid . '</senderId><password>' . $this->senderpass . '</password></sender></requestInfo><requestData><data xsi:type=\"ns1:docOutgoing\" xmlns:ns1=\"http://esedo.nitec.kz/service/model/document\">'.($this->xmltoJson( $this->xmlFlat($data) )).'</data></requestData></request></SendMessage></soapenv:Body></soapenv:Envelope>';
        return $enveolpe;
    }

    public function eds_download_xml ($fileid) {
        $query = '<tempStorageRequest>
				  <downloadRequest>
				  <fileIdentifiers>'.$fileid.'</fileIdentifiers>
				  </downloadRequest>
				  <credentials>
				  <senderId>'.$this->senderid.'</senderId>
				  <password>'.$this->senderpass2.'</password>
				  </credentials>
				  <type>DOWNLOAD</type>
				  </tempStorageRequest>';
        return $query;
    }

    public function eds_upload_xml ($name,$base64) {
        $query = '<tempStorageRequest xmlns:ns2="http://egov.bee.kz/eds/tempstorage/v2/">
                        <type>UPLOAD</type>
				  	<uploadRequest>
				  		<fileUploadRequests>
				  		<fileProcessIdentifier>0a732e45-5d5b-43ca-9fb9-5c9c3a610388</fileProcessIdentifier>
				  		<content>'.$base64.'</content>
				  		<name>'.$name.'</name>
				  		<lifeTime>10000000</lifeTime>
				  		<needToBeConfirmed>false</needToBeConfirmed>
				  		</fileUploadRequests>
				  	</uploadRequest>
				  	<credentials>
				  	<senderId>'.$this->senderid.'</senderId>
				  	<password>'.$this->senderpass2.'</password>
				  	</credentials>
				  </tempStorageRequest>';
        return $query;
    }

    public function get_eds_download_env($name) {
        $xml = $this->eds_download_xml($name);
        $envelop_xml = $this->soapEnvelop_EDS('','7ea6369f-18c5-4633-ad73-19de35883e0b',$xml);
        return $envelop_xml;
    }

    public function get_eds_upload_env($name,$base64) {
        $xml = $this->eds_upload_xml($name,$base64);
        $envelop_xml = $this->soapEnvelop_EDS('','7ea6369f-18c5-4633-ad73-19de35883e0b',$xml);
        return $envelop_xml;
    }

    public function soapEnvelop_EDS($method='',$messageId,$data)
    {
        $date_string = date('Y-m-d\TH:i:s.');
        $date_string = $date_string . '000+06:00';
        //if ($method=='upload') {
            $enveolpe = '<?xml version=\"1.0\" encoding=\"UTF-8\"?><soap:Envelope xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:typ=\"http://bip.bee.kz/SyncChannel/v10/Types\" xmlns:xenc=\"http://www.w3.org/2001/04/xmlenc#\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"><soapenv:Header xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"/><soap:Body><typ:SendMessage><request><requestInfo><messageId>' . $messageId . '</messageId><correlationId/><serviceId>EDS_TEMP_FILES</serviceId><messageDate>' . $date_string . '</messageDate><routeId/><sender><senderId>' . $this->senderid . '</senderId><password>' . $this->senderpass . '</password></sender><properties/><sessionId>{3c3ba84d-6b6a-4b5d-b3b4-90b1b8f00f2d}</sessionId></requestInfo><requestData><data>'.($this->xmltoJson( $this->xmlFlat($data) )).'</data></requestData></request></typ:SendMessage></soap:Body></soap:Envelope>';
        //}
        return $enveolpe;
    }

    public function nsi_xml($handbook='') {
        $query = '<Request xmlns="http://nitec.kz/unidic/ws/getdata">
					<Login>'.$this->senderid.'</Login>
					<Password>'.$this->senderpass3.'</Password>
					<EntityName>'.$handbook.'</EntityName>
					</Request>';
        return $query;
    }

    public function get_nsi_env($handbook='') {
        $xml = $this->nsi_xml($handbook);
        $envelop_xml = $this->soapEnvelop_ENSI('get','7ea6369f-18c5-4633-ad73-19de35883e0b',$xml);
        return $envelop_xml;
    }

    public function get_nsi_xlm($xml) {
        return $this->request($xml);
    }

    public function soapEnvelop_ENSI($method,$messageId,$data) {

        $date_string = date('Y-m-d\TH:i:s.');
        $date_string = $date_string . '000+06:00';

        if ($method=='get') {
            $enveolpe = '<?xml version=\"1.0\" encoding=\"UTF-8\"?><soap:Envelope xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:typ=\"http://bip.bee.kz/SyncChannel/v10/Types\" xmlns:xenc=\"http://www.w3.org/2001/04/xmlenc#\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"><soapenv:Header xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"/><soap:Body><typ:SendMessage><request><requestInfo><messageId>' . $messageId . '</messageId><correlationId/><serviceId>ENSI_SeGetDataGetItems</serviceId><messageDate>' . $date_string . '</messageDate><routeId/><sender><senderId>' . $this->senderid . '</senderId><password>' . $this->senderpass . '</password></sender><properties/><sessionId>{3c3ba84d-6b6a-4b5d-b3b4-90b1b8f00f2d}</sessionId></requestInfo><requestData><data>'.($this->xmltoJson( $this->xmlFlat($data) )).'</data></requestData></request></typ:SendMessage></soap:Body></soap:Envelope>';
        }
        return $enveolpe;
    }


    protected function request($request_str='') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/xml; charset=utf-8',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$request_str);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        try {
            $response = curl_exec($ch);
        } catch (Exception $exception) {
            $error = $exception->getMessage();
        }
        return $response;
        curl_close($ch);
    }
}
