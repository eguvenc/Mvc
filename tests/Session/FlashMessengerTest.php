<?php

use Zend\Escaper\Escaper;
use Obullo\Mvc\Session\FlashMessenger;
use Zend\ServiceManager\ServiceManager;

class FlashMessengerTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $params = [
            'view' => array(
                'message_open_format'      => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
                'message_separator_string' => '</li><li>',
                'message_close_string'     => '</li></ul></div>',
            )
        ];
        $flash = new FlashMessenger($params);
        $flash->setEscaper(new Zend\Escaper\Escaper('utf-8'));
		$this->flash = $flash;
	}

	public function testConstructParameters()
	{
		$this->assertEquals('<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>', $this->flash->getMessageOpenFormat());
		$this->assertEquals('</li><li>', $this->flash->getMessageSeparatorString());
		$this->assertEquals('</li></ul></div>', $this->flash->getMessageCloseString());
	}

	public function testSetMessageOpenFormat()
	{
		$this->flash->setMessageOpenFormat('<span%s><p>');
		$this->assertEquals('<span%s><p>', $this->flash->getMessageOpenFormat());
	}

	public function testSetMessageSeparatorString()
	{
		$this->flash->setMessageSeparatorString('</i><i>');
		$this->assertEquals('</i><i>', $this->flash->getMessageSeparatorString());
	}

	public function testSetMessageCloseString()
	{
		$this->flash->setMessageCloseString('</i></span>');
		$this->assertEquals('</i></span>', $this->flash->getMessageCloseString());
	}

	public function testSuccess()
	{
		$this->flash->success('Success message !');
		$this->refreshPage();
		$this->assertEquals(FlashMessenger::NOTICE_SUCCESS, $this->flash->get('notice:status'));
		$this->assertEquals('Success message !', $this->flash->get('notice:success'));
		
		$flash = new FlashMessenger;
		$this->assertFalse($this->flash->get('notice:success'));
	}

	public function testError()
	{
		$this->flash->error('Error message !');
		$this->refreshPage();
		$this->assertEquals(FlashMessenger::NOTICE_ERROR, $this->flash->get('notice:status'));
		$this->assertEquals('Error message !', $this->flash->get('notice:error'));
		
		$flash = new FlashMessenger;
		$this->assertFalse($this->flash->get('notice:error'));
	}

	public function testInfo()
	{
		$this->flash->info('Info message !');
		$this->refreshPage();
		$this->assertEquals(FlashMessenger::NOTICE_INFO, $this->flash->get('notice:status'));
		$this->assertEquals('Info message !', $this->flash->get('notice:info'));
		
		$flash = new FlashMessenger;
		$this->assertFalse($this->flash->get('notice:info'));
	}

	public function testWarning()
	{
		$this->flash->warning('Warning message !');
		$this->refreshPage();
		$this->assertEquals(FlashMessenger::NOTICE_WARNING, $this->flash->get('notice:status'));
		$this->assertEquals('Warning message !', $this->flash->get('notice:warning'));
		
		$flash = new FlashMessenger;
		$this->assertFalse($this->flash->get('notice:warning'));
	}

	public function testKeep()
	{
		$this->flash->warning('Warning message !');
		$this->refreshPage();
		$this->flash->keep('notice:warning');

		$this->assertArrayHasKey(FlashMessenger::NOTICE_PREFIX.':new:notice:warning', $_SESSION);
	}

	public function testFlushMessages()
	{
		$this->flash->success('Success message <!>');
		$this->flash->error('Error message <!>');
		$this->flash->info('Info message <!>');
		$this->flash->warning('Warning message <!>');
		$this->refreshPage();

		$messages = $this->flash->flushMessages(['test1', 'test2']);

		$this->assertEquals('<div class="success test1 test2"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>Success message <!></li><li></li></ul></div>', $messages[0]);

		$this->assertEquals('<div class="error test1 test2"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>Error message <!></li><li></li></ul></div>', $messages[1]);

		$this->assertEquals('<div class="info test1 test2"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>Info message <!></li><li></li></ul></div>', $messages[2]);

		$this->assertEquals('<div class="warning test1 test2"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>Warning message <!></li><li></li></ul></div>',$messages[3]);
	}

	public function testFlushMessagesWithAutoEscape()
	{
		$this->flash->success('Success message <!>');
		$this->refreshPage();
		$messages = $this->flash->flushMessages(['test1', 'test2'], true);

		$this->assertEquals('<div class="success test1 test2"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>Success message &lt;!&gt;</li><li></li></ul></div>', $messages[0]);
	}

	public function testGetEscaper()
	{
		$this->assertInstanceOf('Zend\Escaper\Escaper', $this->flash->getEscaper());
	}

	protected function refreshPage()
	{
        foreach ($_SESSION as $name => $value) {
            $parts = explode(':new:', $name);
            if (is_array($parts) && count($parts) === 2) {
                $newName = FlashMessenger::NOTICE_PREFIX . ':old:' . $parts[1];
                $_SESSION[$newName] = $value;
                unset($_SESSION[$name]);
            }
        }
	}

}