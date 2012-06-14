<?php

class RMethod extends ReflectionMethod{
	
	const VARIABLE_PARAMETERS = '*';
	
	public $classInstance = null;
	
	public function __construct($class, $name){
		parent::__construct($class, $name);
		if(is_object($class)){
			$this->classInstance = $class;
		}else{
		#	$class = new ReflectionClass($class);
		#	$this->classInstance = $class->newInstanceWithoutConstructor();
		}
	}
	
	
	/**
	 * Returns an indication of the number of arguments accepted by a method. 
	 * Returns a nonnegative integer for methods that take a fixed number of arguments. 
	 * For Ruby methods that take a variable number of arguments, returns -n-1, where n is the number of required arguments. 
	 * For methods written in C, returns -1 if the call takes a variable number of arguments.
	 *
	 * @return int Arity of method
	 * @author Koen Punt
	 */
	public function arity(){
		$parameters = $this->getParameters();
		$parameterCount = $this->getNumberOfParameters();
		$optionalParameterCount = array_reduce($parameters, function($result, $parameter){
			return $result + ( ( $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() == RMethod::VARIABLE_PARAMETERS ) || $parameter->isPassedByReference() ? 1 : 0 );
		}, 0);
		$requiredParameterCount = $parameterCount - $optionalParameterCount;
		if( $optionalParameterCount ){
			return -$requiredParameterCount-1;
		}
		return $this->getNumberOfParameters();
	}
	
	/**
	 * Invokes the method with the specified arguments, returning the method’s return value.
	 *
	 * @return mixed
	 * @author Koen Punt
	 */
	public function call(){
		$arguments = func_get_args();
		$class = $this->class;
		return $this->invokeArgs($this->classInstance ?: new $class(), $arguments);
	}
	
	/**
	 * Returns the class that defines the method.
	 *
	 * @return void
	 * @author Koen Punt
	 */
	public function owner(){
		return $this->classInstance;
	}
	
	/**
	 * Returns the class that defines the method.
	 *
	 * @return void
	 * @author Koen Punt
	 */
	public function parameters(){
		return $this->getParameters();
	}
	
	/**
	 * Returns the source filename and line number containing this method or null if this method was not defined
	 *
	 * @return void
	 * @author Koen Punt
	 */
	public function source_location(){
		if($this->getFileName()){
			return array($this->getFileName(), $this->getStartLine());
		}
		return null;
	}

	/**
	 * Returns a Closure object corresponding to this method.
	 *
	 * @return Closure
	 * @author Koen Punt
	 */
	public function to_closure(){
		$class = $this->class;
		return $this->getClosure($this->classInstance ?: new $class());
	}
	
	/**
	 * Returns the name of the underlying method.
	 *
	 * @return void
	 * @author Koen Punt
	 */
	public function to_s(){
		return $this->__toString();
	}
	
}
