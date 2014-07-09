<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link http://github.com/phergie/phergie-irc-plugin-react-command for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Command
 */

namespace Phergie\Irc\Tests\Plugin\React\Command;

use Phake;
use Phergie\Irc\ConnectionInterface;
use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Plugin\React\Command\CommandEvent;

/**
 * Tests for the CommandEvent class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Command
 */
class CommandEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Instance of the class under test
     *
     * @var \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    protected $event;

    /**
     * Instantiates the class under test.
     */
    public function setUp()
    {
        $this->event = new CommandEvent;
    }

    /**
     * Tests that the class extends from UserEvent.
     */
    public function testExtendsUserEvent()
    {
        $this->assertInstanceOf('\Phergie\Irc\Event\UserEvent', $this->event);
    }

    /**
     * Tests getCustomCommand().
     */
    public function testGetCustomCommand()
    {
        $this->assertNull($this->event->getCustomCommand());
    }

    /**
     * Tests setCustomCommand().
     */
    public function testSetCustomCommand()
    {
        $command = 'foo';
        $this->event->setCustomCommand($command);
        $this->assertSame($command, $this->event->getCustomCommand());
    }

    /**
     * Tests getCustomParams().
     */
    public function testGetCustomParams()
    {
        $this->assertSame(array(), $this->event->getCustomParams());
    }

    /**
     * Tests setCustomParams().
     */
    public function testSetCustomParams()
    {
        $params = array('foo', 'bar');
        $this->event->setCustomParams($params);
        $this->assertSame($params, $this->event->getCustomParams());
    }

    /**
     * Tests fromEvent().
     */
    public function testFromEvent()
    {
        $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');

        // EventInterface
        $message = 'message';
        $connection = Phake::mock('\Phergie\Irc\ConnectionInterface');
        $params = array('param1', 'param2');
        $command = 'command';
        Phake::when($event)->getMessage()->thenReturn($message);
        Phake::when($event)->getConnection()->thenReturn($connection);
        Phake::when($event)->getParams()->thenReturn($params);
        Phake::when($event)->getCommand()->thenReturn($command);

        // UserEventInterface
        $prefix = 'prefix';
        $nick = 'nick';
        $username = 'username';
        $host = 'host';
        $targets = array('target1', 'target2');
        Phake::when($event)->getPrefix()->thenReturn($prefix);
        Phake::when($event)->getNick()->thenReturn($nick);
        Phake::when($event)->getUsername()->thenReturn($username);
        Phake::when($event)->getHost()->thenReturn($host);
        Phake::when($event)->getTargets()->thenReturn($targets);

        $this->event->fromEvent($event);

        // EventInterface
        $this->assertSame($message, $this->event->getMessage());
        $this->assertSame($connection, $this->event->getConnection());
        $this->assertSame($params, $this->event->getParams());
        $this->assertSame($command, $this->event->getCommand());

        // UserEventInterface
        $this->assertSame($prefix, $this->event->getPrefix());
        $this->assertSame($nick, $this->event->getNick());
        $this->assertSame($username, $this->event->getUsername());
        $this->assertSame($host, $this->event->getHost());
        $this->assertSame($targets, $this->event->getTargets());
    }
}
