<?php
class LencoAPI {
    private $baseUrl;
    private $apiKey;

    public function __construct() {
        $this->baseUrl = rtrim(LENCO_BASE_URL, '/');
        $this->apiKey = LENCO_KEY;
    }

    private function getAuthorizationHeader() {
        $key = (string) $this->apiKey;
        $normalized = strtolower($key);

        if ($key !== '' && strpos($normalized, 'bearer ') !== 0) {
            return 'Bearer ' . $key;
        }

        return $key;
    }

    private function request($method, $endpoint, $data = []) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        $headers = [
            'Authorization: ' . $this->getAuthorizationHeader(),
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev/localhost

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => false, 'message' => $error];
        }

        return json_decode($response, true);
    }

    public function normalizePhone($phone, $countryIso = 'zm') {
        $digits = preg_replace('/\D+/', '', $phone);
        
        // Extended African country codes
        $codes = [
            'zm' => '260',
            'mw' => '265',
            'ke' => '254',
            'ug' => '256',
            'tz' => '255',
            'rw' => '250',
            'gh' => '233',
            'ng' => '234',
            'za' => '27',
            'zw' => '263',
            'bw' => '267',
            'mz' => '258',
            'ls' => '266',
            'sz' => '266',
            'na' => '264',
            'ao' => '244',
            'cd' => '243'
        ];

        $countryCode = $codes[strtolower($countryIso)] ?? '260';

        // If number starts with country code, strip it
        if (strpos($digits, $countryCode) === 0) {
            $digits = substr($digits, strlen($countryCode));
        }

        // Strip leading zero
        if (strpos($digits, '0') === 0) {
            $digits = ltrim($digits, '0');
        }

        return $digits;
    }

    public function initiateMobileMoney($amount, $currency, $phone, $operator, $country = 'zm') {
        $normalizedPhone = $this->normalizePhone($phone, $country);
        
        // Correct payload structure for Lenco Mobile Money
        $payload = [
            'amount' => number_format((float) $amount, 2, '.', ''),
            'currency' => $currency,
            'reference' => 'SUB-' . uniqid() . '-' . time(),
            'type' => 'mobile-money',
            'mobileMoneyDetails' => [
                'country' => strtoupper($country), // ZM or MW
                'phone' => $normalizedPhone,
                'operator' => strtolower($operator),
            ],
            'bearer' => 'customer',
        ];

        $response = $this->request('POST', '/collections/mobile-money', $payload);
        
        // If response doesn't have reference but was successful, add our generated reference for tracking
        if (isset($response['status']) && $response['status'] === true && !isset($response['data']['reference'])) {
            $response['data']['reference'] = $payload['reference'];
        }
        
        return $response;
    }

    public function verifyTransaction($reference) {
        return $this->request('GET', '/collections/status/' . $reference);
    }

    public function getCollections($page = 1) {
        return $this->request('GET', '/collections?page=' . $page);
    }
}
?>
