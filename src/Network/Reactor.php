<?php

declare(strict_types = 1);

namespace Rush\Network;

use Closure;
use Event;
use EventBase;

/**
 * Class Reactor
 * @package Rush
 */
class Reactor
{
    /**
     * Read Event
     * @var int
     */
    public const READ = Event::READ | Event::PERSIST;

    /**
     * Write Event
     * @var int
     */
    public const WRITE = Event::WRITE | Event::PERSIST;

    /**
     * Signal Event
     * @var int
     */
    public const SIGNAL = Event::SIGNAL | Event::PERSIST;

    /**
     * Timer Event
     * @var int
     */
    public const TIMER = Event::TIMEOUT | Event::PERSIST;

    /**
     * Reactor Instance
     * @var Reactor
     */
    protected static Reactor $instance;

    /**
     * Base Event
     * @var EventBase
     */
    protected EventBase $base;

    /**
     * Listeners Of I/O Event
     * @var array
     */
    protected array $ios = [];

    /**
     * Listeners Of Signal Event
     * @var array
     */
    protected array $signals = [];

    /**
     * Listeners Of Timer Event
     * @var array
     */
    protected array $timers = [];

    /**
     * ID Recorder Of Timer Event
     * @var int
     */
    protected int $timer_recorder = 1;

    /**
     * Reactor constructor
     * @access private
     */
    private function __construct() {}

    /**
     * Reactor cloner
     * @access private
     */
    private function __clone() {}

    /**
     * Get the reactor instance
     * @return Reactor
     */
    public static function getInstance(): Reactor
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
            static::$instance->init();
        }

        return static::$instance;
    }

    /**
     * Init base event
     * @return void
     */
    public function init(): void
    {
        $this->base = new EventBase();
    }

    /**
     * Register a event
     * @param mixed $fd Socket resource, signal num or timeout.
     * @param int $what Type of event.
     * @param Closure $cb The function that is emitted when the event is triggered.
     * @param mixed $arg The function arguments, and the first param is fd in i/o event.
     * @return bool|int
     * @throws NetworkException
     */
    public function add(mixed $fd, int $what, Closure $cb, mixed $arg = null): int|null
    {
        switch ($what) {
            case static::READ:
            case static::WRITE:
                $event = new Event($this->base, $fd, $what, $cb, $arg);

                ! $event->add() &&
                throw new NetworkException("Failed to add event, an error occurred while adding $what.");

                $this->ios[(int) $fd][$what] = $event;

                return null;
            case static::SIGNAL:
                $event = Event::signal($this->base, $fd, $cb, $arg);

                ! $event->addSignal() &&
                throw new NetworkException("Failed to add event, an error occurred while adding $fd.");

                $this->signals[$fd] = $event;

                return null;
            case static::TIMER:
                $event = Event::timer($this->base, $cb, $arg);

                ! $event->addTimer($fd) &&
                throw new NetworkException("Failed to add event, an error occurred while adding $fd.");

                $this->timers[$this->timer_recorder] = $event;

                return $this->timer_recorder++;
            default:
                throw new NetworkException("Failed to add event, $what is not supported type.");
        }
    }

    /**
     * Remove a event
     * @param mixed $fd Socket resource, signal num or timeout.
     * @param int $what Type of event.
     * @return void
     * @throws NetworkException
     */
    public function del(mixed $fd, int $what): void
    {
        switch ($what) {
            case static::READ:
            case static::WRITE:
                if (isset($this->ios[(int) $fd][$what])) {
                    ! $this->ios[(int) $fd][$what]->del()  &&
                    throw new NetworkException("Failed to delete event, an error occurred while deleting $what.");

                    unset($this->ios[(int) $fd][$what]);
                }

                if (empty($this->ios[(int) $fd])) {
                    unset($this->ios[(int) $fd]);
                }

                return;
            case static::SIGNAL:
                if (isset($this->signals[$fd])) {
                    ! $this->signals[$fd]->del() &&
                    throw new NetworkException("Failed to delete event, an error occurred while deleting $fd.");

                    unset($this->signals[$fd]);
                }

                return;
            case static::TIMER:
                if (isset($this->timers[$fd])) {
                    ! $this->timers[$fd]->del() &&
                    throw new NetworkException("Failed to delete timer, an error occurred while deleting $fd.");

                    unset($this->timers[$fd]);
                }

                return;
            default:
                throw new NetworkException("Failed to add event, $what is not supported type.");
        }
    }

    /**
     * Dispatch event
     * @return void
     */
    public function loop(): void
    {
        $this->base->loop(EventBase::EPOLL_USE_CHANGELIST);
    }

    /**
     * Delete all event and re-init base event
     * @param int $timeout Time to exit event dispatch.
     * @return void
     */
    public function destroy(int $timeout = 0): void
    {
        foreach ($this->ios as $io) {
            foreach ($io as $event) {
                $event->del();
            }
        }

        foreach ($this->signals as $event) {
            $event->del();
        }

        foreach ($this->timers as $event) {
            $event->del();
        }

        $this->ios = $this->signals = $this->timers = [];

        $this->base->exit($timeout);

        $this->base->reInit();
    }
}
