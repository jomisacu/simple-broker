<?php
/**
 * @author Jorge Miguel Sanchez Cuello <jomisacu.software@gmail.com>
 *
 * Date: 2021-11-23 08:42
 */

declare(strict_types=1);

namespace Jomisacu\SimpleBroker\Contracts;

interface Dispatcher
{
    public function dispatch($message);
}
