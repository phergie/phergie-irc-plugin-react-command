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

use Phergie\Irc\Event\UserEvent;
use Phergie\Irc\Event\UserEventInterface;

/**
 * Class for an event containing a command.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Command
 */
class CommandEvent extends UserEvent implements CommandEventInterface
{
    /**
     * Name of the parsed command
     *
     * @var string|null
     */
    protected $customCommand;

    /**
     * Parsed command parameter values
     *
     * @var array
     */
    protected $customParams = array();

    /**
     * Returns the name of the parsed command.
     *
     * @return string
     */
    public function getCustomCommand()
    {
        return $this->customCommand;
    }

    /**
     * Sets the name of the parsed command.
     *
     * @param string $customCommand
     */
    public function setCustomCommand($customCommand)
    {
        $this->customCommand = $customCommand;
    }

    /**
     * Returns parsed command parameter values.
     *
     * @return array
     */
    public function getCustomParams()
    {
        return $this->customParams;
    }

    /**
     * Sets parsed command parameter values.
     *
     * @param array $customParams
     */
    public function setCustomParams(array $customParams)
    {
        $this->customParams = $customParams;
    }

    /**
     * Copies data from an existing event into this one.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     */
    public function fromEvent(UserEventInterface $event)
    {
        // EventInterface
        $this->setMessage($event->getMessage());
        $this->setConnection($event->getConnection());
        $this->setParams($event->getParams());
        $this->setCommand($event->getCommand());

        // UserEventInterface
        $this->setPrefix($event->getPrefix());
        $this->setNick($event->getNick());
        $this->setUsername($event->getUsername());
        $this->setHost($event->getHost());
        $this->setTargets($event->getTargets());
    }
}
