<?php
/**
 * Phergie (http://phergie.org)
 *
 * @link https://github.com/phergie/phergie-irc-plugin-react-command for the canonical source repository
 * @copyright Copyright (c) 2008-2014 Phergie Development Team (http://phergie.org)
 * @license http://phergie.org/license Simplified BSD License
 * @package Phergie\Irc\Plugin\React\Command
 */

namespace Phergie\Irc\Plugin\React\Command;

use Phergie\Irc\Event\UserEventInterface;

/**
 * Interface for an event containing a command.
 *
 * @category Phergie
 * @package Phergie\Irc\Plugin\React\Command
 */
interface CommandEventInterface extends UserEventInterface
{
    /**
     * Returns the name of the parsed command.
     *
     * @return string
     */
    public function getCustomCommand();

    /**
     * Sets the name of the parsed command.
     *
     * @param string $customCommand
     */
    public function setCustomCommand($customCommand);

    /**
     * Returns parsed command parameter values.
     *
     * @return array
     */
    public function getCustomParams();

    /**
     * Sets parsed command parameter values.
     *
     * @param array $customParams
     */
    public function setCustomParams(array $customParams);

    /**
     * Copies data from an existing event into this one.
     *
     * @param \Phergie\Irc\Event\UserEventInterface $event
     */
    public function fromEvent(UserEventInterface $event);
}
