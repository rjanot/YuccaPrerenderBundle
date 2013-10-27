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
     * @param $url
     * @throws Exception
     * @return mixed
     */
    public function send($url)
    {
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'Internal-UserAgent'
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);

        // Check if any error occurred
        $http_code = null;
        $error = curl_error($curl);
        if (!curl_errno($curl)) {
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        // Close request to clear up some resources
        curl_close($curl);

        //Throw an error when not a 200
        if (200 != $http_code) {
            throw new Exception('Request "'.$url.'" didn\'t run properly : '.$error);
        }

        return $resp;
    }
}
