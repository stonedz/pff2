<?php

namespace pff\models;

use Doctrine\ORM\Mapping as ORM;

use pff\Abs\AModel;

/**
 * Class Test
 * @package pff\models
 */
#[ORM\Entity]
#[ORM\Table(name: 'test')]
class Test extends AModel
{

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue]
    private $id;


    #[ORM\Column(type: 'string')]
    private $name;

    public function __construct()
    {
    }

    public function doBeforeSystem(): void
    {
        echo 'Before system hook';
    }

    public function getID()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }
}
