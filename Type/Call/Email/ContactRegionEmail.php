<?php

namespace BaksDev\Contacts\Region\Type\Call\Email;

final class ContactRegionEmail
{
	public const TYPE = 'contact_region_email';
	
	private $value;
	
	
	public function __construct(?string $value = null)
	{
		if(!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			throw new \InvalidArgumentException('Incorrect Email.');
		}
		
		$this->value = mb_strtolower($value);
	}
	
	
	public function __toString() : string
	{
		return $this->value;
	}
	
	
	public function isEqual(self $other) : bool
	{
		return $this->getValue() === $other->getValue();
	}
	
	
	public function getValue() : string
	{
		return $this->value;
	}
	
	
	public function getUserName() : string
	{
		return substr($this->value, 0, strrpos($this->value, '@'));
	}
	
	
	public function getHostName() : string
	{
		return substr($this->value, strrpos($this->value, '@') + 1);
	}
	
}