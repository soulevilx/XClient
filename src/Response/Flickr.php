<?php

namespace JOOservices\XClient\Response;

use Psr\Http\Message\ResponseInterface;

class Flickr extends Json
{
    public function __construct(private ResponseInterface $response)
    {
        parent::__construct($response);

        if ($this->data) {
            $this->data = $this->cleanTextNodes($this->data);
        }
    }

    private function cleanTextNodes($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        } elseif (count($arr) == 0) {
            return $arr;
        } elseif (count($arr) == 1 && array_key_exists('_content', $arr)) {
            return $arr['_content'];
        } else {
            foreach ($arr as $key => $element) {
                $arr[$key] = $this->cleanTextNodes($element);
            }
            return ($arr);
        }
    }

    public function isSuccessful(): bool
    {
        if ($this->data['stat'] === 'fail') {
            return false;
        }

        return parent::isSuccessful();
    }
}