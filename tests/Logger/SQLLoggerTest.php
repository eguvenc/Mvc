<?php

use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Obullo\Mvc\Logger\SQLLogger\DoctrineDBAL;

class Logger implements LoggerInterface
{   
    use LoggerTrait;
    protected $messages = array();
    public function log($level, $message, array $context = array())
    {
        $this->messages[$level]['message'] = $message;
        $this->messages[$level]['context'] = $context;
    }
    public function getMessages()
    {
        return $this->messages;
    }
}
class SQLLoggerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $this->log = new Logger;
        $this->logger = new DoctrineDBAL($this->log);
	}

    public function testStartQuery()
    {
        $this->logger->startQuery("SELECT * FROM users WHERE id = ? AND name = ?", array(5,'test'));
        $this->logger->stopQuery();
        $debug = $this->log->getMessages()['debug'];

        $this->assertEquals('SQL-1 ( Query ):', $debug['message']);
        $this->assertEquals("SELECT * FROM users WHERE id = 5 AND name = 'test'", $debug['context']['output']);

        $this->logger->startQuery("SELECT * FROM users WHERE id = :id AND name = :name", array('id' => 5, 'name' => 'test'));
        $this->logger->stopQuery();
        $debug = $this->log->getMessages()['debug'];

        $this->assertEquals('SQL-2 ( Query ):', $debug['message']);
        $this->assertEquals("SELECT * FROM users WHERE id = 5 AND name = 'test'", $debug['context']['output']);
    }
}
