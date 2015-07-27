<?php
namespace Flowpack\Depender;

/**
 * Describes a Depender Step
 *
 */
interface Step
{

    /**
     * The Steps identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @param Loader $loader
     * @return mixed
     */
    public function execute(Loader $loader);

    /**
     * @return array Step identifiers that are dependencies of this step
     */
    public function getDependencies();

    /**
     * Has this step been executed
     *
     * @return bool
     */
    public function isExecuted();

    /**
     * Each Step can have a value after being executed, before execution this should return NULL.
     *
     * @return mixed
     */
    public function getValue();
}