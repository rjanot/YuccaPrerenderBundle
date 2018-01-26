<?php
/*
 * This file was delivered to you as part of the YuccaPrerenderBundle package.
 *
 * (c) Rémi JANOT <r.janot@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yucca\PrerenderBundle\HttpClient;

class Curl implements ClientInterface
{

    /**
     * @param        $url
     * @param string $token
     *
     * @return mixed
     * @throws Exception
     */
    public function send($url, $token = '')
    {
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Internal-UserAgent',
            CURLOPT_HEADER => 1
        ));

        if (!empty($token)) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
                'X-Prerender-Token: ' . $token
            ) );
        }

        // Send the request & save response to $resp
        $response = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Check if any error occurred
        $httpCode = null;
        $error = curl_error($curl);
        if (!curl_errno($curl)) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        // Close request to clear up some resources
        curl_close($curl);

        //Throw an error when not a 200
        if (200 != $httpCode) {
            throw new Exception('Request "'.$url.'" didn\'t run properly : '.$error, (int)$httpCode, null, $header, $body);
        }

        return $body;
    }
}
