<?php

declare(strict_types=1);

namespace Werty\Http\Json;

class Client
{
    /**
     * @var resource curl handle
     */
    private $ch;

    public function __construct()
    {
        $this->ch = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    /**
     * @throws Exception
     *
     * @return mixed return of json_decode
     */
    protected function post(string $url, array $query = [], array $data = [], array $headers = [])
    {
        $url = $this->buildUrl($url, $query);
        $headers = array_merge([
            'Accept: application/json',
            'Content-Type: application/json',
        ], $headers);
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        return $this->exec($opts);
    }

    /**
     * @throws Exception
     *
     * @return mixed return of json_decode
     */
    protected function put(string $url, array $query = [], array $data = [], array $headers = [])
    {
        $url = $this->buildUrl($url, $query);
        $headers = array_merge([
            'Accept: application/json',
            'Content-Type: application/json',
        ], $headers);
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        return $this->exec($opts);
    }

    private function buildUrl(string $url, array $query =[]): string
    {
        $parts = parse_url($url);
        $query = array_merge($parts['query'] ?? [], $query);
        $url = "{$parts['scheme']}://{$parts['host']}{$parts['path']}?" . http_build_query($query);
        return $url;
    }

    /**
     * @throws Exception
     *
     * @return mixed return of json_decode
     */
    protected function get(string $url, array $query = [], array $headers = [])
    {
        // this will append query to url if url already has query component
        $url = $this->buildUrl($url, $query);
        $headers = array_merge([
            'Accept: application/json',
        ], $headers);

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ];

        return $this->exec($opts);
    }

    /**
     * @throws Exception
     */
    private function exec(array $opts)
    {
        curl_reset($this->ch);
        curl_setopt_array($this->ch, $opts);
        $result = curl_exec($this->ch);

        $errno = curl_errno($this->ch);

        if ($errno) {
            $message = curl_error($this->ch);
            throw new Exception($message, $errno, var_export($opts, true), $result);
        }

        $code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if (!$this->isStatusCodeOk($code)) {
            $message = "Server responded with unexpected code: {$code}";
            throw new Exception($message, $code, var_export($opts, true), $result);
        }

        return $this->decode($result);
    }

    protected function decode($result)
    {
        return json_decode($result, false, 512, JSON_THROW_ON_ERROR);
    }

    private function isStatusCodeOk(int $code): bool
    {
        return in_array($code, [200, 201, 202, 203, 204, 205, 206]);
    }
}
