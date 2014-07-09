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
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\UserEventInterface;
use Phergie\Irc\Plugin\React\Command\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Command
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $plugin = new Plugin;
        $this->assertInternalType('array', $plugin->getSubscribedEvents());
    }

    /**
     * Data provider for testParseCommandEmitsEvent().
     *
     * @return array
     */
    public function dataProviderParseCommandEmitsEvent()
    {
        $data = array();

        $commands = array(
            'PRIVMSG' => 'receivers',
            'NOTICE' => 'nickname',
        );

        $message = 'foo bar "two words" baz';

        $prefixConfig = array('prefix' => '!');
        $patternConfig = array('pattern' => '/^~/');
        $nickConfig = array('nick' => true);

        $configs = array(
            'foo' => array(),
            '!' . $message => $prefixConfig,
            '~' . $message => $patternConfig,
            'nickname ' . $message => $nickConfig,
            'nickname: ' . $message => $nickConfig,
            ' nickname, ' . $message => $nickConfig,
        );

        $expectedParams = array('bar', 'two words', 'baz');

        $targets = array('#channel', 'user');

        $getEvent = function($command, $targetField, $text, $config, $target) {
            $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
            $connection = Phake::mock('\Phergie\Irc\ConnectionInterface');
            $params = array('text' => $text, $targetField => $target);
            Phake::when($connection)->getNickname()->thenReturn('nickname');
            Phake::when($event)->getConnection()->thenReturn($connection);
            Phake::when($event)->getCommand()->thenReturn($command);
            Phake::when($event)->getParams()->thenReturn($params);
            Phake::when($event)->getTargets()->thenReturn(array());
            return $event;
        };

        foreach ($commands as $command => $targetField) {
            foreach ($configs as $text => $config) {
                foreach ($targets as $target) {
                    $event = $getEvent($command, $targetField, $text, $config, $target);
                    $data[] = array($config, $event, $text == 'foo' ? array() : $expectedParams);
                }
            }
        }

        // Events sent directly to the bot should always be interpreted as
        // potential commands
        $data[] = array(
            $prefixConfig,
            $getEvent('PRIVMSG', $commands['PRIVMSG'], $message, $prefixConfig, 'user'),
            $expectedParams
        );
        $data[] = array(
            $patternConfig,
            $getEvent('PRIVMSG', $commands['PRIVMSG'], $message, $patternConfig, 'user'),
            $expectedParams
        );
        $data[] = array(
            $patternConfig,
            $getEvent('PRIVMSG', $commands['PRIVMSG'], $message, $nickConfig, 'user'),
            $expectedParams
        );

        return $data;
    }

    /**
     * Tests parseCommand() under conditions when it is expected to emit an
     * event.
     *
     * @param array $config Plugin configuration
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @param array $expectedParams Expected command event parameter values
     * @dataProvider dataProviderParseCommandEmitsEvent
     */
    public function testParseCommandEmitsEvent(array $config, UserEventInterface $event, array $expectedParams)
    {
        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $eventEmitter = Phake::mock('\Evenement\EventEmitterInterface');

        $plugin = new Plugin($config);
        $plugin->setEventEmitter($eventEmitter);
        $plugin->parseCommand($event, $queue);

        Phake::verify($eventEmitter)->emit('command.foo', Phake::capture($commandEventParams));
        $commandEvent = $commandEventParams[0];
        $this->assertInstanceOf('\Phergie\Irc\Plugin\React\Command\CommandEvent', $commandEvent);
        $this->assertSame('foo', $commandEvent->getCustomCommand());
        $this->assertSame($expectedParams, $commandEvent->getCustomParams());
        $this->assertSame($queue, $commandEventParams[1]);
    }

    /**
     * Data provider for testParseCommandDoesNotEmitEvent().
     *
     * @return array
     */
    public function dataProviderParseCommandDoesNotEmitEvent()
    {
        $data = array();

        $commands = array(
            'PRIVMSG' => 'receivers',
            'NOTICE' => 'nickname',
        );

        $configs = array(
            'foo' => array('prefix' => '!'),
            'foo bar "two words" baz' => array('prefix' => '!'),
            '~foo bar "two words" baz' => array('pattern' => '/^!/'),
            'foo bar "two words" baz' => array('nick' => true),
            'foo bar "two words" baz' => array('nick' => true),
            ' foo bar "two words" baz' => array('nick' => true),
        );

        foreach ($commands as $command => $targetField) {
            foreach ($configs as $text => $config) {
                $event = Phake::mock('\Phergie\Irc\Event\UserEventInterface');
                $connection = Phake::mock('\Phergie\Irc\ConnectionInterface');
                $params = array('text' => $text, $targetField => '#channel');

                Phake::when($connection)->getNickname()->thenReturn('nickname');
                Phake::when($event)->getConnection()->thenReturn($connection);
                Phake::when($event)->getCommand()->thenReturn($command);
                Phake::when($event)->getParams()->thenReturn($params);
                Phake::when($event)->getTargets()->thenReturn(array());

                $data[] = array($config, $event);
            }
        }

        return $data;
    }
    /**
     * Tests parseCommand() under conditions when it is expected not to emit an
     * event.
     *
     * @param array $config Plugin configuration
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @dataProvider dataProviderParseCommandDoesNotEmitEvent
     */
    public function testParseCommandDoesNotEmitEvent(array $config, UserEventInterface $event)
    {
        $queue = Phake::mock('Phergie\Irc\Bot\React\EventQueueInterface');
        $eventEmitter = Phake::mock('\Evenement\EventEmitterInterface');

        $plugin = new Plugin($config);
        $plugin->setEventEmitter($eventEmitter);
        $plugin->parseCommand($event, $queue);

        Phake::verify($eventEmitter, Phake::never())->emit('command.foo', $this->isType('array'));
    }
}
