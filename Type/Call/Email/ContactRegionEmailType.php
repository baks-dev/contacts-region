<?php

namespace BaksDev\Contacts\Region\Type\Call\Email;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class ContactRegionEmailType extends StringType
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		return (string) $value;
	}
	
	
	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		return !empty($value) ? new ContactRegionEmail($value) : null;
	}
	
	
	public function getName(): string
	{
        return ContactRegionEmail::TYPE;
	}
	
	
	public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
	{
		return true;
	}
	
}