<?php
namespace Flowpack\Depender;

/**
 * Simple dependent step loader
 */
class Loader
{

    const STEP_IDENTIFIER_PATTERN = '/[a-zA-Z0-9.-_]{3,}/';

    /**
     * @var array
     */
    protected $steps = [];

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * @param array $initialStack
     */
    public function __construct(array $initialStack = [])
    {
        $this->stack = $initialStack;
    }

    /**
     * @param Step $step
     * @param bool $replaceExistingStep
     */
    public function registerStep(Step $step, $replaceExistingStep = false)
    {
        $identifier = $step->getIdentifier();
        if (preg_match(static::STEP_IDENTIFIER_PATTERN, $identifier) !== 1) {
            throw new \InvalidArgumentException(sprintf('The given step "%s" does not match the step identifier pattern %s.',
                $identifier, static::STEP_IDENTIFIER_PATTERN), 1437921283);
        }

        if ($replaceExistingStep === false && isset($this->steps[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The given step "%s" was already registered and you did not set the "replaceExistingStep" flag.', $identifier), 1437921270);
        }

        $this->steps[$step->getIdentifier()]['step'] = $step;
    }

    /**
     * @param string $identifier
     * @param bool $withDependencies
     * @return mixed
     */
    public function runStep($identifier, $withDependencies = true)
    {
        if (!isset($this->steps[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The step "%s" you wanted to boot was not registered.',
                $identifier), 1435690556);
        }

        $stepReturnValue = null;
        $dependencyOrder = [$identifier];

        if ($withDependencies) {
            $dependencyOrder = $this->solveDependencies($identifier);
        }

        foreach ($dependencyOrder as $currentIdentifier) {
            $stepReturnValue = $this->executeStep($currentIdentifier);
        }

        return $stepReturnValue;
    }

    /**
     * @param string $key Stack element key or NULL if full stack requested.
     * @return mixed
     */
    public function getStack($key = null)
    {
        if ($key !== null) {
            return $this->stack[$key];
        }

        return $this->stack;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addValueToStack($key, $value)
    {
        $this->stack[$key] = $value;
    }

    /**
     * @param $identifier
     * @return Step
     */
    public function getStep($identifier)
    {
        return $this->steps[$identifier]['step'];
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function isStepRegistered($identifier)
    {
        return isset($this->steps[$identifier]);
    }

    /**
     * @param string $identifier
     * @return array
     */
    protected function solveDependencies($identifier)
    {
        if (isset($this->steps[$identifier]['solvedDependencies'])) {
            return $this->steps[$identifier]['solvedDependencies'];
        }

        $this->steps[$identifier]['solvedDependencies'] = array();
        foreach ($this->getStep($identifier)->getDependencies() as $dependencyIdentifier) {
            $dependencies = $this->solveDependencies($dependencyIdentifier);
            $this->steps[$identifier]['solvedDependencies'] = array_merge($this->steps[$identifier]['solvedDependencies'],
                $dependencies);
        }

        $this->steps[$identifier]['solvedDependencies'][] = $identifier;
        $this->steps[$identifier]['solvedDependencies'] = array_unique($this->steps[$identifier]['solvedDependencies']);
        return $this->steps[$identifier]['solvedDependencies'];
    }

    /**
     * Executes a step by calling the configured callback and handing over the current stack. If the callback returns an array it is considered the new stack.
     *
     * @param string $identifier
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function executeStep($identifier)
    {
        if ($this->getStep($identifier)->isExecuted()) {
            return $this->steps[$identifier]['returnValue'];
        }

        if (!isset($this->steps[$identifier])) {
            throw new \InvalidArgumentException(sprintf('The step "%s" given to execute was not registered.',
                $identifier), 1432187637);
        }

        /** @var Step $step */
        $step = $this->getStep($identifier);
        $returnValue = $step->execute($this);
        $this->steps[$identifier]['returnValue'] = $returnValue;

        return $returnValue;
    }

}