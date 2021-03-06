<?php

declare(strict_types=1);
/*
 * This file is part of waglabs/pawfect-php.
 *
 * (C) 2021 Wag Labs, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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
            $values['names'] = (array)$values['value'];
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

            return (bool)preg_match($this->regex, $test);
        }

        return in_array($test, $this->names);
    }
}
