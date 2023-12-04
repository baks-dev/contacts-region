<?php

namespace BaksDev\Contacts\Region\Type\Call\Gps;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ContactRegionGpsType extends StringType
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		return (string) $value;
	}
	
	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		return !empty($value) ? new ContactRegionGps($value) : null;
	}
	
	
	public function getName(): string
	{
		return ContactRegionGps::TYPE;
	}
	
	
	public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
	{
		return true;
	}
	
}