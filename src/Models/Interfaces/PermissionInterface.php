<?php
namespace Morilog\Acl\Models\Interfaces;

/**
 * Interface PermissionInterface
 * @package Morilog\Acl\Models\Interfaces
 */
interface PermissionInterface
{

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTitle();
}