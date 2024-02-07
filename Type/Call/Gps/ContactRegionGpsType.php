<?php

namespace BaksDev\Contacts\Region\Type\Call\Gps;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

final class ContactRegionGpsType extends Type
{
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform): string
	{
		return (string) $value;
	}
	
	public function convertToPHPValue($value, AbstractPlatform $platform): ?ContactRegionGps
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

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }
	
}