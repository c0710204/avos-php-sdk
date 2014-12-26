<?php

namespace Avos;

use Avos\AVObject;

/**
 * AVRole - Representation of an access Role.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVRole extends AVObject
{

  public static $avClassName = "_Role";

  /**
   * Create a AVRole object with a given name and ACL.
   *
   * @param string   $name
   * @param AVACL $acl
   *
   * @return AVRole
   */
  public static function createRole($name, AVACL $acl)
  {
    $role = AVObject::create(static::$avClassName);
    $role->setName($name);
    $role->setACL($acl);
    return $role;
  }

  /**
   * Returns the role name.
   *
   * @return string
   */
  public function getName()
  {
    return $this->get("name");
  }

  /**
   * Sets the role name.
   *
   * @param string $name The role name
   *
   * @return null
   */
  public function setName($name)
  {
    if ($this->getObjectId()) {
      throw new AVException(
        "A role's name can only be set before it has been saved."
      );
    }
    if (!is_string($name)) {
      throw new AVException(
        "A role's name must be a string."
      );
    }
    return $this->set("name", $name);
  }

  /**
   * Gets the AVRelation for the AVUsers which are direct children of
   *   this role.  These users are granted any privileges that this role
   *   has been granted.
   *
   * @return AVRelation
   */
  public function getUsers()
  {
    return $this->getRelation("users");
  }

  /**
   * Gets the AVRelation for the AVRoles which are direct children of
   *   this role.  These roles' users are granted any privileges that this role
   *   has been granted.
   *
   * @return AVRelation
   */
  public function getRoles()
  {
    return $this->getRelation("roles");
  }

  public function save($useMasterKey = false)
  {
    if (!$this->getACL()) {
      throw new AVException(
        "Roles must have an ACL."
      );
    }
    if (!$this->getName() || !is_string($this->getName())) {
      throw new AVException(
        "Roles must have a name."
      );
    }
    return parent::save($useMasterKey);
  }



}