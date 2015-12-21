# phergie/phergie-irc-plugin-react-command

A plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/) to
parse commands issued to the bot.

A common plugin to use in combination with this one is the
[CommandHelp plugin](https://github.com/phergie/phergie-irc-plugin-react-commandhelp/),
which provides information about available commands and their usage to users.

[![Build Status](https://secure.travis-ci.org/phergie/phergie-irc-plugin-react-command.png?branch=master)](http://travis-ci.org/phergie/phergie-irc-plugin-react-command)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "phergie/phergie-irc-plugin-react-command": "2.*"
    }
}
```

See Phergie documentation for more information on installing plugins.

## Configuration

```php
new \Phergie\Irc\Plugin\React\Command\Plugin(array(

    // Select how you'd like the command to be triggered. 
    // Only 1 method supported at a time so make sure to remove unused methods.

    'prefix' => '!', // string denoting the start of a command

    // or

    'pattern' => '/^!/', // PCRE regular expression denoting the presence of a
                         // command

    // or

    'nick' => true, // true to match common ways of addressing the bot by its
                    // connection nick

))
```

### Usage

This plugin monitors `PRIVMSG` and `NOTICE` events attempting to locate
commands. When it finds one, it emits a custom event: `'command.COMMAND'` where
`COMMAND` is the matched command. Other plugins can subscribe to these events to
be notified when a command is received.

Event parameters include an instance of
[`CommandEvent`](https://github.com/phergie/phergie-irc-plugin-react-command/blob/master/src/CommandEvent.php)
(a subclass of [`UserEvent`](https://github.com/phergie/phergie-irc-event/blob/master/src/UserEvent.php))
that contains data about the parsed command and any other parameters that
accompanied the original event that contained the command (e.g. an object that implements
[`EventQueueInterface`](https://github.com/phergie/phergie-irc-bot-react/blob/master/src/EventQueueInterface.php)).

Here's an example of a plugin that handles a 'foo' command:

```php
use Phergie\Irc\Plugin\React\Command\CommandEvent;
use Phergie\Irc\Bot\React\EventQueueInterface;
use Phergie\Irc\Bot\React\PluginInterface;

class FooPlugin implements PluginInterface
{
    public function getSubscribedEvents()
    {
        return array('command.foo' => 'handleFooCommand');
    }

    public function handleFooCommand(CommandEvent $event, EventQueueInterface $queue)
    {
        $commandName = $event->getCustomCommand();
        $fooParams = $event->getCustomParams();
        // ...
    }
}
```

In its `getSubscribedEvents()` implementation, this plugin indicates that it
will listen for `'command.foo'` events emitted by the Command plugin.

It specifies `handleFooCommand()` as the method for handling those events. Among
this method's parameters is `$event`, an instance of the Command plugin's special
`CommandEvent` class.

`handleFooCommand()` invokes two methods of `$event`: `getCustomCommand()`,
which returns the command that was received (`'foo'` in this case) and is
primarily useful when the same method is used to handle multiple commands, and
`getCustomParams()`, which returns parameters specified when the command was
issued.

Let's say the Command plugin is used with no configuration and receives this IRC event:

`PRIVMSG #channel foo bar "two words" baz`

It will emit the `'command.foo'` event. The `$event` parameter sent to a
handler method for that event will return `'foo'` when its `getCustomCommand()`
method is called and `array('bar', 'two words', 'baz')` when its
`getCustomParams()` method is called.

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
