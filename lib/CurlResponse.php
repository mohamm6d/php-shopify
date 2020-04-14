<?php

namespace PHPShopify;

class CurlResponse
{
    /** @var array */
    private $headers = [];
    /** @var string */
    private $body;

    public function __construct($response)
    {
        $this->parse($response);
    }

    /**
     * @param string $response
     */
    private function parse($response)
    {
        $response = \explode("\r\n\r\n", $response);
        if (\count($response) > 1) {
            // We want the last two parts
            $response = \array_slice($response, -2, 2);
            list($headers, $body) = $response;
            foreach (\explode("\r\n", $headers) as $header) {
                $pair = \explode(': ', $header, 2);
                if (isset($pair[1])) {
                    $headerKey = strtolower($pair[0]);
                    $this->headers[$headerKey] = $pair[1];
                }
            }
        } else {
            $body = $response[0];
        }
		
		if(isset($this->headers['link'])) {
		$links = explode(',', $this->headers['link']);
		foreach($links as $link) {
			if(strpos($link, 'rel="next"')) {
				preg_match('~<(.*?)>~', $link, $next);
				$url_components = parse_url($next[1]);
				parse_str($url_components['query'], $params);
				$this->next_page = '&page_info=' . $params['page_info'];
				$this->next_page_info = $params['page_info'];
			} else {
				$this->last_page = true;
			}
		}
		} else {
            $this->last_page = true; 
        }
		
		
		
        $this->body = array('next_page_info'=>$this->next_page_info,'headers'=>$this->headers, 'body'=>$body);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function __toString()
    {
        $body = $this->getBody();
        $body = $body ? : '';

        return $body;
    }
}
