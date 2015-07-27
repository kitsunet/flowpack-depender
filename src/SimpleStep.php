<?php
namespace Flowpack\Depender;

/**
 * A simple step to run the loader with.
 */
class SimpleStep implements Step
{

    /**
     * The step identifier
     *
     * @var string
     */
    protected $identifier;

    /**
     * the callable that will be run on execute
     *
     * @var callable
     */
    protected $callable;

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * Dependencies for this step
     *
     * @var array
     */
    protected $dependencies;

    /**
     * The value after executing the step.
     *
     * @var mixed
     */
    protected $value;

    /**
     * @param string $identifier
     * @param callable $callable
     * @param array $dependencies
     */
    public function __construct($identifier, callable $callable, array $dependencies = [])
    {
        $this->identifier = $identifier;
        $this->callable = $callable;
        $this->dependencies = $dependencies;
    }

    /**
     * The Steps identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param Loader $loader
     * @return mixed
     */
    public function execute(Loader $loader)
    {
        $this->runCallable($loader);
        $this->executed = true;
        return $this->value;
    }

    /**
     * This executes the callable and can be overwritten for more sophisticated implementations.
     *
     * @param Loader $loader
     */
    protected function runCallable(Loader $loader)
    {
        $this->value = call_user_func($this->callable);
    }

    /**
     * @return array Step identifiers that are dependencies of this step
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Has this step been executed
     *
     * @return bool
     */
    public function isExecuted()
    {
        return $this->executed;
    }

    /**
     * Each Step can have a value after being executed, before execution this should return NULL.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
