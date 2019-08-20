<?php
namespace Drupal\nova_services\Services;

class FieldStateComposer {
	/**
	 * Composed Field State.
	 */
	private $fieldState = [];

	/**
	 * Current target state.
	 */
	private $targetState = '';

	/**
	 * Sets the composer to group state change conditions into an array.
	 */
	private $isGroupInsert = FALSE;


	/**
	 * Generic method for setting state. 
	 */
	public function setOn($state) {
		if (!isset($this->fieldState[$state])) {
			$this->fieldState[$state] = [];
		}

		$this->targetState = $state;

		return $this;
	}

	/**
	 * Create a visible state.
	 */
	public function visible() {
		return $this->setOn('visible');
	}

	/**
	 * Add a checked condition to a state. This method creates an "AND" 
	 * condition.
	 */
	public function onChecked($selector, $value = TRUE) {
		return $this->addStateCondition($selector, 'checked', $value);
	}

	/**
	 * Adds an OR checked condition to a state.
	 */
	public function orOnChecked($selector, $value = TRUE) {
		$this->fieldState[$this->targetState][] = 'or';

		return $this->onChecked($selector, $value);
	}

	/**
	 * Groups state condition calls.
	 */
	public function group($groupClosure) {
		$this->isGroupInsert = TRUE;
		$this->fieldState[$this->targetState][] = [];

		if (is_callable($groupClosure)) {
			$groupClosure($this);
		}

		// Reset group mode once changes has been applied.
		$this->isGroupInsert = FALSE;

		return $this;
	}

	/**
	 * Groups state condition calls into a single "OR" condition.
	 */
	public function orGroup($groupClosure) {
		$this->fieldState[$this->targetState][] = 'or';

		return $this->group($groupClosure);
	}

	/**
	 * Apply composed state condition to a given field.
	 */
	public function apply(&$field) {
		$field['#states'] = $this->fieldState;

		return $this;
	}

	/**
	 * Append composed state condition to a given field.
	 */
	public function append(&$field) {
		$field['#states'] = array_merge($field['#states'], $this->fieldState);

		return $this;
	}

	public function clear() {
		$this->fieldState = [];
	}
	/**
	 * Get current composed states.
	 */
	public function getStatesArray() {
		return $this->fieldState;
	}

	/**
	 * Get the index of last condition of a state.
	 */
	private function getLastIndex($state) {
		return sizeOf($this->fieldState[$state]) - 1;
	}

	/**
	 * Generic method for adding condition to a state.
	 */
	private function addStateCondition($selector, $state, $value) {
		$newState = [ $state => $value ];

		if (!$this->isGroupInsert) {
			$this->fieldState[$this->targetState][$selector] = $newState;
		} else {
			$this->fieldState[$this->targetState][$this->getLastIndex($this->targetState)][$selector] = $newState;
		}

		return $this;
	}
}