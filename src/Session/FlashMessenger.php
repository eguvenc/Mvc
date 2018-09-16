<?php

namespace Obullo\Session;

/**
 * Flash messenger
 * 
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class FlashMessenger
{
    /**
     * Key prefix
     */
    const NOTICE_PREFIX = '_flash';

    /**
     * Status constants
     */
    const NOTICE_ERROR = 'error';
    const NOTICE_SUCCESS = 'success';
    const NOTICE_WARNING = 'warning';
    const NOTICE_INFO = 'info';

    /**
     * Escaper
     * 
     * @var object
     */
    protected $escaper;

    /**
     * Parameters
     * 
     * @var array
     */
    protected $params = array();

    /**
     * Notice keys
     * 
     * @var array
     */
    protected $notice = array();

    /**
     * Templates for the open/close/separators for message tags
     *
     * @var string
     */
    protected $messageCloseString     = '</li></ul>';
    protected $messageOpenFormat      = '<ul%s><li>';
    protected $messageSeparatorString = '</li><li>';
   
    /**
     * Constructor
     * 
     * @param array $params parameters
     */
    public function __construct($params = array()) 
    {
        if (isset($params['view'])) {
            $this->messageOpenFormat  = $params['view']['message_open_format'];
            $this->messageCloseString = $params['view']['message_close_string'];
            $this->messageSeparatorString = $params['view']['message_separator_string'];
        }
        $this->flashdataRemove(); // Delete old flashdata (from last request)
        $this->flashdataMark();   // Marks all new flashdata as old (data will be deleted before next request)
    }

    /**
     * Set message open format
     * 
     * @param string $messageOpenFormat message open format
     */
    public function setMessageOpenFormat(string $messageOpenFormat = '<div%s><p>')
    {
        $this->messageOpenFormat = $messageOpenFormat;
    }

    /**
     * Set message separator string
     * 
     * @param string $messageSeparatorString separator
     */
    public function setMessageSeparatorString(string $messageSeparatorString = '</p><p>')
    {
        $this->messageSeparatorString = $messageSeparatorString;
    }

    /**
     * Set message close string
     * 
     * @param string $messageCloseString close string
     */
    public function setMessageCloseString(string $messageCloseString = '</p></div>')
    {
        $this->messageCloseString = $messageCloseString;
    }

    /**
     * Returns to message open format
     * 
     * @return string
     */
    public function getMessageOpenFormat() : string
    {
        return $this->messageOpenFormat;
    }

    /**
     * Returns to message separator
     * 
     * @return string
     */
    public function getMessageSeparatorString() : string
    {
        return $this->messageSeparatorString;
    }

    /**
     * Returns to message close string
     * 
     * @return string
     */
    public function getMessageCloseString() : string
    {
        return $this->messageCloseString;
    }

    /**
     * Flush all messages
     * 
     * @param null|bool $autoEscape
     * 
     * @return array
     */
    public function flushMessages(array $classes = [], $autoEscape = null) : array
    {
        $openFormat  = $this->messageOpenFormat;
        $separator   = $this->messageSeparatorString;
        $closeString = $this->messageCloseString;

        $stringClasses = '';
        $messages = array();
        foreach (array('success', 'error', 'info', 'warning') as $key) {
            if ($message = $this->get($key)) {
                $stringClasses = array_merge(array($key), $classes);
                if ($autoEscape) {
                    $message = $this->getEscaper()->escapeHtml($message);
                }
                $messages[] = sprintf($openFormat, ' class="'.implode(' ',$stringClasses).'"').$message.$separator.$closeString;
            }
        }
        return $messages;
    }

    /**
     * Success flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function success($message)
    {
        $this->set(array('success' => $message, 'status' => static::NOTICE_SUCCESS));
        return $this;
    }

    /**
     * Error flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function error($message)
    {
        $this->set(array('error' => $message, 'status' => static::NOTICE_ERROR));
        return $this;
    }

    /**
     * Info flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function info($message)
    {
        $this->set(array('info' => $message, 'status' => static::NOTICE_INFO));
        return $this;
    }

    /**
     * Warning flash message
     * 
     * @param string $message notice
     *
     * @return object
     */
    public function warning($message)
    {
        $this->set(array('warning' => $message, 'status' => static::NOTICE_WARNING));
        return $this;
    }

    /**
     * Fetch a specific flashdata item from the session array
     *
     * @param string $key you want to fetch
     * 
     * @return mixed
     */
    public function get($key)
    {
        $flashdataKey = Self::NOTICE_PREFIX . ':old:' . $key;
        return isset($_SESSION[$flashdataKey]) ? $_SESSION[$flashdataKey] : false;
    }

    /**
     * Keeps existing flashdata available to next request.
     *
     * @param string $key session key
     * 
     * @return object
     */
    public function keep($key)
    {
        $old_flashdataKey = Self::NOTICE_PREFIX . ':old:' . $key;
        $value = $_SESSION[$old_flashdataKey];
        $new_flashdataKey = Self::NOTICE_PREFIX . ':new:' . $key;
        $_SESSION[$new_flashdataKey] = $value;
        return $this;
    }

    /**
     * Set escaper
     * 
     * @param Escaper $escaper object
     */
    public function setEscaper($escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Returns to escaper
     * 
     * @return object
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Identifies flashdata as 'old' for removal
     * when flashdataSweep() runs.
     * 
     * @return void
     */
    protected function flashdataMark()
    {
        foreach ($_SESSION as $name => $value) {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) === 2) {
                $newName = Self::NOTICE_PREFIX . ':old:' . $parts[1];
                $_SESSION[$newName] = $value;
                unset($_SESSION[$name]);
            }
        }
    }

    /**
     * Removes all flashdata marked as 'old'
     *
     * @return void
     */
    protected function flashdataRemove()
    {
        foreach ($_SESSION as $key => $value) {
            $value = null;
            if (strpos($key, ':old:')) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Add or change flashdata, only available
     * until the next request
     *
     * @param mixed  $newData key or array
     * @param string $newval  value
     * 
     * @return object
     */
    protected function set($newData = array(), $newval = '')
    {
        if (is_string($newData)) {
            $newData = array($newData => $newval);
        }
        if (is_array($newData) && sizeof($newData) > 0) {
            foreach ($newData as $key => $val) {
                $flashdataKey = Self::NOTICE_PREFIX . ':new:' . $key;
                $_SESSION[$flashdataKey] = $val;
            }
        }
        return $this;
    }
}