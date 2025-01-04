<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace Putyourlightson\Datastar\Models;

use Putyourlightson\Datastar\Services\SseService;

class Signals
{
    public function __construct(
        protected array $values,
        protected SseService $sse,
    ) {
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * This exists so that `signals.{name}` and `signals.{name}({value})` will work in Twig.
     */
    public function __call(string $name, array $arguments)
    {
        if (empty($arguments)) {
            return $this->get($name);
        }

        return $this->set($name, $arguments[0]);
    }

    /**
     * Returns the signal value.
     */
    public function get(string $name): mixed
    {
        return $this->getNestedValue($name);
    }

    /**
     * Returns the signal values.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Sets a signal value.
     */
    public function set(string $name, mixed $value): static
    {
        $this->setNestedValue($name, $value);

        $this->sse->mergeSignals($this->getNestedArrayValue($name, $value));

        return $this;
    }

    /**
     * Sets multiple signal values at once.
     */
    public function setValues(array $values): static
    {
        foreach ($values as $name => $value) {
            $this->values[$name] = $value;
        }

        $this->sse->mergeSignals($values);

        return $this;
    }

    /**
     * Removes a signal.
     */
    public function remove(string $name): static
    {
        $this->removeNestedValue($name);

        $this->sse->removeSignals([$name]);

        return $this;
    }

    /**
     * Returns a nested value, or null if it does not exist.
     */
    private function getNestedValue(string $name): mixed
    {
        $parts = explode('.', $name);
        $current = &$this->values;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                return null;
            }
            $current = &$current[$part];
        }

        return $current;
    }

    /**
     * Sets a nested signal value while supporting dot notation in the name.
     */
    private function setNestedValue(string $name, mixed $value): void
    {
        $parts = explode('.', $name);
        $current = &$this->values;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }
        $current = $value;
    }

    /**
     * Removes a nested signal value while supporting dot notation in the name.
     */
    private function removeNestedValue(string $name): void
    {
        $parts = explode('.', $name);
        $part = reset($parts);
        $current = &$this->values;
        $parent = &$current;
        foreach ($parts as $part) {
            if (!isset($current[$part])) {
                return;
            }
            $parent = &$current;
            $current = &$current[$part];
        }
        unset($parent[$part]);
    }

    /**
     * Returns a nested value while supporting dot notation in the name.
     */
    private function getNestedArrayValue(string $name, mixed $value): array
    {
        $parts = explode('.', $name);
        $nestedValue = [];
        $current = &$nestedValue;
        foreach ($parts as $part) {
            $current[$part] = [];
            $current = &$current[$part];
        }
        $current = $value;

        return $nestedValue;
    }
}
