<?php

namespace BaksDev\Contacts\Region\Type\Call\Email;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

final class ContactRegionEmailType extends Type
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform): string
	{
		return (string) $value;
	}
	
	
	public function convertToPHPValue($value, AbstractPlatform $platform): ?ContactRegionEmail
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

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }
	
}