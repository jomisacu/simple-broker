<?php
/**
 * @author Jorge Miguel Sanchez Cuello <jomisacu.software@gmail.com>
 *
 * Date: 2021-12-10 17:32
 */

declare(strict_types=1);

namespace Jomisacu\SimpleBroker;

use Throwable;

class RuntimeException extends \RuntimeException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null, $file = null, $line = null)
    {
        parent::__construct($message, $code, $previous);

        $this->file = $file;
        $this->line = $line;
    }
}
