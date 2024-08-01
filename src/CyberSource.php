<?php

namespace OsSalahuddin\CbsClient;

class CyberSource
{
    private $profileId;
    private $accessKey;
    private $paymentUrl;
    private $approvalUrl;
    private $failedUrl;
    private $secret;
    private $apiUrl;
    private $organizationId;
    private $accessKeyApi;
    private $secretApi;
    private $host;

    const cardBrand = [
        '001' => 'Visa',
        '002' => 'Mastercard',
        '003' => 'American Express',
        '004' => 'Discover',
        '005' => 'Diners Club',
        '006' => 'Carte Blanche',
        '007' => 'JCB',
        '014' => 'Enroute',
        '021' => 'JAL',
        '031' => 'Delta',
        '033' => 'Visa Electron',
        '034' => 'Dankort',
        '036' => 'Cartes Bancaires',
        '037' => 'Carta Si',
        '039' => 'Encoded account number',
        '040' => 'UATP',
        '042' => 'Maestro',
        '050' => 'Hipercard',
        '051' => 'Aura',
        '054' => 'Elo',
        '062' => 'China UnionPay',
        '070' => 'EFTPOS',
    ];

    public function __construct()
    {
            $config = include '../config.php';
            $this->setApprovalUrl($config['approvalUrl']);
            $this->setApiUrl($config['apiUrl']);
            $this->setFailedUrl($config['failedUrl']);
            $this->setPaymentUrl($config['liveUrl']);
            $this->setProfileId($config['merchant']);
            $this->setAccessKey($config['merchantPassword']);
            $this->setSecret($config['secret']);
            $this->setOrganizationId($config['organizationId']);
            $this->setAccessKeyApi($config['merchantApi']);
            $this->setSecretApi($config['secretApi']);
            $this->setHost($config['host']);
    }

    public function setAccessKeyApi($accessKeyApi): string
    {
        return $this->accessKeyApi = $accessKeyApi;
    }

    public function getAccessKeyApi(): string
    {
        return $this->accessKeyApi;
    }

    public function setSecretApi($secretApi): string
    {
        return $this->secretApi = $secretApi;
    }

    public function getSecretApi(): string
    {
        return $this->secretApi;
    }

    public function getApprovalUrl(): string
    {
        return $this->approvalUrl;
    }

    public function setApprovalUrl($approvalUrl): string
    {
        return $this->approvalUrl = $approvalUrl;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl): string
    {
        return $this->apiUrl = $apiUrl;
    }

    public function getFailedUrl(): string
    {
        return $this->failedUrl;
    }

    public function setFailedUrl($failedUrl): string
    {
        return $this->failedUrl = $failedUrl;
    }

    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    public function setPaymentUrl($paymentUrl)
    {
        $this->paymentUrl = $paymentUrl;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function getAccessKey()
    {
        return $this->accessKey;
    }

    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public static function sign($requests, $secret): string
    {
        return self::signData(self::buildDataToSign($requests), $secret);
    }

    public static function signData($data, $secretKey): string
    {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    public static function buildDataToSign($requests): string
    {
        $signedFieldNames = explode(",", $requests["signed_field_names"]);
        $dataToSign = [];
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . $requests[$field];
        }
        return self::commaSeparate($dataToSign);
    }

    public static function commaSeparate($dataToSign): string
    {
        return implode(",", $dataToSign);
    }

    public function GenerateDigest($requestPayload)
    {
        $utf8EncodedString = utf8_encode($requestPayload);
        $digestEncode = hash("sha256", $utf8EncodedString, true);
        return base64_encode($digestEncode);
    }

    public function getHttpSignature($resourcePath, $httpMethod, $currentDate, $payload): array
    {
        $request_host = $this->getHost();
        $merchant_id = $this->getOrganizationId();
        $merchant_secret_key = $this->getSecretApi();
        $merchant_key_id = $this->getAccessKeyApi();

        $headerString = "host date request-target v-c-merchant-id";
        $signatureString = "host: " . $request_host . "\ndate: " . $currentDate . "\nrequest-target: " . $httpMethod . " " . $resourcePath . "\nv-c-merchant-id: " . $merchant_id;
        if ($httpMethod == "post") {
            $digest = $this->GenerateDigest($payload);
            $headerString .= " digest";
            $signatureString .= "\ndigest: SHA-256=" . $digest;
        }

        $signatureByteString = utf8_encode($signatureString);
        $decodeKey = base64_decode($merchant_secret_key);
        $signature = base64_encode(hash_hmac("sha256", $signatureByteString, $decodeKey, true));
        $signatureHeader = array(
            'keyid="' . $merchant_key_id . '"',
            'algorithm="HmacSHA256"',
            'headers="' . $headerString . '"',
            'signature="' . $signature . '"'
        );

        $signatureToken = "Signature:" . implode(", ", $signatureHeader);

        $host = "Host:" . $request_host;
        $vcMerchant = "v-c-merchant-id:" . $merchant_id;
        $headers = array(
            $vcMerchant,
            $signatureToken,
            $host,
            'Date:' . $currentDate
        );

        if ($httpMethod == "post") {
            $digestArray = array("Digest: SHA-256=" . $digest);
            $headers = array_merge($headers, $digestArray);
        }

        return $headers;
    }

    public function getTransactionDetails($transactionUrl)
    {
        try {
            $queryParts = explode("/", $transactionUrl);
            $resource = "/tss/v2/transactions/" . end($queryParts);
            $method = "get";
            $url = $transactionUrl;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date);
        } catch (Exception $e) {
            
        }
    }

    public function createSearchRequest($payload)
    {
        try {
            $request_host = $this->getHost();
            $resource = "/tss/v2/searches";
            $method = "post";
            $url = "https://" . $request_host . $resource;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date, $payload);
        } catch (Exception $e) {
        
        }
    }

    public function getSearchRequest($searchId)
    {
        try {
            $request_host = $this->getHost();
            $resource = "/tss/v2/searches/" . $searchId;
            $method = "get";
            $url = "https://" . $request_host . $resource;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date);
        } catch (Exception $e) {
            
        }
    }

    public function refundTransaction($paymentId, $payload)
    {
        try {
            $request_host = $this->getHost();
            $resource = "/pts/v2/payments/" . $paymentId . "/refunds";
            $method = "post";
            $url = "https://" . $request_host . $resource;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date, $payload);
        } catch (Exception $e) {
            TransactionLog::createLog('Cybersource refundRequest exception', '', $e->getMessage(), 0);
        }
    }

    public function retrieveRefundDetails($resource)
    {
        try {
            $request_host = $this->getHost();
            $method = "get";
            $url = "https://" . $request_host . $resource;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date);
        } catch (Exception $e) {
            TransactionLog::createLog('Cybersource refundRequest exception', '', $e->getMessage(), 0);
        }
    }

    public function voidTransaction($paymentId, $payload)
    {
        try {
            $request_host = $this->getHost();
            $resource = "/pts/v2/payments/" . $paymentId . "/voids";
            $method = "post";
            $url = "https://" . $request_host . $resource;
            $resource = utf8_encode($resource);
            $date = date("D, d M Y G:i:s ") . "GMT";

            return $this->sendRequest($url, $method, $resource, $date, $payload);
        } catch (Exception $e) {
            TransactionLog::createLog('Cybersource voidRequest exception', '', $e->getMessage(), 0);
        }
    }

    public function sendRequest($url, $method, $resource, $date, $payload = null)
    {
        $headerParams = [];
        $headers = [];
        $headerParams['Accept'] = '*/*';
        $headerParams['Content-Type'] = 'application/json;charset=utf-8';
        foreach ($headerParams as $key => $val) {
            $headers[] = "$key: $val";
        }
        $authHeaders = $this->getHttpSignature($resource, $method, $date, $payload);
        $headerParams = array_merge($headers, $authHeaders);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerParams);
        curl_setopt($curl, CURLOPT_CAINFO, \Yii::getAlias('@app/web/uploads/cybersource-resource') . '/cacert.pem');
        if ($method == 'post') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");
        $response = curl_exec($curl);
        $http_header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $http_body = substr($response, $http_header_size);
        return json_decode(strval($http_body));
    }
}