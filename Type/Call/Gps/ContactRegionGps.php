<?php

namespace BaksDev\Contacts\Region\Type\Call\Gps;

use InvalidArgumentException;

final class ContactRegionGps
{

    public const string TYPE = 'contact_region_gps';
	
	private $value;

	public function __construct(?string $value = null)
	{
		if(!empty($value) && !preg_match('{^[\d]+\.[\d]{4,}$}Di', $value))
		{
			throw new InvalidArgumentException('Incorrect Gps.');
		}
		
		$this->value = $value;
	}
	
	
	public function __toString(): string
	{
		return $this->value ?:'';
	}
	
	public function getValue(): string
	{
		return $this->value ?:'';
	}
	
	
}