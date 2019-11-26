<?php

namespace AppBundle\Exception;


class EmptyLookupException extends \Exception
{
    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $message = "", $name)
    {
        parent::__construct($message);

        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
