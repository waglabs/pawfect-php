<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP;

/**
 * Class Results
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class Results
{

    /**
     * @var int
     */
    protected $failures = 0;

    /**
     * @var int
     */
    protected $passes = 0;

    /**
     * @var array<array>
     */
    protected $failureArray = [];

    public function incrementFailures(): void
    {
        $this->failures++;
    }

    public function incrementPasses(): void
    {
        $this->passes++;
    }

    /**
     * @param string        $className
     * @param RuleInterface $rule
     * @param string|null   $message
     */
    public function logFailure(string $className, RuleInterface $rule, string $message = null): void
    {
        $this->failureArray[] = [
            $className,
            $rule->getName(),
            $rule->getDescription(),
            'failure',
            $message
        ];
    }

    /**
     * @param string        $className
     * @param RuleInterface $rule
     * @param string|null   $message
     */
    public function logException(string $className, RuleInterface $rule, string $message = null): void
    {
        $this->failureArray[] = [
            $className,
            $rule->getName(),
            $rule->getDescription(),
            'exception',
            $message
        ];
    }

    /**
     * @return int
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * @return int
     */
    public function getPasses(): int
    {
        return $this->passes;
    }

    /**
     * @return array<array>
     */
    public function getFailureArray(): array
    {
        return $this->failureArray;
    }
}
