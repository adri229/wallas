<?php

/**
 * Modelo de tipos
 *
 * @author acfernandez4 <acfernandez4@esei.uvigo.es>
 */

class Type
{
	private $idType;
	private $name;
	private $owner;

	public function __construct($idType = NULL, $name = NULL, $owner = NULL)
	{
		$this->idType = $idType;
		$this->name = $name;
		$this->owner = $owner;
	}

	public function getIdType()
	{
		return $this->idType;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getOwner()
	{
		return $this->owner;
	}

	public function setIdType($idType)
	{
		$this->idType = $idType;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
}
?>
