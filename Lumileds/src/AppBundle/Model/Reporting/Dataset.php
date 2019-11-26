<?php

namespace AppBundle\Model\Reporting;

use JMS\Serializer\Annotation as JMS;

class Dataset
{
    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $data = [];

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $label;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("borderColor")
     *
     * @var string
     */
    private $borderColor;

    /**
     * @JMS\Type("string")
     * @JMS\SerializedName("backgroundColor")
     *
     * @var string
     */
    private $backgroundColor;

    /**
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $fill;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $stack;

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Dataset
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $data
     *
     * @return Dataset
     */
    public function addData($data): self
    {
        $this->data[] = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Dataset
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    /**
     * @param string $borderColor
     *
     * @return Dataset
     */
    public function setBorderColor(string $borderColor): self
    {
        $this->borderColor = $borderColor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFill(): bool
    {
        return $this->fill;
    }

    /**
     * @param bool $fill
     *
     * @return Dataset
     */
    public function setFill(bool $fill): self
    {
        $this->fill = $fill;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $backgroundColor
     *
     * @return Dataset
     */
    public function setBackgroundColor(string $backgroundColor): self
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Dataset
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getStack(): string
    {
        return $this->stack;
    }

    /**
     * @param string $stack
     *
     * @return Dataset
     */
    public function setStack(string $stack): self
    {
        $this->stack = $stack;

        return $this;
    }
}
