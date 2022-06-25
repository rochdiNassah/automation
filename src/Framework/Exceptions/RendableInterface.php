<?php declare(strict_types=1);

namespace Automation\Framework\Exceptions;

interface RendableInterface
{
    /**
     * Get the exception's associated view name.
     * 
     * @return string
     */
    public function getView(): string;
}