<?php

namespace Joomla\CMS\Log;

defined('_JEXEC') or die;

class Log
{
    /**
     * All log priorities.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const ALL = 30719;

    /**
     * The system is unusable.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const EMERGENCY = 1;

    /**
     * Action must be taken immediately.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const ALERT = 2;

    /**
     * Critical conditions.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const CRITICAL = 4;

    /**
     * Error conditions.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const ERROR = 8;

    /**
     * Warning conditions.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const WARNING = 16;

    /**
     * Normal, but significant condition.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const NOTICE = 32;

    /**
     * Informational message.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const INFO = 64;

    /**
     * Debugging message.
     *
     * @var    integer
     * @since  1.7.0
     */
    public const DEBUG = 128;

    /**
     * Method to add an entry to the log.
     *
     * @param   mixed    $entry     The LogEntry object to add to the log or the message for a new LogEntry object.
     * @param   integer  $priority  Message priority.
     * @param   string   $category  Type of entry
     * @param   string   $date      Date of entry (defaults to now if not specified or blank)
     * @param   array    $context   An optional array with additional message context.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public static function add($entry, $priority = self::INFO, $category = '', $date = null, array $context = [])
    {
        echo "$entry\n";
    }

}