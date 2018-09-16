<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Obullo\Logger\SQLLogger;

class SQLLoggerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        if (file_exists(ROOT .'/tests/var/log/debug.log')) {
            unlink(ROOT .'/tests/var/log/debug.log');
        }
        $logger = new Logger('tests');
        $logger->pushHandler(new StreamHandler(ROOT .'/tests/var/log/debug.log', Logger::DEBUG, true, 0666));
        
        $this->logger = $logger;
        $this->sqlLogger = new SQLLogger($this->logger);
	}

    public function testStartQuery()
    {
        // SQL-1
        $this->sqlLogger->startQuery("SELECT * FROM users WHERE id = ? AND name = ?", array(5,'test'));
        $this->sqlLogger->stopQuery();

        // SQL-2
        $this->sqlLogger->startQuery("SELECT * FROM users WHERE id = :id AND name = :name", array('name' => 'test', 'id' => 6));
        $this->sqlLogger->stopQuery();

        $debugLog = file_get_contents(ROOT .'/tests/var/log/debug.log');

        $sql1 = '] tests.DEBUG: SQL-1 ( Query ): {"sql":"SELECT * FROM users WHERE id = 5 AND name = \'test\'"';
        $sql2 = '] tests.DEBUG: SQL-2 ( Query ): {"sql":"SELECT * FROM users WHERE id = 6 AND name = \'test\'"';

        $this->assertContains($sql1, $debugLog);
        $this->assertContains($sql2, $debugLog);
    }
}
