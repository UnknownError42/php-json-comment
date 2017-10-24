<?php

namespace Ahc\Json;

/**
 * JSON comment stripper.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Comment
{
    /**
     * Strip comments from JSON string.
     *
     * @param string $json
     *
     * @return string The comment stripped JSON.
     */
    public function strip($json)
    {
        if (!preg_match('%\/(\/|\*)%', $json)) {
            return $json;
        }

        $index   = -1;
        $inStr   = false;
        $return  = '';
        $char    = '';
        $comment = 'none';

        while (isset($json[++$index])) {
            list($prev, $char) = [$char, $json[$index]];

            if ('none' === $comment && $char === '"' && $prev !== '\\') {
                $inStr = !$inStr;
            }

            $charnext = $char . (isset($json[$index + 1]) ? $json[$index + 1] : '');

            if (!$inStr && 'none' === $comment) {
                $comment = $charnext === '//' ? 'single' : ($charnext === '/*' ? 'multi' : 'none');
            }

            if ($inStr || 'none' === $comment) {
                $return .= $char;

                continue;
            }

            if (($comment === 'single' && $char == "\n")
                || ($comment === 'multi' && $charnext == '*/')
            ) {
                // Cosmetic fix only!
                if ($comment === 'single') {
                    $return = rtrim($return) . $char;
                }

                $comment = 'none';
            }

            $index += $charnext === '*/' ? 1 : 0;
        }

        return $return;
    }

    /**
     * Strip comments and decode JSON string.
     *
     * @param string    $json
     * @param bool|bool $assoc
     * @param int|int   $depth
     * @param int|int   $options
     *
     * @see http://php.net/json_decode [JSON decode native function]
     *
     * @return mixed
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($this->strip($json), $assoc, $depth, $options);
    }
}
