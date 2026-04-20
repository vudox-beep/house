<?php
class SMSHelper {
    private $apiKey;
    private $apiUrl;
    private $mtnConsumerKey;
    private $mtnConsumerSecret;
    private $mtnTokenUrl;
    private $mtnSendUrl;

    public function __construct() {
        // smsmode config (legacy)
        $this->apiKey = defined('SMSMODE_API_KEY') ? SMSMODE_API_KEY : '';
        $this->apiUrl = defined('SMSMODE_URL') ? SMSMODE_URL : 'https://rest.smsmode.com/sms/v1/messages';

        // MTN config
        $this->mtnConsumerKey = defined('MTN_SMS_CONSUMER_KEY') ? MTN_SMS_CONSUMER_KEY : '';
        $this->mtnConsumerSecret = defined('MTN_SMS_CONSUMER_SECRET') ? MTN_SMS_CONSUMER_SECRET : '';
        $this->mtnTokenUrl = defined('MTN_SMS_TOKEN_URL') ? MTN_SMS_TOKEN_URL : '';
        $this->mtnSendUrl = defined('MTN_SMS_SEND_URL') ? MTN_SMS_SEND_URL : '';
    }

    /**
     * Get access token from MTN OAuth API
     * 
     * @return string|null Access token or null on failure
     */
    private function getMTNAccessToken() {
        if (empty($this->mtnConsumerKey) || empty($this->mtnConsumerSecret) || empty($this->mtnTokenUrl)) {
            return ['error' => 'MTN configuration missing'];
        }

        // We will try multiple combinations of URLs and Auth styles
        $tokenUrls = [
            $this->mtnTokenUrl, // Current configured URL
            'https://api.mtn.com/oauth/client_credential/accesstoken?grant_type=client_credentials' // Alternative from prompt
        ];

        foreach ($tokenUrls as $url) {
            // Method 1: Credentials in Body (Standard Body)
            $curl = curl_init();
            $postData = [
                'client_id' => $this->mtnConsumerKey,
                'client_secret' => $this->mtnConsumerSecret,
                'grant_type' => 'client_credentials'
            ];
            if (defined('MTN_SMS_SCOPE')) {
                $postData['scope'] = MTN_SMS_SCOPE;
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($curl);
            $resData = json_decode($response, true);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200 && !empty($resData['access_token'])) {
                return $resData['access_token'];
            }

            // Method 2: Basic Auth Header
            $curl = curl_init();
            $authHeader = base64_encode($this->mtnConsumerKey . ":" . $this->mtnConsumerSecret);
            $postDataBasic = ['grant_type' => 'client_credentials'];
            if (defined('MTN_SMS_SCOPE')) {
                $postDataBasic['scope'] = MTN_SMS_SCOPE;
            }

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postDataBasic),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Basic " . $authHeader,
                    "Content-Type: application/x-www-form-urlencoded"
                ],
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($curl);
            $resData = json_decode($response, true);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200 && !empty($resData['access_token'])) {
                return $resData['access_token'];
            }
        }

        // If all attempts failed, return detailed error from the last attempt
        $errorMsg = $resData['statusMessage'] ?? $resData['message'] ?? $resData['error_description'] ?? 'Unauthorized';
        $supportMsg = $resData['supportMessage'] ?? null;
        $finalError = "Token Failure: " . $errorMsg . " (HTTP " . $httpCode . ")";
        if ($supportMsg) $finalError .= " | Support: " . $supportMsg;
        return ['error' => $finalError];
    }

    /**
     * Send SMS using MTN v2 API
     * 
     * @param string $phoneNumber Recipient phone number
     * @param string $message Message content
     * @return array Response with status and message
     */
    private function sendMTNSMS($phoneNumber, $message) {
        $tokenData = $this->getMTNAccessToken();
        if (is_array($tokenData) && isset($tokenData['error'])) {
            return ['status' => 'error', 'message' => $tokenData['error']];
        }
        $accessToken = $tokenData;

        // Prepare payload according to latest MTN v2 outbound spec
        // Message is limited to 160 characters
        $safeMessage = mb_substr($message, 0, 160);
        
        $payload = [
            'senderAddress' => defined('MTN_SMS_SENDER_ID') ? MTN_SMS_SENDER_ID : 'MTN',
            'receiverAddress' => [$phoneNumber],
            'message' => $safeMessage,
            'clientCorrelator' => substr(uniqid('hr_v2_'), 0, 36)
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->mtnSendUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Authorization: Bearer " . $accessToken
            ],
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            return ['status' => 'error', 'message' => 'MTN CURL Error: ' . $err];
        }

        $resData = json_decode($response, true);
        
        // Success check based on the new v2 response format provided by user
        if ($httpCode >= 200 && $httpCode < 300 && isset($resData['statusCode']) && $resData['statusCode'] === '0000') {
            return [
                'status' => 'success', 
                'message' => 'SMS sent successfully via MTN v2',
                'transactionId' => $resData['transactionId'] ?? null
            ];
        } else {
            // Enhanced error reporting with MADAPI fields
            $errorMsg = $resData['statusMessage'] ?? $resData['message'] ?? 'MTN API Error (HTTP ' . $httpCode . ')';
            $supportMsg = $resData['supportMessage'] ?? null;
            $madapiCode = $resData['statusCode'] ?? null;
            $transactionId = $resData['transactionId'] ?? null;

            if ($supportMsg) {
                $errorMsg .= " | Support: " . $supportMsg;
            }
            if ($madapiCode) {
                $errorMsg .= " [Code: " . $madapiCode . "]";
            }

            return [
                'status' => 'error', 
                'message' => $errorMsg,
                'transactionId' => $transactionId,
                'raw_response' => $resData
            ];
        }
    }

    /**
     * Send SMS using smsmode (Legacy)
     * 
     * @param string $phoneNumber Recipient phone number
     * @param string $message Message content
     * @return array Response with status and message
     */
    private function sendSmsmodeSMS($phoneNumber, $message) {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            return ['status' => 'error', 'message' => 'SMS API configuration missing'];
        }

        // Remove '+' if present for smsmode
        $cleanPhone = str_replace('+', '', $phoneNumber);

        $payload = [
            'recipient' => [
                'to' => $cleanPhone
            ],
            'body' => [
                'text' => $message
            ]
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "X-Api-Key: " . $this->apiKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err) {
            return ['status' => 'error', 'message' => 'CURL Error: ' . $err];
        }

        $resData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['status' => 'success', 'message' => 'SMS sent successfully via smsmode'];
        } else {
            return [
                'status' => 'error', 
                'message' => $resData['message'] ?? $resData['title'] ?? 'Failed to send SMS (HTTP ' . $httpCode . ')',
                'raw_response' => $resData
            ];
        }
    }

    /**
     * Send SMS (Auto-chooses between MTN and smsmode)
     * 
     * @param string $phoneNumber Recipient phone number
     * @param string $message Message content
     * @return array Response with status and message
     */
    public function sendSMS($phoneNumber, $message) {
        // Prioritize MTN if configured
        if (!empty($this->mtnConsumerKey) && !empty($this->mtnConsumerSecret)) {
            $mtnResponse = $this->sendMTNSMS($phoneNumber, $message);
            // If MTN fails because of token, we might want to return that specifically
            return $mtnResponse;
        }

        // Fallback to smsmode
        return $this->sendSmsmodeSMS($phoneNumber, $message);
    }
}
