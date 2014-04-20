<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-command for the canonical source repository
 * @copyright Copyright (c) 2008-2013 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license New BSD License
 * @package Phergie\Irc\Plugin\React\Command
 */

namespace Phergie\Irc\Plugin\React\Command;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Event\UserEventInterface;

/**
 * Plugin for parsing commands issued to the bot.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Command
 */
class Plugin extends AbstractPlugin
{
    /**
     * Pattern used to identify commands, where the matched substring is
     * removed before the command is parsed
     *
     * @var string
     */
    protected $identityPattern;

    /**
     * Pattern used to parse commands
     *
     * @var string
     */
    protected $commandPattern = '/^(?P<command>\S+)(\s+(?P<params>.+))?$/';

    /**
     * Pattern used to parse a single command parameter
     *
     * @var string
     */
    protected $paramsPattern = '/"(?:[^\\"]|\\"?)+"|[^\s"]+/';

    /**
     * Pattern used to identify channel names
     *
     * @var string
     * @see http://tools.ietf.org/html/rfc2812#section-1.3
     */
    protected $channelPattern = '/^[&#+!](?:[^ \cG,]+)$/';

    /**
     * Flag indicating whether to check for the bot being address by its
     * connection nick as a command prefix
     *
     * @var boolean
     */
    protected $nick = false;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *
     * prefix - string denoting the start of a command
     *
     * pattern - PCRE regular expression denoting the presence of a command
     *
     * nick - boolean flag where true indicates that common ways of addressing
     * the bot by its connection nick should denote the presence of a command
     *
     * All keys are optional and mutually exclusive.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (isset($config['nick'])) {
            $this->nick = (bool) $config['nick'];
        } elseif (isset($config['prefix'])) {
            $this->identityPattern = '/^' . preg_quote($config['prefix']) . '/';
        } elseif (isset($config['pattern'])) {
            $this->identityPattern = $config['pattern'];
        }
    }

    /**
     * Indicates that the plugin monitors messages sent from users that may
     * contain commands.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'irc.received.privmsg' => 'parseCommand',
            'irc.received.notice' => 'parseCommand',
        );
    }

    /**
     * Returns a pattern for matching a command addressing the bot by its
     * connection nick.
     *
     * @param string $nick Bot's connection nick
     * @return string
     */
    protected function getNickPattern($nick)
    {
        return '/^\s*' . preg_quote($nick) . '[:,\s]+/i';
    }

    /**
     * Returns a new event data object. Intended for overriding by subclasses.
     *
     * @return \Phergie\Irc\Plugin\React\Command\CommandEventInterface
     */
    protected function getCommandEvent()
    {
        return new CommandEvent;
    }

    /**
     * Parses user events for commands and emits them as events.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function parseCommand(UserEventInterface $event, EventQueueInterface $queue)
    {
        // Get the pattern to identify commands
        if ($this->nick) {
            $nick = $event->getConnection()->getNickname();
            $identity = $this->getNickPattern($nick);
        } else {
            $identity = $this->identityPattern;
        }

        // Verify this event contains a command and remove the substring
        // identifying it as one
        $eventParams = $event->getParams();
        $target = $event->getCommand() === 'PRIVMSG'
            ? $eventParams['receivers']
            : $eventParams['nickname'];
        $message = $eventParams['text'];
        if ($identity) {
            if (preg_match($identity, $message, $match)) {
                $message = str_replace($match[0], '', $message);
            } elseif (preg_match($this->channelPattern, $target)) {
                return;
            }
        }

        // Parse the command and its parameters
        preg_match($this->commandPattern, $message, $match);
        $customCommand = $match['command'];
        if (!empty($match['params'])
            && preg_match_all($this->paramsPattern, $match['params'], $matches)) {
            $customParams = array_map(
                function($param) {
                    return trim($param, '"');
                },
                $matches[0]
            );
        } else {
            $customParams = array();
        }

        // Populate an event object with the parsed data
        $commandEvent = $this->getCommandEvent();
        $commandEvent->fromEvent($event);
        $commandEvent->setCustomCommand($customCommand);
        $commandEvent->setCustomParams($customParams);

        // Emit the event object to listeners
        $customEventName = 'command.' . strtolower($customCommand);
        $customEventParams = array($commandEvent, $queue);
        $this->getEventEmitter()->emit($customEventName, $customEventParams);
    }
}
