<?php

class Lokalise_Logger
{
    /**
     * @param $message
     */
    public static function writeLog($message)
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        error_log(self::formatMessage($message));
    }

    /**
     * @param $message
     *
     * @return string
     */
    private static function formatMessage($message)
    {
        return sprintf('%s [Lokalise]: %s', date('d.m.Y H:i:s'), $message);
    }
}
