<?php

declare(strict_types=1);

namespace pff\Exception;
/**
 * Generic pff exception
 *
 * @author paolo.fagni<at>gmail.com
 */
class PffException extends \Exception
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $backtrace;

    /**
     * @var array<string, mixed>
     */
    private array $viewParams = [];

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, ?array $viewParams = null)
    {
        parent::__construct($message, $code, $previous);

        if ($viewParams !== null) {
            $this->setViewParams($viewParams);
        }
        $this->backtrace = debug_backtrace();
    }

    /**
     * @param array<string, mixed> $viewParams
     */
    public function setViewParams(array $viewParams): void
    {
        $this->viewParams = $viewParams;
    }

    /**
     * @return array<string, mixed>
     */
    public function getViewParams(): array
    {
        return $this->viewParams;
    }
}
