<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class ApplyRule
 *
 * @Annotation
 * @Target("CLASS")
 */
final class ApplyRule
{
    /** @var array<string> */
    public $names = [];
    /** @var string|null */
    public $regex;

    /**
     * ApplyRule constructor.
     *
     * @param array<string, mixed> $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $values['names'] = (array) $values['value'];
            unset($values['value']);
        }
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param string $test
     *
     * @return bool
     */
    public function matches(string $test): bool
    {
        if (empty($this->names)) {
            if (empty($this->regex)) {
                return true;
            }

            return !!preg_match($this->regex, $test);
        }

        return in_array($test, $this->names);
    }
}
