<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionParameterTest extends Test
{
	protected $type = 'parameter';

	public function testPosition()
	{
		$rfl = $this->getFunctionReflection('position');
		$internalParameters = $rfl->internal->getParameters();
		$tokenParameters = $rfl->token->getParameters();
		for ($i = 0; $i < 3; $i++) {
			$internal = $internalParameters[$i];
			$token = $tokenParameters[$i];

			$this->assertSame($internal->getPosition(), $token->getPosition());
			$this->assertSame($i, $token->getPosition());
		}
	}

	public function testNull()
	{
		$rfl = $this->getParameterReflection('null');
		$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
		$this->assertTrue($rfl->token->allowsNull());

		$rfl = $this->getParameterReflection('noNull');
		$this->assertSame($rfl->internal->allowsNull(), $rfl->token->allowsNull());
		$this->assertTrue($rfl->token->allowsNull());
	}

	public function testOptional()
	{
		ReflectionParameter::setParseValueDefinitions(true);

		$types = array('null' => null, 'true' => true, 'false' => false, 'array' => array(), 'string' => 'string', 'integer' => 1, 'float' => 1.1, 'constant' => E_NOTICE);
		$definitions = array('null' => 'null', 'true' => 'true', 'false' => 'false', 'array' => 'array()', 'string' => "'string'", 'integer' => '1', 'float' => '1.1', 'constant' => 'E_NOTICE');
		foreach ($types as $type => $value) {
			$rfl = $this->getParameterReflection('optional' . ucfirst($type));
			$this->assertSame($rfl->internal->isOptional(), $rfl->token->isOptional());
			$this->assertTrue($rfl->token->isOptional());
			$this->assertSame($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
			$this->assertTrue($rfl->token->isDefaultValueAvailable());
			$this->assertSame($rfl->internal->getDefaultValue(), $rfl->token->getDefaultValue());
			$this->assertSame($value, $rfl->token->getDefaultValue());
			$this->assertSame($definitions[$type], $rfl->token->getDefaultValueDefinition());
		}

		$rfl = $this->getParameterReflection('noOptional');
		$this->assertSame($rfl->internal->isOptional(), $rfl->token->isOptional());
		$this->assertFalse($rfl->token->isOptional());
		$this->assertSame($rfl->internal->isDefaultValueAvailable(), $rfl->token->isDefaultValueAvailable());
		$this->assertFalse($rfl->token->isDefaultValueAvailable());

		try {
			$rfl->token->getDefaultValue();
			$this->fail('Expected exception \TokenReflection\Exception.');
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
			throw $e;
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('TokenReflection\Exception', $e);
		}

		ReflectionParameter::setParseValueDefinitions(false);
	}

	public function testArray()
	{
		$rfl = $this->getParameterReflection('array');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertTrue($rfl->token->isArray());

		$rfl = $this->getParameterReflection('noArray');
		$this->assertSame($rfl->internal->isArray(), $rfl->token->isArray());
		$this->assertFalse($rfl->token->isArray());
	}

	public function testClass()
	{
		$rfl = $this->getParameterReflection('class');
		$this->assertSame($rfl->internal->getClass()->getName(), $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClass()->getName());
		$this->assertSame('Exception', $rfl->token->getClassName());
		$this->assertInstanceOf('TokenReflection\IReflectionClass', $rfl->token->getClass());

		$rfl = $this->getParameterReflection('noClass');
		$this->assertSame($rfl->internal->getClass(), $rfl->token->getClass());
		$this->assertNull($rfl->token->getClass());
		$this->assertNull($rfl->token->getClassName());
	}

	public function testReference()
	{
		$rfl = $this->getParameterReflection('reference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertTrue($rfl->token->isPassedByReference());

		$rfl = $this->getParameterReflection('noReference');
		$this->assertSame($rfl->internal->isPassedByReference(), $rfl->token->isPassedByReference());
		$this->assertFalse($rfl->token->isPassedByReference());
	}

	public function testDeclaring()
	{
		$rfl = $this->getParameterReflection('declaringFunction');
		$this->assertSame($rfl->internal->getDeclaringFunction()->getName(), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunction()->getName());
		$this->assertSame($this->getFunctionName('declaringFunction'), $rfl->token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionFunction', $rfl->token->getDeclaringFunction());

		$this->assertSame($rfl->internal->getDeclaringClass(), $rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClass());
		$this->assertNull($rfl->token->getDeclaringClassName());

		$rfl = $this->getMethodReflection('declaringMethod');
		$internalParameters = $rfl->internal->getParameters();
		$internal = $internalParameters[0];
		$tokenParameters = $rfl->token->getParameters();
		$token = $tokenParameters[0];

		$this->assertSame($internal->getDeclaringFunction()->getName(), $token->getDeclaringFunction()->getName());
		$this->assertSame($this->getMethodName('declaringMethod'), $token->getDeclaringFunction()->getName());
		$this->assertSame($this->getMethodName('declaringMethod'), $token->getDeclaringFunctionName());
		$this->assertInstanceOf('TokenReflection\ReflectionMethod', $token->getDeclaringFunction());

		$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClass()->getName());
		$this->assertSame($this->getClassName('declaringMethod'), $token->getDeclaringClassName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}
}