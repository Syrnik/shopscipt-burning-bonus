<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 *
 */
interface shopBurningbonusPluginTaskInterface
{
    /**
     * @param shopBurningbonusPlugin $plugin
     */
    public function __construct(shopBurningbonusPlugin $plugin);

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return bool
     */
    public function run(): bool;
}
